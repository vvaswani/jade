<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Form\Annotation\AnnotationBuilder;
use Doctrine\ORM\EntityManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Application\Entity\Label;
use Application\Form\ConfirmationForm;

class LabelController extends AbstractActionController
{
    private $em;
    
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
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
<<<<<<< HEAD
<<<<<<< HEAD
                $this->em->flush();
                // TODO add activity stream logging        
=======
                $this->em->flush();            
>>>>>>> Added activity recording service and activity stream in case view
=======
                $this->em->flush();
                // TODO add activity stream logging        
>>>>>>> Updated service
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
<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> Redefined confirmation form as modal invoked via AJAX. Updated across controllers. Closes #51.
        if (!$label) {
            return $this->redirect()->toRoute('labels');
        } 

        $builder = new AnnotationBuilder();
        $form = $builder->createForm(new ConfirmationForm());
        $form->setAttribute('action', $this->url()->fromRoute('labels', array('action' => 'delete', 'id' => $id)));
        $form->get('cancelTo')->setValue($this->url()->fromRoute('labels'));
        
        $request = $this->getRequest();
        if ($request->isPost()){
            $form->setData($request->getPost());
            if ($form->isValid()) { 
                $data = $form->getData();
                if ($data['confirm'] == 1) {
                    $this->em->remove($label);
                    $this->em->flush();                    
                } 
            }
            return $this->redirect()->toRoute('labels');
        } 

        $viewModel = new ViewModel(array(
            'form' => $form,
            'entityType' => 'label',
            'entityDescriptor' => $label->getName(),            
        ));
        $viewModel->setTerminal($request->isXmlHttpRequest());
        $viewModel->setTemplate('application/common/confirm.phtml');
        return $viewModel;
<<<<<<< HEAD
=======
        $this->em->remove($label);
        $this->em->flush();
        // TODO add activity stream logging        
        return $this->redirect()->toRoute('labels');
>>>>>>> Updated service
=======
>>>>>>> Redefined confirmation form as modal invoked via AJAX. Updated across controllers. Closes #51.
    }
    
}
