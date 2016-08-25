<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Doctrine\ORM\EntityManager;
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
        echo '<pre>';
        print_r( $this->em->getRepository(Job::class)->findAll() );
        echo '</pre>';
        return new ViewModel();
    }
}
