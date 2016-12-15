<?php
namespace Application\Service;

use Zend\Authentication\AuthenticationService;

class AccessControlService
{
    private $as;

    public function __construct(AuthenticationService $as)
    {
        $this->as = $as;
    } 

    public function filterAccess()
    {
        //$this->redirect()->toRoute('login');
        //print_r($this->as->hasIdentity()); die;
        if (!$this->as->getIdentity()) {
            return false;
        }
        return true;
    }
}