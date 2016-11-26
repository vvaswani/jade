<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Form\Annotation\AnnotationBuilder;
use Zend\Http\Response\Stream;
use Zend\Http\Headers;
use Zend\Authentication\AuthenticationService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Events;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Application\Listener\ActivityListener;
use Application\Service\ActivityManagerService;
use Application\Entity\File;
use Application\Entity\Job;
use Application\Entity\Activity;
use Application\Form\ConfirmationForm;

class FileController extends AbstractActionController
{

    private $em;
    
    private $al;

    private $ams;

    private $as;

    public function __construct(EntityManager $em, ActivityManagerService $ams, ActivityListener $al, AuthenticationService $as)
    {
        $this->em = $em;
        $this->ams = $ams;
        $this->al = $al;
        $this->as = $as;
        $this->em->getEventManager()->addEventListener(
            array(Events::onFlush),
            $this->al
        );
    }

    public function saveAction()
    {   
        $jid = (int) $this->params()->fromRoute('jid', 0);
        if (!$jid) {
            return $this->redirect()->toRoute('jobs');
        }

        $job = $this->em->getRepository(Job::class)->find($jid);
        if (!$job) {
            return $this->redirect()->toRoute('jobs');
        }

        $file = new File();
        $builder = new AnnotationBuilder();
        $form = $builder->createForm($file);
        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            );
            if (!file_exists(File::UPLOAD_PATH . '/' . (int)$job->getId())) {
                mkdir (File::UPLOAD_PATH . '/' . (int)$job->getId());
            }            
            $filter = $form->getInputFilter()->get('file')->getFilterChain()->getFilters()->extract('FileRenameUpload');
            $filter->setTarget(File::UPLOAD_PATH . '/' . $job->getId());
            $form->setInputFilter($form->getInputFilter()); 
            $form->setData($post);
            if ($form->isValid()) { 
                $data = $form->getData();
                $file->setCreated(new \DateTime("now"));
                $file->setName($data['file']['name']); 
                $file->setJob($job);                      
                $this->em->persist($file); 
                $this->em->flush();
                $this->ams->flush($this->al->getQueue());
                return $this->redirect()->toRoute('jobs', array('action' => 'view', 'id' => $job->getId()));
            } 
        }

        return new ViewModel(array(
            'form' => $form,
            'jid'  => $job->getId()
        ));        
    }

    public function deleteAction()
    {   
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('jobs');
        }

        $file = $this->em->getRepository(File::class)->find($id);
        if (!$file) {
            return $this->redirect()->toRoute('jobs');
        }

        $builder = new AnnotationBuilder();
        $form = $builder->createForm(new ConfirmationForm());
        $form->setAttribute('action', $this->url()->fromRoute('files', array('action' => 'delete', 'id' => $id, 'jid' => $file->getJob()->getId())));
        $form->get('cancelTo')->setValue($this->url()->fromRoute('jobs'));
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $data = $form->getData();
                if ($data['confirm'] == 1) {
                    $fileObject = File::UPLOAD_PATH . '/' . $file->getJob()->getId() . '/' . $file->getName();
                    if (file_exists($fileObject)) {
                        unlink($fileObject);
                    }
                    $this->em->remove($file);
                    $this->em->flush(); 
                    $this->ams->flush($this->al->getQueue());
                    return $this->redirect()->toRoute('jobs', array('action' => 'view', 'id' => $file->getJob()->getId()));
                }
            } 
            return $this->redirect()->toRoute('jobs');
        }

        $viewModel = new ViewModel(array(
            'form' => $form,
            'entityType' => 'file',
            'entityDescriptor' => $file->getName(),
            'confirmationMessage' => 'common.confirm-delete', 
        ));
        $viewModel->setTerminal($request->isXmlHttpRequest());
        $viewModel->setTemplate('application/common/confirm.phtml');
        return $viewModel;
    }

    public function downloadAction() 
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('jobs');
        }

        $file = $this->em->getRepository(File::class)->find($id);
        if (!$file) {
            return $this->redirect()->toRoute('jobs');
        }

        $fileObject = File::UPLOAD_PATH . '/' . $file->getJob()->getId() . '/' . $file->getName();
        if (file_exists($fileObject)) {
            $queue[] = array(
                Activity::OPERATION_REQUEST, 
                new \DateTime("now"),
                $file->getJob(),
                $file, 
                array('name' => $file->getName())
            );
            $this->al->setQueue($queue); 
            $this->ams->flush($this->al->getQueue());
            $response = new Stream();
            $response->setStream(fopen($fileObject, 'r'));
            $response->setStatusCode(200);
            $response->setStreamName($file->getName());
            $headers = new Headers();
            $headers->addHeaders(array(
                'Content-Disposition' => 'attachment; filename="' . $file->getName() .'"',
                'Content-Type' => 'application/octet-stream',
                'Content-Length' => filesize($fileObject),
                'Expires' => '@0', 
                'Cache-Control' => 'must-revalidate',
                'Pragma' => 'public'
            ));
            $response->setHeaders($headers);
            return $response;
        } 
        return $this->redirect()->toRoute('jobs');
    }    
}