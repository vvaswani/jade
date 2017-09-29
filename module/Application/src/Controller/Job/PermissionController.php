<?php
namespace Application\Controller\Job;

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
use Application\Entity\Job;
use Application\Entity\Job\Permission as JobPermission;
use Application\Entity\Permission;
use Application\Entity\User;
use Application\Entity\Activity;
use Application\Form\ConfirmationForm;

class PermissionController extends AbstractActionController
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

    public function grantAction()
    {
        $jid = (int) $this->params()->fromRoute('jid', 0);
        if (!$jid) {
            return $this->redirect()->toRoute('jobs');
        }

        $job = $this->em->getRepository(Job::class)->find($jid);
        if (!$job) {
            return $this->redirect()->toRoute('jobs');
        }

        if ($this->authorizationPlugin()->isAuthorized($this->as->getIdentity(), 'job', 'delete', $job) === false) {
            return $this->alertPlugin()->alert('common.alert-access-denied', array('job.entity'), $this->url()->fromRoute('jobs'));
        }

        if ($job->getStatus() == Job::STATUS_CLOSED) {
            return $this->alertPlugin()->alert('job.alert-action-closed-job', array('job.entity'), $this->url()->fromRoute('jobs', array('action' => 'index', 'id' => false, 'status' => Job::STATUS_CLOSED)));
        }

        $builder = new AnnotationBuilder();
        $permission = new JobPermission();
        $form = $builder->createForm($permission);

        $form->setAttribute('action', $this->url()->fromRoute('jobs.permissions', array('action' => 'grant', 'jid' => $job->getId())));

        $form->get('cancelUrl')->setValue($this->url()->fromRoute('jobs', array('action' => 'view', 'id' => $job->getId())));

        $form->get('user')->setOptions(array(
            'object_manager' => $this->em,
            'target_class' => 'Application\Entity\User',
            'property' => 'name',
            'find_method' => [
                'name'   => 'getPotentialCollaboratorsByJob',
                'params' => [
                    'jid' => $job->getId()
                ]
            ]
        ));

        // set options for permission selector
        $permissionOptions = array();
        $permissionOptions[] = array(
            'value' => Job::PERMISSION_EDIT,
            'label' => 'job.permission-edit',
        );
        $permissionOptions[] = array(
            'value' => Job::PERMISSION_VIEW,
            'label' => 'job.permission-view',
        );
        $form->get('name')->setValueOptions($permissionOptions);

        $request = $this->getRequest();
        if ($request->isPost()){
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $data = $form->getData();
                $name = null;
                switch ($data['name']) {
                    case Job::PERMISSION_EDIT:
                        $name = Job::PERMISSION_EDIT;
                        break;
                    case Job::PERMISSION_VIEW:
                        $name = Job::PERMISSION_VIEW;
                        break;
                }
                foreach ($data['user'] as $uid) {
                    $user = $this->em->getRepository(User::class)->find($uid);
                    if ($user->getStatus() == User::STATUS_ACTIVE && !is_null($name)) {
                        $permission = new JobPermission();
                        $permission->setUser($user);
                        $permission->setName($name);
                        $permission->setJob($job);
                        $this->em->persist($permission);
                        $this->em->flush();
                        $this->ams->flush();
                    }
                }
                return $this->redirect()->toRoute('jobs', array('action' => 'view', 'id' => $job->getId()));
            }
        }

        $viewModel = new ViewModel(array(
            'form' => $form,
            'jid' => $job->getId(),
        ));
        $viewModel->setTemplate('application/common/grant.phtml');
        return $viewModel;
    }

    public function revokeAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('jobs');
        }

        $permission = $this->em->getRepository(Permission::class)->find($id);
        if (!$permission || !$permission instanceof \Application\Entity\Job\Permission) {
            return $this->redirect()->toRoute('jobs');
        }

        $job = $permission->getJob();
        if ($this->authorizationPlugin()->isAuthorized($this->as->getIdentity(), 'job', 'delete', $job) === false) {
            return $this->alertPlugin()->alert('common.alert-access-denied', array('job.entity'), $this->url()->fromRoute('jobs', array('action' => 'view', 'id' => $job->getId())));
        }

        if ($permission->getName() == Job::PERMISSION_MANAGE) {
            return $this->alertPlugin()->alert('job.alert-owner-permissions-revoke', array('job.entity'), $this->url()->fromRoute('jobs', array('action' => 'view', 'id' => $job->getId())));
        }

        if ($job->getStatus() == Job::STATUS_CLOSED) {
            return $this->alertPlugin()->alert('job.alert-action-closed-job', array('job.entity'), $this->url()->fromRoute('jobs', array('action' => 'index', 'id' => false, 'status' => Job::STATUS_CLOSED)));
        }

        $builder = new AnnotationBuilder();
        $form = $builder->createForm(new ConfirmationForm());
        $form->setAttribute('action', $this->url()->fromRoute('jobs.permissions', array('action' => 'revoke', 'jid' => $job->getId(), 'id' => $permission->getId())));

        $request = $this->getRequest();
        if ($request->isPost()){
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $data = $form->getData();
                if ($data['confirm'] == 1) {
                    $this->em->remove($permission);
                    $this->em->flush();
                    $this->ams->flush();
                }
            }
            return $this->redirect()->toRoute('jobs', array('action' => 'view', 'id' => $job->getId()));
        }

        return $this->confirmationPlugin()->confirm(
            'common.confirm-permissions-revoke',
            array (
                array('user.entity', 'lower', 'false'),
                array($permission->getUser()->getName(), 'none', 'true'),
                array('job.entity', 'lower', 'false'),
                array($job->getName(), 'none', 'true')
            ),
            $form,
            $this->url()->fromRoute('jobs', array('action' => 'view', 'id' => $job->getId()))
        );

    }

}