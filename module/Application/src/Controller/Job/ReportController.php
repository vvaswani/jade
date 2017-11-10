<?php
namespace Application\Controller\Job;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Form\Annotation\AnnotationBuilder;
use Zend\Authentication\AuthenticationService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Events;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Application\Service\ActivityService;
use Application\Entity\Job;
use Application\Entity\Job\Log;
use Application\Entity\Activity;
use Application\Form\EffortReportForm;
use Application\View\Model\CsvModel;

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
        $form->get('entity')->setLabel('job.entity');
        $form->get('entity')->setOptions(array(
            'object_manager' => $this->em,
            'target_class' => 'Application\Entity\Job',
            'property' => 'name',
            'label_generator' => function ($entity) {
                return sprintf('#%d: %s', $entity->getId(), $entity->getName());
            },
	        'find_method'    => [
	            'name'   => 'getAuthorizedJobs',
	            'params' => [
                	'identity' => $this->as->getIdentity(),
                	'status' => Job::STATUS_OPEN,
                	'controller' => 'job',
                	'action' => 'view',
	            ],
	        ],
        ));

        $logs = array();
        $request = $this->getRequest();
        $form->setData($request->getQuery());
        if ($form->isValid()) {
            $data = $form->getData();
            $to = $data['to'];
            $from = $data['from'];
            $id = $data['entity'];
            $format = $data['format'];

            if ($id) {
                $job = $this->em->getRepository(Job::class)->find($id);
                if (!$job) {
                    return $this->redirect()->toRoute('jobs.reports', array('action' => 'effort'));
                }

                $logs = $this->em->getRepository(Job::class)->getJobLogs($job->getId(), $from, $to);
                $queue[] = array(
                    Activity::OPERATION_REPORT,
                    new \DateTime("now"),
                    $job,
                    null,
                    array('name' => 'job-effort')
                );
                $this->ams->setQueue($queue);
                $this->ams->flush();

            }
        }

        $view = new ViewModel(array(
            'form' => $form,
            'logs' => $logs
        ));

        if (isset($format) && $format == 'csv') {
            $data = array();
            if (count($logs)) {
                foreach ($logs as $log) {
                    $data[] = [
                        $log->getUser()->getName(),
                        $log->getDate()->format('d-m-Y'),
                        $log->getEffort(),
                        $log->getDescription()
                    ];
                }
            }
            $view = new CsvModel(array(
                'form' => $form,
                'data' => $data
            ));
        }

        return $view;
    }
}
