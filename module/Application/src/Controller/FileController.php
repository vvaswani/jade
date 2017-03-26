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
use Application\Service\ActivityService;
use Application\Service\AuthorizationService;
use Application\Entity\File;
use Application\Entity\Job;
use Application\Entity\Activity;
use Application\Form\ConfirmationForm;

class FileController extends AbstractActionController
{

    private $em;
    
    private $ams;

    private $as;

    public function __construct(EntityManager $em, ActivityService $ams, AuthenticationService $as)
    {
        $this->em = $em;
        $this->ams = $ams;
        $this->as = $as;
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

        if ($this->authorizationPlugin()->isAuthorized($this->as->getIdentity(), null, null, $job) === false) {
            return $this->alertPlugin()->alert('common.alert-access-denied', array('job.entity'), $this->url()->fromRoute('jobs'));
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
                $file->setCreationTime(new \DateTime("now"));
                $file->setName($data['file']['name']); 
                $file->setJob($job);                      
                $this->em->persist($file); 
                $this->em->flush();
                $this->ams->flush();
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

        $job = $file->getJob();
        if ($this->authorizationPlugin()->isAuthorized($this->as->getIdentity(), null, null, $job) === false) {
            return $this->alertPlugin()->alert('common.alert-access-denied', array('job.entity'), $this->url()->fromRoute('jobs'));
        }

        $builder = new AnnotationBuilder();
        $form = $builder->createForm(new ConfirmationForm());
        $form->setAttribute('action', $this->url()->fromRoute('files', array('action' => 'delete', 'id' => $id, 'jid' => $file->getJob()->getId())));
        
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
                    $this->ams->flush();
                    return $this->redirect()->toRoute('jobs', array('action' => 'view', 'id' => $file->getJob()->getId()));
                }
            } 
            return $this->redirect()->toRoute('jobs');
        }

        return $this->confirmationPlugin()->confirm(
            'common.confirm-delete', 
            array ('file.entity', $file->getName()), 
            $form,
            $this->url()->fromRoute('jobs')
        );
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

        $job = $file->getJob();
        if ($this->authorizationPlugin()->isAuthorized($this->as->getIdentity(), null, null, $job) === false) {
            return $this->alertPlugin()->alert('common.alert-access-denied', array('job.entity'), $this->url()->fromRoute('jobs'));
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
            $this->ams->setQueue($queue); 
            $this->ams->flush();
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