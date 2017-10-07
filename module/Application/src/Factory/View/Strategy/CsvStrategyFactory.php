<?php
namespace Application\Factory\View\Strategy;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Application\View\Strategy\CsvStrategy;

class CsvStrategyFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $csvRenderer = $container->get('Application\View\Renderer\CsvRenderer');
        return new CsvStrategy($csvRenderer);
    }
}