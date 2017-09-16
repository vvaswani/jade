<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Doctrine\ORM\EntityManager;
use Application\Service\ActivityService;
use Zend\Authentication\AuthenticationService;
use Application\Entity\Job;
use Application\Entity\Template;
use Application\Entity\Label;
use Application\Entity\User;
use Application\Entity\Activity;

class IndexController extends AbstractActionController
{

    private $as;

    private $em;

    public function __construct(EntityManager $em, ActivityService $ams, AuthenticationService $as)
    {
        $this->em = $em;
        $this->as = $as;
    }

    public function indexAction()
    {
        $this->layout()->setVariable('home', 'true');
        return new ViewModel();
    }

    public function dashboardAction()
    {
    	$counts = [
    		'jobs' => 0,
    		'labels' => 0,
    		'templates' => 0,
    		'users' => 0,
            'activities' => 0,
    	];

        $grants = [
            'jobs' => [],
            'labels' => [],
            'templates' => [],
        ];

        // count open jobs to which user has access
    	if ($this->authorizationPlugin()->isAuthorized($this->as->getIdentity(), 'job', 'index')) {
		    $results = $this->em->getRepository(Job::class)->findBy(array(), array('creationTime' => 'DESC'));
		    foreach ($results as $job) {
	            if ($this->authorizationPlugin()->isAuthorized($this->as->getIdentity(), 'job', 'view', $job) !== false) {
	                $counts['jobs']++;
                    $grants['jobs'][] = $job->getId();
	            }
		    }
		}

        // count templates to which user has access
    	if ($this->authorizationPlugin()->isAuthorized($this->as->getIdentity(), 'template', 'index')) {
		    $results = $this->em->getRepository(Template::class)->findBy(array(), array('creationTime' => 'DESC'));
	        $counts['templates'] = count($results);
            foreach ($results as $template) {
                $grants['templates'][] = $template->getId();
            }
    	}

        // count labels to which user has access
    	if ($this->authorizationPlugin()->isAuthorized($this->as->getIdentity(), 'label', 'index')) {
		    $results = $this->em->getRepository(Label::class)->findBy(array(), array('creationTime' => 'DESC'));
	        $counts['labels'] = count($results);
            foreach ($results as $label) {
                $grants['labels'][] = $label->getId();
            }
    	}

        // count user accounts if user is administrator
    	$identity = $this->as->getIdentity();
		if ($identity->getRole() == $identity::ROLE_ADMINISTRATOR) {
            $results = $this->em->getRepository(User::class)->findBy(array(),
                array('creationTime' => 'DESC'));
	        $counts['users'] = count($results);
		}

        // retrieve recent activities
        // get activity records in batches and test user access to each activity
        // until recent activity list is full
        // exclude certain types of activities/entities from processing
        $excludedActivities = [Activity::OPERATION_LOGIN, Activity::OPERATION_LOGOUT];
        $excludedEntities = [Activity::ENTITY_TYPE_USER];
        $resultOffset = 0;
        $resultBatchSize = 50;
        $maxRecentActivities = 10;
        $recentActivities = [];
        while ($counts['activities'] < $maxRecentActivities) {
            $activities = $this->em->getRepository(Activity::class)
                               ->getRecentActivities($resultOffset, $resultBatchSize);
            if (count($activities) == 0) {
                break;
            }
            foreach($activities as $activity) {
                // check if activity/entity is excluded
                $entityType = $activity->getEntityType();
                $associatedEntityType = $activity->getAssociatedEntityType();
                if (in_array($activity->getOperation(), $excludedActivities) || in_array($entityType, $excludedEntities)) {
                    continue;
                }

                // check for entity access
                switch($entityType) {
                    case Activity::ENTITY_TYPE_JOB:
                        if (in_array($activity->getEntityId(), $grants['jobs'])) {
                            $recentActivities[] = $activity;
                            $counts['activities']++;
                        }
                        break;
                    case Activity::ENTITY_TYPE_FILE:
                        if (in_array($activity->getAssociatedEntityId(), $grants['jobs'])) {
                            $recentActivities[] = $activity;
                            $counts['activities']++;
                        }
                        break;
                    case Activity::ENTITY_TYPE_LABEL:
                        if (in_array($activity->getEntityId(), $grants['labels'])) {
                            $recentActivities[] = $activity;
                            $counts['activities']++;
                        }
                        break;
                    case Activity::ENTITY_TYPE_TEMPLATE:
                        if (in_array($activity->getEntityId(), $grants['templates'])) {
                            $recentActivities[] = $activity;
                            $counts['activities']++;
                        }
                        break;
                }
                // check if recent activity list is full
                if ($counts['activities'] == $maxRecentActivities) {
                    break;
                }
            }
            $resultOffset += $resultBatchSize;
        }

        return new ViewModel(array('counts' => $counts, 'activities' => $recentActivities));
    }
}
