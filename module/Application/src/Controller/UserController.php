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

        $redirectUri = (string) $this->params()->fromQuery('url', '');
        $form->get('url')->setValue($redirectUri);

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
                        array('source' => $source, 'result' => (string)$result->getCode())
                    );
                    $this->ams->setQueue($queue);
                    $this->ams->flush($this->ams->getQueue());

                    if ($this->as->hasIdentity()) {
                        if(empty($data['url'])) {
                            return $this->redirect()->toRoute('jobs');
                        } else {
                            $this->redirect()->toUrl($data['url']);
                        }                                    
                    }
                }
             } 
        }

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
                array('source' => $request->getServer('REMOTE_ADDR'))
            );
            $this->ams->setQueue($queue);
            $this->ams->flush();            
        }
        return $this->redirect()->toRoute('login');
    }

    public function indexAction()
    {
        /*
        // this is simpler but the other is more consistent with the ACL approach
        $identity = $this->as->getIdentity();
        if ($identity->getRole() == User::ROLE_ADMINISTRATOR) {
            $users = $this->em->getRepository(User::class)->findBy(array(), 
                array('creationTime' => 'DESC'));
        } else {
            $users = array($identity);            
        }
        */

        $results = $this->em->getRepository(User::class)->findBy(array(), array('creationTime' => 'DESC'));
        $users = array();
        foreach ($results as $user) {
            if ($this->authorizationPlugin()->isAuthorized($this->as->getIdentity(), 'user', 'save', $user) !== false) {
                $users[] = $user;
            }
        }
        return new ViewModel(array('users' => $users));
    }

    public function saveAction()
    {   
        $id = (int) $this->params()->fromRoute('id', 0);
        /*
        $identity = $this->as->getIdentity();
        if (!$id && $identity->getRole() != User::ROLE_ADMINISTRATOR) {
            return $this->alertPlugin()->alert('common.alert-access-denied', array(), $this->url()->fromRoute('users'));
        }
        */
        $user = $this->em->getRepository(User::class)->find($id);   
        if ($this->authorizationPlugin()->isAuthorized($this->as->getIdentity(), null, null, $user) === false) {
            return $this->alertPlugin()->alert('common.alert-access-denied', array('user.entity'), $this->url()->fromRoute('users'));
        }

        if (!$user) {
            $user = new User();
            $user->setCreationTime(new \DateTime("now"));
            $passwordRequired = true;  // new user creation
            $permission = new UserPermission();
            $permission->setUser($user);
            $permission->setName(User::PERMISSION_EDIT);
            $permission->setEntity($user);
            $user->setPermissions(array($permission));            
        } else {
            $passwordHash = $user->getPassword();
            $passwordRequired = false;  // existing user modification
            $role = $user->getRole();
        }
        $builder = new AnnotationBuilder();
        $hydrator = new DoctrineHydrator($this->em);
        $form = $builder->createForm($user);
        $form->setHydrator($hydrator);
        $form->getInputFilter()->get('password')->setRequired($passwordRequired);
        $form->get('role')->setValueOptions(array(
            User::ROLE_ADMINISTRATOR => 'user.role-administrator',
            User::ROLE_EMPLOYEE => 'user.role-employee',
            User::ROLE_CUSTOMER => 'user.role-customer',
        ));
        
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
                if ($this->authorizationPlugin()->isAuthorized($this->as->getIdentity(), 'user', 'delete', $user) === false) {
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
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('users');
        }

        $user = $this->em->getRepository(User::class)->find($id);
        if (!$user) {
            return $this->redirect()->toRoute('users');
        }

        if ($this->authorizationPlugin()->isAuthorized($this->as->getIdentity(), null, null, $user) === false) {
            return $this->alertPlugin()->alert('common.alert-access-denied', array('user.entity'), $this->url()->fromRoute('users'));
        }

        $request = $this->getRequest();

        // check for at least one administrator
        $users = $this->em->getRepository(User::class)->findAll();
        if (count($users) == 1) {
            return $this->alertPlugin()->alert('user.alert-min-threshold', array('user.entity'), $this->url()->fromRoute('users')); 
        }

        // TODO: check for open owned jobs

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
            array ('user.entity', $user->getUsername()), 
            $form,
            $this->url()->fromRoute('users')
        );
    }

    public function deactivateAction()
    {   
        $identity = $this->as->getIdentity();

        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('users');
        }

        $user = $this->em->getRepository(User::class)->find($id);
        if (!$user) {
            return $this->redirect()->toRoute('users');
        }

        if ($this->authorizationPlugin()->isAuthorized($this->as->getIdentity(), null, null, $user) === false) {
            return $this->alertPlugin()->alert('common.alert-access-denied', array('user.entity'), $this->url()->fromRoute('users'));
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
            array ('user.entity', $user->getUsername()), 
            $form,
            $this->url()->fromRoute('users')
        );
    }    

    public function activateAction()
    {   
        $identity = $this->as->getIdentity();

        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('users');
        }

        $user = $this->em->getRepository(User::class)->find($id);
        if (!$user) {
            return $this->redirect()->toRoute('users');
        }

        if ($this->authorizationPlugin()->isAuthorized($this->as->getIdentity(), null, null, $user) === false) {
            return $this->alertPlugin()->alert('common.alert-access-denied', array('user.entity'), $this->url()->fromRoute('users'));
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
            array ('user.entity', $user->getUsername()), 
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