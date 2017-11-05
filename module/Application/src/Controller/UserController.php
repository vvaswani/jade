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
use Application\Entity\User;
use Application\Entity\Activity;
use Application\Entity\Job;
use Application\Entity\Permission\User as UserPermission;
use Application\Form\LoginForm;
use Application\Form\ConfirmationForm;

class UserController extends AbstractActionController
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

    public function loginAction()
    {
        $builder = new AnnotationBuilder();
        $form = $builder->createForm(new LoginForm());
        $request = $this->getRequest();

        $redirectUri = (string) $this->params()->fromQuery('continue', '');
        $form->get('continue')->setValue($redirectUri);

        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $data = $form->getData();
                $adapter = $this->as->getAdapter();
                $adapter->setIdentity($data['username']);
                $adapter->setCredential($data['password']);
                $result = $this->as->authenticate();

                if (!$result->isValid()) {
                    $form->get('password')->setMessages($result->getMessages());
                } else {
                    $user = $this->as->getIdentity();
                    $queue = $this->ams->getQueue();
                    $source = $request->getServer('REMOTE_ADDR');
                    $queue[] = array(
                        Activity::OPERATION_LOGIN,
                        new \DateTime("now"),
                        $user,
                        null,
                        array('result' => (string)$result->getCode())
                    );
                    $this->ams->setQueue($queue);
                    $this->ams->flush($this->ams->getQueue());

                    if ($this->as->hasIdentity()) {
                        if(empty($data['continue'])) {
                            return $this->redirect()->toRoute('dashboard');
                        } else {
                            $this->redirect()->toUrl($data['continue']);
                        }
                    }
                }
             }
        }

        $this->layout()->setVariable('login', 'true');
        return new ViewModel(array(
            'form' => $form
        ));
    }

    public function logoutAction()
    {
        if ($this->as->hasIdentity()) {
            $clone = clone $this->as->getIdentity();
            $this->as->clearIdentity();
            $request = $this->getRequest();
            $queue[] = array(
                Activity::OPERATION_LOGOUT,
                new \DateTime("now"),
                $clone,
                null,
                null
            );
            $this->ams->setQueue($queue);
            $this->ams->flush();
        }
        return $this->redirect()->toRoute('login');
    }

    public function indexAction()
    {
        $identity = $this->as->getIdentity();
        if ($identity->getRole() == User::ROLE_ADMINISTRATOR) {
            $users = $this->em->getRepository(User::class)->findBy(array(),
                array('creationTime' => 'DESC'));
        } else {
            $users = array($identity);
        }
        return new ViewModel(array('users' => $users));
    }

    public function saveAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        $identity = $this->as->getIdentity();
        if (($identity->getRole() != User::ROLE_ADMINISTRATOR) && ($id != $identity->getId())) {
            return $this->alertPlugin()->alert('common.alert-access-denied', array(), $this->url()->fromRoute('users'));
        }

        if (!$id) {
            $user = new User();
            $user->setCreationTime(new \DateTime("now"));
            $passwordRequired = true;  // new user creation
        } else {
            $user = $this->em->getRepository(User::class)->find($id);
            $passwordHash = $user->getPassword();
            $passwordRequired = false;  // existing user modification
            $role = $user->getRole();
        }
        $builder = new AnnotationBuilder();
        $hydrator = new DoctrineHydrator($this->em);
        $form = $builder->createForm($user);
        $form->setHydrator($hydrator);
        $form->getInputFilter()->get('password')->setRequired($passwordRequired);
        if ($identity->getRole() == User::ROLE_ADMINISTRATOR) {
            $form->get('role')->setValueOptions(array(
                User::ROLE_ADMINISTRATOR => 'user.role-administrator',
                User::ROLE_EMPLOYEE => 'user.role-employee',
                User::ROLE_CUSTOMER => 'user.role-customer',
            ));
        }

        // TODO use a @UniqueObject annotation once it works
        $form->getInputFilter()->get('username')->getValidatorChain()->attach(
             new \DoctrineModule\Validator\UniqueObject(array(
                'use_context' => true,
                'fields' => 'username',
                'object_repository' => $this->em->getRepository('Application\Entity\User'),
                'object_manager' => $this->em,
            ))
        );
        $form->setInputFilter($form->getInputFilter());
        $form->bind($user);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                if (!empty($user->getPassword())) {
                    $passwordHash = password_hash($user->getPassword(), PASSWORD_DEFAULT);
                }
                $user->setPassword($passwordHash);
                $user->setStatus(User::STATUS_ACTIVE);
                // to avoid POST manual injection for role change
                // explicitly reset the role
                if ($identity->getRole() != User::ROLE_ADMINISTRATOR) {
                    $user->setRole($role);
                }
                $this->em->persist($user);
                $this->em->flush();
                $this->ams->flush();
                return $this->redirect()->toRoute('users');
            }
        }

        return new ViewModel(array(
            'form' => $form,
            'id' => $user->getId(),
            'user' => $user
        ));
    }

    public function deleteAction()
    {
        $identity = $this->as->getIdentity();

        if ($identity->getRole() != User::ROLE_ADMINISTRATOR) {
            return $this->alertPlugin()->alert('common.alert-access-denied', array(), $this->url()->fromRoute('users'));
        }

        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('users');
        }

        $user = $this->em->getRepository(User::class)->find($id);
        if (!$user) {
            return $this->redirect()->toRoute('users');
        }

        $request = $this->getRequest();

        // check for at least one administrator
        if ($user->getRole() == User::ROLE_ADMINISTRATOR) {
            $users = $this->em->getRepository(User::class)->findBy(array('role' => User::ROLE_ADMINISTRATOR));
            if (count($users) == 1) {
                return $this->alertPlugin()->alert('user.alert-min-threshold', array('user.role-administrator'), $this->url()->fromRoute('users'));
            }
        }

        $builder = new AnnotationBuilder();
        $form = $builder->createForm(new ConfirmationForm());
        $form->setAttribute('action', $this->url()->fromRoute('users', array('action' => 'delete', 'id' => $id)));

        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $data = $form->getData();
                if ($data['confirm'] == 1) {
                    $this->em->remove($user);
                    $this->em->flush();
                    $this->ams->flush();
                }
            }
            return $this->redirect()->toRoute('users');
        }

        return $this->confirmationPlugin()->confirm(
            'common.confirm-delete',
            array (
                array('user.entity', 'lower', 'false'),
                array($user->getUsername(), 'none', 'true'),
            ),
            $form,
            $this->url()->fromRoute('users')
        );
    }

    public function deactivateAction()
    {
        $identity = $this->as->getIdentity();

        if ($identity->getRole() != User::ROLE_ADMINISTRATOR) {
            return $this->alertPlugin()->alert('common.alert-access-denied', array(), $this->url()->fromRoute('users'));
        }

        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('users');
        }

        $user = $this->em->getRepository(User::class)->find($id);
        if (!$user) {
            return $this->redirect()->toRoute('users');
        }

        // check for at least one administrator
        if ($user->getRole() == User::ROLE_ADMINISTRATOR) {
            $users = $this->em->getRepository(User::class)->findBy(array('role' => User::ROLE_ADMINISTRATOR));
            if (count($users) == 1) {
                return $this->alertPlugin()->alert('user.alert-min-threshold', array('user.role-administrator'), $this->url()->fromRoute('users'));
            }
        }

        $builder = new AnnotationBuilder();
        $form = $builder->createForm(new ConfirmationForm());
        $form->setAttribute('action', $this->url()->fromRoute('users', array('action' => 'deactivate', 'id' => $id)));

        $request = $this->getRequest();
        if ($request->isPost()){
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $data = $form->getData();
                if ($data['confirm'] == 1) {
                    $user->setStatus(User::STATUS_INACTIVE);
                    $this->em->persist($user);
                    $this->em->flush();
                    $this->ams->flush();
                }
            }
            return $this->redirect()->toRoute('users');
        }

        return $this->confirmationPlugin()->confirm(
            'user.confirm-deactivate',
            array (
                array('user.entity', 'lower', 'false'),
                array($user->getUsername(), 'none', 'true'),
            ),
            $form,
            $this->url()->fromRoute('users')
        );
    }

    public function activateAction()
    {
        $identity = $this->as->getIdentity();

        if ($identity->getRole() != User::ROLE_ADMINISTRATOR) {
            return $this->alertPlugin()->alert('common.alert-access-denied', array(), $this->url()->fromRoute('users'));
        }

        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('users');
        }

        $user = $this->em->getRepository(User::class)->find($id);
        if (!$user) {
            return $this->redirect()->toRoute('users');
        }

        $builder = new AnnotationBuilder();
        $form = $builder->createForm(new ConfirmationForm());
        $form->setAttribute('action', $this->url()->fromRoute('users', array('action' => 'activate', 'id' => $id)));

        $request = $this->getRequest();
        if ($request->isPost()){
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $data = $form->getData();
                if ($data['confirm'] == 1) {
                    $user->setStatus(User::STATUS_ACTIVE);
                    $this->em->persist($user);
                    $this->em->flush();
                    $this->ams->flush();
                }
            }
            return $this->redirect()->toRoute('users');
        }

        return $this->confirmationPlugin()->confirm(
            'user.confirm-activate',
            array (
                array('user.entity', 'lower', 'false'),
                array($user->getUsername(), 'none', 'true'),
            ),
            $form,
            $this->url()->fromRoute('users')
        );
    }

    public static function verifyCredential(User $user, $inputPassword)
    {
        if ($user->getStatus() == User::STATUS_ACTIVE) {
            return password_verify($inputPassword, $user->getPassword());
        }
        return false;
    }

}