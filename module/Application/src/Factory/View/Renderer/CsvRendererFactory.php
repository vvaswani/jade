<?php

namespace Application\Factory\View\Renderer;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Application\View\Renderer\CsvRenderer;

class CsvRendererFactory implements FactoryInterface
{

    public function __invoke(ContainerInterface $container, $name, array $options = null)
    {
        return new CsvRenderer();
    }
}
