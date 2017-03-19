<?php
namespace Application\Service;

use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Role\GenericRole as Role;
use Zend\Permissions\Acl\Resource\GenericResource as Resource;
use Zend\View\Model\ViewModel;
use Application\Entity\Privilege;

class AuthorizationService
{
    private $acl;

    public function __construct()
    {
        $acl = new Acl();
        $yellow = new Role(Privilege::NAME_YELLOW);
        $orange = new Role(Privilege::NAME_ORANGE);
        $green = new Role(Privilege::NAME_GREEN);
        $red = new Role(Privilege::NAME_RED);
        $acl->addRole($red);
        $acl->addRole($yellow);
        $acl->addRole($orange, $yellow);
        $acl->addRole($green, $orange);

        $acl->addResource(new Resource('job'));
        $acl->allow($yellow, 'job', array('job.view', 'job.index', 'file.download'));
        $acl->allow($orange, 'job', array('job.save', 'file.save', 'file.delete'));
        $acl->allow($green, 'job', array('job.delete', 'job.close', 'job.open'));

        $this->acl = $acl;
    } 

    public function getAcl()
    {
        return $this->acl;
    }
}