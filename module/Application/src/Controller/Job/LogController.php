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
use Application\Entity\Job\Log;
use Application\Entity\Activity;
use Application\Form\ConfirmationForm;

class LogController extends AbstractActionController
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
        $jid = (int) $this->params()->fromRoute('jid', 0);
        if (!$jid) {
            return $this->redirect()->toRoute('jobs');
        }

        $job = $this->em->getRepository(Job::class)->find($jid);
        if (!$job) {
            return $this->redirect()->toRoute('jobs');
        }

        if ($this->authorizationPlugin()->isAuthorized($this->as->getIdentity(), 'job', 'view', $job) === false) {
            return $this->alertPlugin()->alert('common.alert-access-denied', array('job.entity'), $this->url()->fromRoute('jobs'));
        }

        $logs = $this->em->getRepository(Log::class)->findBy(array('job' => $job->getId()), array('date' => 'DESC'));
        return new ViewModel(array('job' => $job, 'logs' => $logs));
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

        if ($job->getStatus() == Job::STATUS_CLOSED) {
            return $this->alertPlugin()->alert('job.alert-action-closed-job', array('job.entity'), $this->url()->fromRoute('jobs', array('action' => 'index', 'id' => false, 'status' => Job::STATUS_CLOSED)));
        }

        $id = (int) $this->params()->fromRoute('id', 0);
        $log = $this->em->getRepository(Log::class)->find($id);

        if (!$log) {
            if (!($this->authorizationPlugin()->isAuthorized($this->as->getIdentity(), 'job', 'view', $job) && $this->as->getIdentity()->getRole() != \Application\Entity\User::ROLE_CUSTOMER)) {
                return $this->alertPlugin()->alert('common.alert-access-denied', array('job.entity'), $this->url()->fromRoute('jobs'));
            }
        }

        if ($log) {
            if (!($this->authorizationPlugin()->isAuthorized($this->as->getIdentity(), 'job', 'view', $job) && $log->getUser() == $this->as->getIdentity()) && !($this->authorizationPlugin()->isAuthorized($this->as->getIdentity(), 'job', 'delete', $job))) {
                return $this->alertPlugin()->alert('common.alert-access-denied', array('job.entity'), $this->url()->fromRoute('jobs'));
            }
        }

        if (!$log) {
            $log = new Log();
            $log->setCreationTime(new \DateTime("now"));
        }

        $builder = new AnnotationBuilder();
        $hydrator = new DoctrineHydrator($this->em);
        $form = $builder->createForm($log);
        $form->setHydrator($hydrator);
        $form->bind($log);
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $log->setJob($job);
                if (is_null($log->getUser())) {
                    $log->setUser($this->as->getIdentity());
                }
                $this->em->persist($log);
                $this->em->flush();
                $this->ams->flush();
                return $this->redirect()->toRoute('jobs', array('action' => 'view', 'id' => $job->getId()));
            }
        }

        return new ViewModel(array(
            'form' => $form,
            'jid'  => $job->getId(),
            'id' => $log->getId()
        ));
    }

    public function deleteAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('jobs');
        }

        $log = $this->em->getRepository(Log::class)->find($id);
        if (!$log) {
            return $this->redirect()->toRoute('jobs');
        }

        $job = $log->getJob();
        if (!($this->authorizationPlugin()->isAuthorized($this->as->getIdentity(), 'job', 'view', $job) && $log->getUser() == $this->as->getIdentity() || ($this->authorizationPlugin()->isAuthorized($this->as->getIdentity(), 'job', 'delete', $job)))) {
            return $this->alertPlugin()->alert('common.alert-access-denied', array('job.entity'), $this->url()->fromRoute('jobs'));
        }

        if ($job->getStatus() == Job::STATUS_CLOSED) {
            return $this->alertPlugin()->alert('job.alert-action-closed-job', array('job.entity'), $this->url()->fromRoute('jobs', array('action' => 'index', 'id' => false, 'status' => Job::STATUS_CLOSED)));
        }

        $builder = new AnnotationBuilder();
        $form = $builder->createForm(new ConfirmationForm());
        $form->setAttribute('action', $this->url()->fromRoute('jobs.logs', array('action' => 'delete', 'id' => $id, 'jid' => $log->getJob()->getId())));

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $data = $form->getData();
                if ($data['confirm'] == 1) {
                    $this->em->remove($log);
                    $this->em->flush();
                    $this->ams->flush();
                    return $this->redirect()->toRoute('jobs.logs', array('action' => 'index', 'jid' => $log->getJob()->getId()));
                }
            }
            return $this->redirect()->toRoute('jobs');
        }

        return $this->confirmationPlugin()->confirm(
            'common.confirm-delete',
            array (
                array('log.entity', 'lower', 'false'),
                array($log->getDescription(), 'none', 'true'),
            ),
            $form,
            $this->url()->fromRoute('jobs')
        );
    }

}