<?php
namespace Application\Controller\Plugin; 

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Form\Annotation\AnnotationBuilder;
use Zend\View\Model\ViewModel;
use Application\Form\ConfirmationForm;

class ConfirmationPlugin extends AbstractPlugin 
{

    public function confirm($confirmationMessage, $confirmationMessageStrings, $confirmationForm, $cancelUrl)
    {
        $controller = $this->getController();
        if (!$controller || !method_exists($controller, 'plugin')) {
            throw new Exception\DomainException('Alert plugin requires a controller that defines the plugin() method');
        }

        $request = $controller->getRequest();        
        $urlPlugin = $controller->plugin('url');

        $confirmationForm->get('cancelUrl')->setValue($cancelUrl);

        $viewModel = new ViewModel(array(
            'form' => $confirmationForm,
            'confirmationMessage' => $confirmationMessage, 
            'confirmationMessageStrings' => $confirmationMessageStrings, 
        ));
        $viewModel->setTerminal($request->isXmlHttpRequest());
        $viewModel->setTemplate('application/common/confirm.phtml');
        return $viewModel;
    }
}
