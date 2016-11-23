<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Form\Annotation\AnnotationBuilder;
use Zend\Authentication\AuthenticationService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Events;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Application\Listener\ActivityListener;
use Application\Service\ActivityManagerService;
use Application\Entity\User;
use Application\Entity\Activity;
use Application\Form\LoginForm;

class UserController extends AbstractActionController
{

    private $em;
    
    private $al;

    private $ams;

    private $as;

    public function __construct(EntityManager $em, ActivityManagerService $ams, ActivityListener $al, AuthenticationService $as)
    {
        $this->em = $em;
        $this->ams = $ams;
        $this->al = $al;
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
                    if(empty($data['url'])) {
                        return $this->redirect()->toRoute('jobs');
                    } else {
                        $this->redirect()->toUrl($data['url']);
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
        $this->as->clearIdentity();
        return $this->redirect()->toRoute('login');
    }

    public static function verifyCredential(User $user, $inputPassword) 
    {
        return password_verify($inputPassword, $user->getPassword());
    }

}