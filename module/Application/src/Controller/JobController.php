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
use Application\Entity\Job\Log;
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
        $status = (int) $this->params()->fromRoute('status', Job::STATUS_OPEN);

        if ($this->authorizationPlugin()->isAuthorized($this->as->getIdentity(), null, null) === false) {
            return $this->alertPlugin()->alert('common.alert-access-denied', array('job.entity'), $this->url()->fromRoute('jobs'));
        }

        $jobs = $this->em->getRepository(Job::class)->getAuthorizedJobs($this->as->getIdentity(), $status, 'job', 'view');
        return new ViewModel(array('jobs' => $jobs, 'status' => $status));
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

        $activities = $this->em->getRepository(Activity::class)
                           ->getRecentActivitiesByJob($job->getId(), 10, 720);

        $logs = $this->em->getRepository(Log::class)->findBy(array('job' => $job->getId()), array('date' => 'DESC'));
        $job->setLogs($logs);

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
            $job->setStatus(Job::STATUS_OPEN);
            $permission = new JobPermission();
            $permission->setUser($this->as->getIdentity());
            $permission->setName(Job::PERMISSION_MANAGE);
            $permission->setJob($job);
            $job->setPermissions(array($permission));
        }

        if ($job->getStatus() == Job::STATUS_CLOSED) {
            return $this->alertPlugin()->alert('job.alert-action-closed-job', array('job.entity'), $this->url()->fromRoute('jobs', array('action' => 'index', 'id' => false, 'status' => Job::STATUS_CLOSED)));
        }

        $builder = new AnnotationBuilder();
        $hydrator = new DoctrineHydrator($this->em);
        $form = $builder->createForm($job);

        $form->setHydrator($hydrator);
        $form->get('labels')->setOptions(array(
            'object_manager' => $this->em,
            'target_class' => 'Application\Entity\Label',
            'property' => 'name',
            'option_attributes' => [
                'data-colour' => function (Label $label) {
                    return $label->getColour();
                },
            ],
        ));
        $form->get('customer')->setOptions(array(
            'object_manager' => $this->em,
            'target_class' => 'Application\Entity\User',
            'property' => 'name',
            'display_empty_item' => true,
            'empty_item_label' => 'common.select-empty-item-title',
            'find_method' => [
                'name'   => 'findBy',
                'params' => [
                    'criteria' => ['status' => User::STATUS_ACTIVE, 'role' => User::ROLE_CUSTOMER],
                ]
            ]
        ));
        $form->bind($job);

        // set options for contract type selector
        $form->get('contractType')->setValueOptions(array(
            Job::CONTRACT_TYPE_FIXED => 'job.contract-type-fixed',
            Job::CONTRACT_TYPE_VARIABLE => 'job.contract-type-variable',
        ));

        $request = $this->getRequest();
        if ($request->isPost()){
            $form->setData($request->getPost());
            if ($form->isValid()){
                $this->em->persist($job);
                $this->em->flush();
                if (!file_exists(File::UPLOAD_PATH . '/' . (int)$job->getId())) {
                    mkdir (File::UPLOAD_PATH . '/' . (int)$job->getId());
                }
                $this->ams->flush();
                return $this->redirect()->toRoute('jobs', array('action' => 'view', 'id' => $job->getId()));
            }
        }

        return new ViewModel(array(
            'form' => $form,
            'jid' => $job->getId(),
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

        if ($job->getStatus() == Job::STATUS_OPEN) {
            return $this->alertPlugin()->alert('job.alert-action-open-job', array('job.entity'), $this->url()->fromRoute('jobs'));
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

        if ($job->getStatus() == Job::STATUS_CLOSED) {
            return $this->alertPlugin()->alert('job.alert-action-closed-job', array('job.entity'), $this->url()->fromRoute('jobs', array('action' => 'index', 'id' => false, 'status' => Job::STATUS_CLOSED)));
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

        if ($job->getStatus() == Job::STATUS_OPEN) {
            return $this->alertPlugin()->alert('job.alert-action-open-job', array('job.entity'), $this->url()->fromRoute('jobs'));
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
