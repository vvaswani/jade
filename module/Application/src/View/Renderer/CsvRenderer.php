<?php

namespace Application\View\Renderer;

use Traversable;
use Zend\Stdlib\ArrayUtils;
use Zend\View\Exception;
use Zend\View\Model\CsvModel;
use Zend\View\Model\ModelInterface as Model;
use Zend\View\Renderer\RendererInterface;
use Zend\View\Resolver\ResolverInterface;
use Zend\View\Renderer\PhpRenderer;

/**
 * CSV renderer
 */
class CsvRenderer implements RendererInterface
{
	public function getEngine()
    {
        return $this;
    }

    public function setResolver(ResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }

    public function render($model, $values = null)
    {
        if (! $model instanceof CsvModel) {
            return 'aaf';
        }

        $result = 'oo';

        $values = $model->getVariables();

        if ($model->hasChildren()) {
            // recursively render all children
            foreach ($model->getChildren() as $child) {
                $result .= $this->render($child, $values);
            }
        }

        return $result;
    }

}