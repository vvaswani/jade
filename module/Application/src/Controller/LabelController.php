<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Form\Annotation\AnnotationBuilder;
use Zend\Authentication\AuthenticationService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Events;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Application\Entity\Label;
use Application\Entity\Permission\Label as LabelPermission;
use Application\Service\ActivityService;
use Application\Form\ConfirmationForm;

class LabelController extends AbstractActionController
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
            return $this->alertPlugin()->alert('common.alert-access-denied', array('label.entity'), $this->url()->fromRoute('labels'));
        }  

        $labels = $this->em->getRepository(Label::class)->findBy(array(), array('creationTime' => 'DESC'));
        return new ViewModel(array('labels' => $labels));
    }
    
    public function saveAction()
    {   
        $id = (int) $this->params()->fromRoute('id', 0);
        $label = $this->em->getRepository(Label::class)->find($id);    

        if ($this->authorizationPlugin()->isAuthorized($this->as->getIdentity(), null, null, $label) === false) {
            return $this->alertPlugin()->alert('common.alert-access-denied', array('label.entity'), $this->url()->fromRoute('labels'));
        }            
        
        if (!$label) {
            $label = new Label();
            $label->setCreationTime(new \DateTime("now"));
            $permission = new LabelPermission();
            $permission->setUser($this->as->getIdentity());
            $permission->setName(Label::PERMISSION_MANAGE);
            $permission->setLabel($label);
            $label->setPermissions(array($permission));
        } 

        $builder = new AnnotationBuilder();
        $hydrator = new DoctrineHydrator($this->em);
        $form = $builder->createForm($label);
        $form->setHydrator($hydrator);
        $form->bind($label);
        $request = $this->getRequest();
        if ($request->isPost()){
            $form->setData($request->getPost());
            if ($form->isValid()){
                $this->em->persist($label); 
                $this->em->flush(); 
                $this->ams->flush();
                return $this->redirect()->toRoute('labels');
            }
        }
         
        return new ViewModel(array(
            'form' => $form,
            'id' => $label->getId(),
        ));
    }
    
    public function deleteAction()
    {   
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('labels');
        }
        $label = $this->em->getRepository(Label::class)->find($id);
        if (!$label) {
            return $this->redirect()->toRoute('labels');
        } 

        if ($this->authorizationPlugin()->isAuthorized($this->as->getIdentity(), null, null, $label) === false) {
            return $this->alertPlugin()->alert('common.alert-access-denied', array('label.entity'), $this->url()->fromRoute('labels'));
        }

        $builder = new AnnotationBuilder();
        $form = $builder->createForm(new ConfirmationForm());
        $form->setAttribute('action', $this->url()->fromRoute('labels', array('action' => 'delete', 'id' => $id)));
        
        $request = $this->getRequest();
        if ($request->isPost()){
            $form->setData($request->getPost());
            if ($form->isValid()) { 
                $data = $form->getData();
                if ($data['confirm'] == 1) {
                    $this->em->remove($label);
                    $this->em->flush();                    
                    $this->ams->flush();
                } 
            }
            return $this->redirect()->toRoute('labels');
        } 

        return $this->confirmationPlugin()->confirm(
            'common.confirm-delete', 
            array (
                array('label.entity', 'lower', 'false'),
                array($label->getName(), 'none', 'true'),
            ),            
            $form,
            $this->url()->fromRoute('labels')
        );
    }
    
}
