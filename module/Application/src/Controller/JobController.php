<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Form\Annotation\AnnotationBuilder;
use Doctrine\ORM\EntityManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
<<<<<<< HEAD
<<<<<<< HEAD
use Application\Service\ActivityStreamLogger;
use Application\Entity\Job;
use Application\Entity\Activity;
<<<<<<< HEAD
use Application\Form\ConfirmationForm;
=======
use Application\Entity\User;
>>>>>>> Updated service
=======
use Application\Service\ActivityRecorder;
=======
use Application\Service\ActivityStreamLogger;
>>>>>>> Updated service names
use Application\Entity\Job;
use Application\Entity\Activity;
>>>>>>> Added activity recording service and activity stream in case view

class JobController extends AbstractActionController
{
    private $em;
    
<<<<<<< HEAD
<<<<<<< HEAD
    public function __construct(EntityManager $em, ActivityStreamLogger $asl)
    {
        $this->em = $em;
        $this->asl = $asl;
        // TODO replace with authenticated user
        $this->user = new User();
        $this->user->setId(1);
=======
    public function __construct(EntityManager $em, ActivityRecorder $ar)
    {
        $this->em = $em;
        $this->ar = $ar;
>>>>>>> Added activity recording service and activity stream in case view
=======
    public function __construct(EntityManager $em, ActivityStreamLogger $asl)
    {
        $this->em = $em;
        $this->asl = $asl;
>>>>>>> Updated service names
    }

    public function indexAction()
    {
        $jobs = $this->em->getRepository(Job::class)->findBy(array(), array('created' => 'DESC'));
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
            array('jobId' => $job->getId()),
            array('created' => 'DESC')
        );
        return new ViewModel(array('job' => $job, 'activities' => $activities));
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
        $form->bind($job);
        $request = $this->getRequest();
        if ($request->isPost()){
            $form->setData($request->getPost());
            if ($form->isValid()){  
                $this->em->persist($job); 
                $this->em->flush();
<<<<<<< HEAD
<<<<<<< HEAD
                if ($job->getEntityOperationType() == Job::OPERATION_TYPE_CREATE) {
                    $this->asl->log(
                        Job::OPERATION_TYPE_CREATE, 
                        $job,
                        $this->user, 
                        $job
                    );                    
                } else if ($job->getEntityOperationType() == Job::OPERATION_TYPE_UPDATE) {
                    $this->asl->log(
                        Job::OPERATION_TYPE_UPDATE, 
                        $job,
                        $this->user, 
                        $job, 
                        $job->getEntityChangeSet()
                    );                    
                }

=======
                $this->ar->record(
=======
                $this->asl->log(
>>>>>>> Updated service names
                    $job->getEntityOperationType(), 
                    Activity::ENTITY_TYPE_JOB, 
                    $job->getId(), 
                    $job->getId(),
                    $job->getEntityChangeSet()
                );
>>>>>>> Added activity recording service and activity stream in case view
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
<<<<<<< HEAD
<<<<<<< HEAD
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
                    $this->ar->record(
                        Activity::ENTITY_OPERATION_TYPE_DELETE, 
                        Activity::ENTITY_TYPE_JOB, 
                        $id, 
                        $id
                    );                       
                } 
            }
            return $this->redirect()->toRoute('jobs');
        } 

        $viewModel = new ViewModel(array(
            'form' => $form,
            'entityType' => 'job',
            'entityDescriptor' => $job->getTitle(),            
        ));
        $viewModel->setTerminal($request->isXmlHttpRequest());
        $viewModel->setTemplate('application/common/confirm.phtml');
        return $viewModel;
=======
=======
        $clone = clone $job;
>>>>>>> Updated service
        $this->em->remove($job);
        $this->em->flush();
<<<<<<< HEAD
<<<<<<< HEAD
        $this->asl->log(
            Job::OPERATION_TYPE_DELETE, 
            $clone,
            $this->user, 
            $clone
=======
        $this->ar->record(
=======
        $this->asl->log(
>>>>>>> Updated service names
            Activity::ENTITY_OPERATION_TYPE_DELETE, 
            Activity::ENTITY_TYPE_JOB, 
            $id, 
            $id
>>>>>>> Added activity recording service and activity stream in case view
        );        
        return $this->redirect()->toRoute('jobs');
>>>>>>> Updated service names
    }
    
}
