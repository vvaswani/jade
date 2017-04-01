<?php
namespace Application\View\Helper;

use Zend\View\Exception;
use Zend\View\Helper\AbstractHelper;
use Application\Service\AuthorizationService;

class Authorize extends AbstractHelper
{

    protected $acs;

    public function __construct(AuthorizationService $acs)
    {
        $this->acs = $acs;
    }

    public function __invoke()
    {
        if (!$this->acs instanceof \Application\Service\AuthorizationService) {
            throw new Exception\RuntimeException('No AuthorizationService instance provided');
        }

        return $this->acs;
    }


    public function setAuthorizationService(AuthorizationService $acs)
    {
        $this->acs = $acs;
        return $this;
    }

    public function getAuthorizationService()
    {
        return $this->acs;
    }
}
