<?php

namespace Application\Factory\View\Renderer;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Application\View\Renderer\CsvRenderer;

class CsvRendererFactory implements FactoryInterface
{

    public function __invoke(ContainerInterface $container, $name, array $options = null)
    {
        $renderer = new CsvRenderer();
        $renderer->setResolver($container->get('ViewResolver'));
        return $renderer;
    }
}
