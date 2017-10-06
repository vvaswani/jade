<?php

namespace Application\View\Renderer;

use Zend\Stdlib\ArrayUtils;
use Zend\View\Exception;
use Zend\View\Model\ModelInterface;
use Zend\View\Renderer\RendererInterface;
use Zend\View\Resolver\ResolverInterface;
use Application\View\Model\CsvModel;

class CsvRenderer implements RendererInterface
{

    private $__helpers;    
	public function getEngine()
    {
        return $this;
    }

    public function setResolver(ResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }

    public function render($nameOrModel, $values = null)
    {
        if ($nameOrModel instanceof ModelInterface && $nameOrModel instanceof CsvModel) {
            return $nameOrModel->serialize();
        }
 
        throw new Exception\DomainException(sprintf(
            '%s: Do not know how to handle operation when both $nameOrModel and $values are populated',
            __METHOD__
        ));
    }

}