<?php
namespace Application\Controller\Plugin; 

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\View\Model\ViewModel;

class AlertPlugin extends AbstractPlugin 
{

    public function alert($alertMessage, $alertMessageEntities, $cancelToRoute)
    {
        $controller = $this->getController();
        if (!$controller || !method_exists($controller, 'plugin')) {
            throw new Exception\DomainException('Alert plugin requires a controller that defines the plugin() method');
        }

        $request = $controller->getRequest();        
        $urlPlugin = $controller->plugin('url');

        $viewModel = new ViewModel(array(
            'alertMessage' => $alertMessage, 
            'alertMessageEntities' => $alertMessageEntities, 
            'cancelTo' => $urlPlugin->fromRoute($cancelToRoute)
        ));
        $viewModel->setTerminal($request->isXmlHttpRequest());
        $viewModel->setTemplate('application/common/alert.phtml');
        return $viewModel;
    }
}
