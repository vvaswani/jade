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
use Application\Listener\ActivityListener;
use Application\Service\ActivityManagerService;
use Application\Form\ConfirmationForm;

class LabelController extends AbstractActionController
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
        $this->em->getEventManager()->addEventListener(
            array(Events::onFlush), $this->al);
    }

    public function indexAction()
    {
        $labels = $this->em->getRepository(Label::class)->findBy(array(), array('created' => 'DESC'));
        return new ViewModel(array('labels' => $labels));
    }
    
    public function saveAction()
    {   
        $id = (int) $this->params()->fromRoute('id', 0);
        $label = $this->em->getRepository(Label::class)->find($id);    
        if (!$label) {
            $label = new Label();
            $label->setCreated(new \DateTime("now"));
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
                $this->ams->flush($this->al->getQueue());
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
                    $this->ams->flush($this->al->getQueue());
                } 
            }
            return $this->redirect()->toRoute('labels');
        } 

        return $this->confirmationPlugin()->confirm(
            'common.confirm-delete', 
            array ('label.entity', $label->getName()), 
            $form,
            $this->url()->fromRoute('labels')
        );
    }
    
}
