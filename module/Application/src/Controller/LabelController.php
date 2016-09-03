<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Form\Annotation\AnnotationBuilder;
use Doctrine\ORM\EntityManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Application\Entity\Label;

class LabelController extends AbstractActionController
{
    private $em;
    
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function indexAction()
    {
        $labels = $this->em->getRepository(Label::class)->findAll();
        return new ViewModel(array('labels' => $labels));
    }
    
    public function saveAction()
    {   
        $id = (int) $this->params()->fromRoute('id', 0);
        if ($id) {
            $label = $this->em->getRepository(Label::class)->find($id);        
            if (!$label) {
                return $this->redirect()->toRoute('labels');
            }
        } else {
            $label = new Label();
            $label->setCreationTime(new \DateTime("now"));
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
                return $this->redirect()->toRoute('labels');
            }
        }
         
        return new ViewModel(array(
            'form' => $form,
            'id' => $label->getId(),
        ));
    }
    
    public function deleteAction($id)
    {   
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('labels');
        }
        $label = $this->em->getRepository(Label::class)->find($id);
        $this->em->remove($label);
        $this->em->flush();
        return $this->redirect()->toRoute('labels');
    }
    
}
