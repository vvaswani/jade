<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Form\Annotation\AnnotationBuilder;
use Zend\Authentication\AuthenticationService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Events;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Application\Service\ActivityService;
use Application\Entity\Job;
use Application\Entity\Job\Permission as JobPermission;
use Application\Entity\Activity;
use Application\Entity\User;
use Application\Entity\Label;
use Application\Entity\Job\File;
use Application\Form\ConfirmationForm;

class JobController extends AbstractActionController
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
            return $this->alertPlugin()->alert('common.alert-access-denied', array('job.entity'), $this->url()->fromRoute('jobs'));
        }

        // this is more efficient but less consistent with the ACL approach
        /*
        $qb = $this->em->createQueryBuilder();
        $qb->select('j')
           ->from(Privilege::class, 'p')
           ->leftJoin(Job::class, 'j', 
                \Doctrine\ORM\Query\Expr\Join::WITH, 'p.job = j.id')
           ->where("j.status = :status")
           ->andWhere("p.user = :user")
           ->orderBy("p.name", "ASC")
           ->setParameter('status', Job::STATUS_OPEN)
           ->setParameter('user', $this->as->getIdentity());
        $jobs = $qb->getQuery()->getResult();
        */
        $results = $this->em->getRepository(Job::class)->findBy(array('status' => Job::STATUS_OPEN), array('creationTime' => 'DESC'));
        $jobs = array();
        foreach ($results as $job) {
            if ($this->authorizationPlugin()->isAuthorized($this->as->getIdentity(), 'job', 'view', $job) !== false) {
                $jobs[] = $job;
            }
        }
        return new ViewModel(array('jobs' => $jobs));
    }
    
    public function viewAction()
    {   
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('jobs');
        }

        $job = $this->em->getRepository(Job::class)->find($id);
        if (!$job) {
            return $this->redirect()->toRoute('jobs');
        }

        if ($this->authorizationPlugin()->isAuthorized($this->as->getIdentity(), null, null, $job) === false) {
            return $this->alertPlugin()->alert('common.alert-access-denied', array('job.entity'), $this->url()->fromRoute('jobs'));
        }

        $activities = $this->em->getRepository(Activity::class)->findBy(
            array('entityId' => $job->getId(), 'entityType' => Activity::ENTITY_TYPE_JOB),
            array('id' => 'DESC')
        );

        $file = new File();
        $builder = new AnnotationBuilder();
        $form = $builder->createForm($file);

        return new ViewModel(array(
            'job' => $job, 
            'form' => $form, 
            'activities' => $activities
        ));
    }    
    
    public function saveAction()
    {   
        $id = (int) $this->params()->fromRoute('id', 0);
        $job = $this->em->getRepository(Job::class)->find($id); 

        if ($this->authorizationPlugin()->isAuthorized($this->as->getIdentity(), null, null, $job) === false) {
            return $this->alertPlugin()->alert('common.alert-access-denied', array('job.entity'), $this->url()->fromRoute('jobs'));
        }

        if (!$job) {
            $job = new Job();
            $job->setCreationTime(new \DateTime("now"));
            $permission = new JobPermission();
            $permission->setUser($this->as->getIdentity());
            $permission->setName(Job::PERMISSION_MANAGE);
            $permission->setJob($job);
            $job->setPermissions(array($permission));
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
                if (!file_exists(File::UPLOAD_PATH . '/' . (int)$job->getId())) {
                    mkdir (File::UPLOAD_PATH . '/' . (int)$job->getId());
                }
                $this->ams->flush();
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

        if ($this->authorizationPlugin()->isAuthorized($this->as->getIdentity(), null, null, $job) === false) {
            return $this->alertPlugin()->alert('common.alert-access-denied', array('job.entity'), $this->url()->fromRoute('jobs'));
        }

        $builder = new AnnotationBuilder();
        $form = $builder->createForm(new ConfirmationForm());
        $form->setAttribute('action', $this->url()->fromRoute('jobs', array('action' => 'delete', 'id' => $id)));

        $request = $this->getRequest();
        if ($request->isPost()){
            $form->setData($request->getPost());
            if ($form->isValid()) { 
                $data = $form->getData();
                if ($data['confirm'] == 1) {
                    $this->em->remove($job);
                    $this->em->flush(); 
                    if (file_exists(File::UPLOAD_PATH . '/' . (int)$id)) {
                        foreach(glob(File::UPLOAD_PATH . '/' . (int)$id . '/*') as $file) {
                            unlink($file);
                        }
                        rmdir (File::UPLOAD_PATH . '/' . (int)$id);
                    }
                    $this->ams->flush();
                } 
            }
            return $this->redirect()->toRoute('jobs');
        } 

        return $this->confirmationPlugin()->confirm(
            'common.confirm-delete', 
            array (
                array('job.entity', 'lower', 'false'),
                array($job->getName(), 'none', 'true'),
            ),            
            $form,
            $this->url()->fromRoute('jobs')
        );

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

        if ($this->authorizationPlugin()->isAuthorized($this->as->getIdentity(), null, null, $job) === false) {
            return $this->alertPlugin()->alert('common.alert-access-denied', array('job.entity'), $this->url()->fromRoute('jobs'));
        }

        $builder = new AnnotationBuilder();
        $form = $builder->createForm(new ConfirmationForm());
        $form->setAttribute('action', $this->url()->fromRoute('jobs', array('action' => 'close', 'id' => $id)));
        
        $request = $this->getRequest();
        if ($request->isPost()){
            $form->setData($request->getPost());
            if ($form->isValid()) { 
                $data = $form->getData();
                if ($data['confirm'] == 1) {
                    $job->setStatus(Job::STATUS_CLOSED);
                    $this->em->persist($job); 
                    $this->em->flush(); 
                    $this->ams->flush();
                } 
            }
            return $this->redirect()->toRoute('jobs');
        } 

        return $this->confirmationPlugin()->confirm(
            'job.confirm-close', 
            array (
                array('job.entity', 'lower', 'false'),
                array($job->getName(), 'none', 'true'),
            ),            
            $form,
            $this->url()->fromRoute('jobs')
        );

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

        if ($this->authorizationPlugin()->isAuthorized($this->as->getIdentity(), null, null, $job) === false) {
            return $this->alertPlugin()->alert('common.alert-access-denied', array('job.entity'), $this->url()->fromRoute('jobs'));
        }

        $builder = new AnnotationBuilder();
        $form = $builder->createForm(new ConfirmationForm());
        $form->setAttribute('action', $this->url()->fromRoute('jobs', array('action' => 'open', 'id' => $id)));
        
        $request = $this->getRequest();
        if ($request->isPost()){
            $form->setData($request->getPost());
            if ($form->isValid()) { 
                $data = $form->getData();
                if ($data['confirm'] == 1) {
                    $job->setStatus(Job::STATUS_OPEN);
                    $this->em->persist($job); 
                    $this->em->flush(); 
                    $this->ams->flush();
                } 
            }
            return $this->redirect()->toRoute('jobs');
        } 

        return $this->confirmationPlugin()->confirm(
            'job.confirm-open', 
            array (
                array('job.entity', 'lower', 'false'),
                array($job->getName(), 'none', 'true'),
            ),            
            $form,
            $this->url()->fromRoute('jobs')
        );
    }    

}
