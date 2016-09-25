<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Form\Annotation\AnnotationBuilder;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Events;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Application\Listener\ActivityListener;
use Application\Service\ActivityManager;
use Application\Entity\Job;
use Application\Entity\Activity;
use Application\Entity\User;
use Application\Entity\Label;
use Application\Form\ConfirmationForm;

class JobController extends AbstractActionController
{
    private $em;

    private $al;

    private $am;

    public function __construct(EntityManager $em, ActivityManager $am, ActivityListener $al)
    {
        $this->em = $em;
        $this->al = $al;
        $this->am = $am;
        $this->em->getEventManager()->addEventListener(
            array(Events::onFlush),
            $this->al
        );
    }

    public function indexAction()
    {
        $jobs = $this->em->getRepository(Job::class)->findBy(array('status' => Job::STATUS_OPEN), array('created' => 'DESC'));
        return new ViewModel(array('jobs' => $jobs));
    }
    
    public function viewAction()
    {   
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('jobs');
        }
        $job = $this->em->getRepository(Job::class)->find($id);
        $activities = $this->em->getRepository(Activity::class)->findBy(
            array('entityId' => $job->getId(), 'entityType' => Activity::ENTITY_TYPE_JOB),
            array('id' => 'DESC')
        );
        return new ViewModel(array(
            'job' => $job, 
            'activities' => $activities,
            'user' => $this->al->getUser()
        ));
    }    
    
    public function saveAction()
    {   
        $id = (int) $this->params()->fromRoute('id', 0);
        $job = $this->em->getRepository(Job::class)->find($id);        
        if (!$job) {
            $job = new Job();
            $job->setCreated(new \DateTime("now"));
        }
        $builder = new AnnotationBuilder();
        $hydrator = new DoctrineHydrator($this->em);
        $form = $builder->createForm($job);

        $form->setHydrator($hydrator);
        $form->get('labels')->setOptions(
            array('object_manager' => $this->em, 'target_class' => 'Application\Entity\Label')
        );
        $form->bind($job);

        // set options for label selector 
        // include the colour as an attribute
        // for further processing in the view script
        $labelOptions = array();
        $labels = $this->em->getRepository(Label::class)->findBy(array(), array('name' => 'ASC'));
        foreach ($labels as $l) {
            $labelOptions[] = array(
                'value' => $l->getId(), 
                'label' => $l->getName(), 
                'attributes' => array('data-colour' => $l->getColour())
            );
        }
        $form->get('labels')->setValueOptions($labelOptions);  

        $request = $this->getRequest();
        if ($request->isPost()){
            $form->setData($request->getPost());
            if ($form->isValid()){  
                $job->setStatus(Job::STATUS_OPEN);
                $this->em->persist($job); 
                $this->em->flush();
                $this->am->flush($this->al->getQueue());
                return $this->redirect()->toRoute('jobs');
            }
        }
         
        return new ViewModel(array(
            'form' => $form,
            'id' => $job->getId(),
        ));
    }
    
    public function deleteAction()
    {   
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('jobs');
        }

        $job = $this->em->getRepository(Job::class)->find($id);
        if (!$job) {
            return $this->redirect()->toRoute('jobs');
        }

        $builder = new AnnotationBuilder();
        $form = $builder->createForm(new ConfirmationForm());
        $form->setAttribute('action', $this->url()->fromRoute('jobs', array('action' => 'delete', 'id' => $id)));
        $form->get('cancelTo')->setValue($this->url()->fromRoute('jobs'));
        
        $request = $this->getRequest();
        if ($request->isPost()){
            $form->setData($request->getPost());
            if ($form->isValid()) { 
                $data = $form->getData();
                if ($data['confirm'] == 1) {
                    $this->em->remove($job);
                    $this->em->flush(); 
                    $this->am->flush($this->al->getQueue());
                } 
            }
            return $this->redirect()->toRoute('jobs');
        } 

        $viewModel = new ViewModel(array(
            'form' => $form,
            'entityType' => 'job',
            'entityDescriptor' => $job->getTitle(),
            'confirmationMessage' => 'common.confirm-delete', 
        ));
        $viewModel->setTerminal($request->isXmlHttpRequest());
        $viewModel->setTemplate('application/common/confirm.phtml');
        return $viewModel;
    }

    public function closeAction()
    {   
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('jobs');
        }

        $job = $this->em->getRepository(Job::class)->find($id);
        if (!$job) {
            return $this->redirect()->toRoute('jobs');
        }

        $builder = new AnnotationBuilder();
        $form = $builder->createForm(new ConfirmationForm());
        $form->setAttribute('action', $this->url()->fromRoute('jobs', array('action' => 'close', 'id' => $id)));
        $form->get('cancelTo')->setValue($this->url()->fromRoute('jobs'));
        
        $request = $this->getRequest();
        if ($request->isPost()){
            $form->setData($request->getPost());
            if ($form->isValid()) { 
                $data = $form->getData();
                if ($data['confirm'] == 1) {
                    $job->setStatus(Job::STATUS_CLOSED);
                    $this->em->persist($job); 
                    $this->em->flush(); 
                    $this->am->flush($this->al->getQueue());
                } 
            }
            return $this->redirect()->toRoute('jobs');
        } 

        $viewModel = new ViewModel(array(
            'form' => $form,
            'entityType' => 'job',
            'entityDescriptor' => $job->getTitle(),
            'confirmationMessage' => 'job.confirm-close',            
        ));
        $viewModel->setTerminal($request->isXmlHttpRequest());
        $viewModel->setTemplate('application/common/confirm.phtml');
        return $viewModel;
    }    

    public function openAction()
    {   
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('jobs');
        }

        $job = $this->em->getRepository(Job::class)->find($id);
        if (!$job) {
            return $this->redirect()->toRoute('jobs');
        }

        $builder = new AnnotationBuilder();
        $form = $builder->createForm(new ConfirmationForm());
        $form->setAttribute('action', $this->url()->fromRoute('jobs', array('action' => 'open', 'id' => $id)));
        $form->get('cancelTo')->setValue($this->url()->fromRoute('jobs'));
        
        $request = $this->getRequest();
        if ($request->isPost()){
            $form->setData($request->getPost());
            if ($form->isValid()) { 
                $data = $form->getData();
                if ($data['confirm'] == 1) {
                    $job->setStatus(Job::STATUS_OPEN);
                    $this->em->persist($job); 
                    $this->em->flush(); 
                    $this->am->flush($this->al->getQueue());
                } 
            }
            return $this->redirect()->toRoute('jobs');
        } 

        $viewModel = new ViewModel(array(
            'form' => $form,
            'entityType' => 'job',
            'entityDescriptor' => $job->getTitle(),
            'confirmationMessage' => 'job.confirm-open', 
        ));
        $viewModel->setTerminal($request->isXmlHttpRequest());
        $viewModel->setTemplate('application/common/confirm.phtml');
        return $viewModel;
    }    

}
