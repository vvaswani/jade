<?php
namespace Application\Service;

use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Role\GenericRole as Role;
use Zend\Permissions\Acl\Resource\GenericResource as Resource;
use Zend\View\Model\ViewModel;
use Application\Entity\User;
use Application\Entity\Job;
use Application\Entity\Label;

class AuthorizationService
{
    private $acl;
    private $systemAcl;

    public function __construct()
    {
        $systemAcl = new Acl();
        $systemAdministrator = new Role(User::ROLE_ADMINISTRATOR);
        $systemEmployee = new Role(User::ROLE_EMPLOYEE);
        $systemCustomer = new Role(User::ROLE_CUSTOMER);
        $systemAcl->addRole($systemCustomer);
        $systemAcl->addRole($systemEmployee, $systemCustomer);

        /* labels */
        $systemAcl->addResource('label');
        $systemAcl->allow($systemCustomer, 'label', array('index'));
        $systemAcl->allow($systemEmployee, 'label', array('save'));  // employees can create labels

            $labelOwner = new Role(Label::PERMISSION_MANAGE);
            $systemAcl->addRole($labelOwner, $systemCustomer);
            $systemAcl->allow($labelOwner, 'label', array('delete', 'save'));

        /* jobs */
        $systemAcl->addResource('job');
        $systemAcl->allow($systemCustomer, 'job', array('index'));
        $systemAcl->allow($systemEmployee, 'job', array('save'));  // employees can create jobs

            $jobOwner = new Role(Job::PERMISSION_MANAGE);
            $jobCollaboratorEditor = new Role(Job::PERMISSION_EDIT);
            $jobCollaboratorViewer = new Role(Job::PERMISSION_VIEW);
            $systemAcl->addRole($jobCollaboratorViewer, $systemCustomer);
            $systemAcl->addRole($jobCollaboratorEditor, $jobCollaboratorViewer);
            $systemAcl->addRole($jobOwner, $jobCollaboratorEditor);

            $systemAcl->allow($jobCollaboratorViewer, 'job', array('view'));
            $systemAcl->allow($jobCollaboratorEditor, 'job', array('save'));
            $systemAcl->allow($jobOwner, 'job', array('delete', 'close', 'open'));

            $systemAcl->addResource('file');
            $systemAcl->allow($jobCollaboratorViewer, 'file', array('download'));
            $systemAcl->allow($jobCollaboratorEditor, 'file', array('save', 'delete'));

        /* users */
        $systemAcl->addResource('user');
        $systemAcl->allow($systemCustomer, 'user', array('index')); // create functionality is only available to administrators

            $userOwner = new Role(User::PERMISSION_MANAGE);
            $userEditor = new Role(User::PERMISSION_EDIT);
            $systemAcl->addRole($userEditor, $systemCustomer);
            $systemAcl->addRole($userOwner, $userEditor);
            $systemAcl->allow($userEditor, 'user', array('save'));
            $systemAcl->allow($userOwner, 'user', array('delete', 'deactivate', 'activate'));

        $systemAcl->addRole($systemAdministrator, array($systemEmployee, $jobOwner, $labelOwner, $userOwner));

        /* config */
        $systemAcl->addResource('config');
        $systemAcl->allow($systemAdministrator, 'config', array('index'));

        $this->systemAcl = $systemAcl;

    } 

    public function getSystemAcl()
    {
        return $this->systemAcl;
    }    

    public function isAuthorized($identity, $controller, $action, $entity = null)
    {

        $aclResource = strtolower($controller);
        $aclPermission = strtolower($action);

        $systemAcl = $this->getSystemAcl();

        if (!is_null($entity)) {

            $entityClassSegments = explode('\\', get_class($entity));     
            $entityName = array_pop($entityClassSegments);            
 
            $permissions = $entity->getUserPermissions($identity);
            foreach ($permissions as $permission) {
                if ($systemAcl->isAllowed($permission->getName(), $aclResource, $aclPermission)) {
                    return true; 
                }                        
            } 
        }

        if ($systemAcl->isAllowed($identity->getRole(), $aclResource, $aclPermission)) {
            return true;
        }

        // default deny
        return false;        
    }    
}