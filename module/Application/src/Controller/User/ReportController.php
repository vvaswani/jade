<?php
namespace Application\Controller\User;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Form\Annotation\AnnotationBuilder;
use Zend\Authentication\AuthenticationService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Events;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Application\Service\ActivityService;
use Application\Entity\Activity;
use Application\Entity\User;
use Application\View\CsvModel;
use Application\Form\EffortReportForm;

class ReportController extends AbstractActionController
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

    public function effortAction()
    {

        $builder = new AnnotationBuilder();
        $hydrator = new DoctrineHydrator($this->em);
        $form = $builder->createForm(new EffortReportForm());
        $form->setHydrator($hydrator);
        $form->get('entity')->setLabel('user.entity');
        $usersCriteria = array();
        if ($this->as->getIdentity()->getRole() != User::ROLE_ADMINISTRATOR) {
            $usersCriteria = [
                'name'   => 'findBy',
                'params' => [
                    'criteria' => ['id' => $this->as->getIdentity()],
                ]
            ];
        }
        $form->get('entity')->setOptions(array(
            'object_manager' => $this->em,
            'target_class' => 'Application\Entity\User',
            'property' => 'name',
            'label_generator' => function ($entity) {
                return $entity->getName();
            },
            'find_method' => $usersCriteria
        ));

        $logs = array();
        $request = $this->getRequest();
        $form->setData($request->getQuery());
        if ($form->isValid()){
            $data = $form->getData();
            $to = $data['to'];
            $from = $data['from'];
            $id = $data['entity'];
            $format = $data['format'];

            if ($id) {

                $user = $this->em->getRepository(User::class)->find($id);
                if (!$user) {
                    return $this->redirect()->toRoute('users.reports', array('action' => 'effort'));
                }

                $logs = $this->em->getRepository(User::class)->getUserLogs($user->getId(), $from, $to);
            }
        }

        $view = new ViewModel(array(
            'form' => $form,
            'logs' => $logs
        ));

        if (isset($format) && $format == 'csv') {
            $view->setTerminal(true);
            $view->setTemplate('application/user/report/effort.csv');
            return $view;
        }

        return $view;
    }
}
