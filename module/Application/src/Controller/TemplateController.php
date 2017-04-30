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
use Application\Entity\Activity;
use Application\Entity\Template;
use Application\Entity\Template\Permission as TemplatePermission;
use Application\Form\ConfirmationForm;

class TemplateController extends AbstractActionController
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

    public function indexAction()
    {
        if ($this->authorizationPlugin()->isAuthorized($this->as->getIdentity(), null, null) === false) {
            return $this->alertPlugin()->alert('common.alert-access-denied', array('template.entity'), $this->url()->fromRoute('templates'));
        }  

        $labels = $this->em->getRepository(Template::class)->findBy(array(), array('creationTime' => 'DESC'));
        return new ViewModel(array('templates' => $templates));
    }

    public function saveAction()
    {   
        $id = (int) $this->params()->fromRoute('id', 0);
        $template = $this->em->getRepository(Template::class)->find($id);    

        if ($this->authorizationPlugin()->isAuthorized($this->as->getIdentity(), null, null, $template) === false) {
            return $this->alertPlugin()->alert('common.alert-access-denied', array('template.entity'), $this->url()->fromRoute('templates'));
        }            
        
        if (!$template) {
            $template = new Template();
            $template->setCreationTime(new \DateTime("now"));
            $permission = new TemplatePermission();
            $permission->setUser($this->as->getIdentity());
            $permission->setName(Template::PERMISSION_MANAGE);
            $permission->setTemplate($template);
            $template->setPermissions(array($permission));
        } 

        $builder = new AnnotationBuilder();
        $hydrator = new DoctrineHydrator($this->em);
        $form = $builder->createForm($template);
        $form->setHydrator($hydrator);
        $form->bind($template);
        $request = $this->getRequest();
        if ($request->isPost()){
            $post = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            );
            $form->setData($post);
            if ($form->isValid()) { 
                $data = $form->getData();
                $template->setCreationTime(new \DateTime("now"));
                $template->setName($post['file']['name']); 
                $this->em->persist($template); 
                $this->em->flush();
                if (!file_exists(Template::UPLOAD_PATH . '/' . (int)$template->getId())) {
                    mkdir (Template::UPLOAD_PATH . '/' . (int)$template->getId());
                }            
                $filter = $form->getInputFilter()->get('file')->getFilterChain()->getFilters()->extract('FileRenameUpload');
                $filter->setTarget(Template::UPLOAD_PATH . '/' . (int)$template->getId());
                $form->setInputFilter($form->getInputFilter()); 
                $data = $form->getData();
                $this->ams->flush();
                return $this->redirect()->toRoute('templates');
            } 

        }

        return new ViewModel(array(
            'form' => $form,
            'id'  => $template->getId()
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
        if ($this->authorizationPlugin()->isAuthorized($this->as->getIdentity(), 'job.file', null, $job) === false) {
            return $this->alertPlugin()->alert('common.alert-access-denied', array('job.entity'), $this->url()->fromRoute('jobs'));
        }

        $builder = new AnnotationBuilder();
        $form = $builder->createForm(new ConfirmationForm());
        $form->setAttribute('action', $this->url()->fromRoute('jobs.files', array('action' => 'delete', 'id' => $id, 'jid' => $file->getJob()->getId())));
        
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
            array (
                array('file.entity', 'lower', 'false'),
                array($file->getName(), 'none', 'true'),
            ), 
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
        if ($this->authorizationPlugin()->isAuthorized($this->as->getIdentity(), 'job.file', null, $job) === false) {
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