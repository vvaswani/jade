<?php
namespace Application\Factory\View\Strategy;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Application\View\Strategy\CsvStrategy;

class ViewCsvStrategyFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $csvRenderer = $container->get('ViewCsvRenderer');
        return new CsvStrategy($csvRenderer);
    }
}