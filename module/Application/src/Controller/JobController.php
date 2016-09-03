<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Form\Annotation\AnnotationBuilder;
use Doctrine\ORM\EntityManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Application\Entity\Job;

class JobController extends AbstractActionController
{
    private $em;
    
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function indexAction()
    {
        $jobs = $this->em->getRepository(Job::class)->findAll();
        return new ViewModel(array('jobs' => $jobs));
    }
    
    public function viewAction()
    {   
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('jobs');
        }
        $job = $this->em->getRepository(Job::class)->find($id);
        return new ViewModel(array('job' => $job));
    }    
    
    public function saveAction()
    {   
        $id = (int) $this->params()->fromRoute('id', 0);
        if ($id) {
            $job = $this->em->getRepository(Job::class)->find($id);        
            if (!$job) {
                return $this->redirect()->toRoute('jobs');
            }
        } else {
            $job = new Job();
            $job->setCreationTime(new \DateTime("now"));
        }
        
        $builder = new AnnotationBuilder();
        $hydrator = new DoctrineHydrator($this->em);
        $form = $builder->createForm($job);
        $form->setHydrator($hydrator);
        $form->bind($job);
        $request = $this->getRequest();
        if ($request->isPost()){
            $form->setData($request->getPost());
            if ($form->isValid()){  
                $this->em->persist($job); 
                $this->em->flush();
                return $this->redirect()->toRoute('jobs');
            }
        }
         
        return new ViewModel(array(
            'form' => $form,
            'id' => $job->getId(),
        ));
    }
    
    public function deleteAction($id)
    {   
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('jobs');
        }
        $job = $this->em->getRepository(Job::class)->find($id);
        $this->em->remove($job);
        $this->em->flush();
        return $this->redirect()->toRoute('jobs');
    }
    
}
