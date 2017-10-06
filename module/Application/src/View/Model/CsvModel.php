<?php

namespace Application\View\Model;

use Zend\Stdlib\ArrayUtils;
use Zend\View\Model\ViewModel;

class CsvModel extends ViewModel
{


    protected $captureTo = null;

    protected $terminate = true;


    public function serialize()
    {
        $vars = $this->getVariables();

        $csv = '';

        if (isset($vars['header'])) {
        	$headerFieldKeys = array_keys($vars['header']);
        	$lastHeaderFieldKey = end($headerFieldKeys);
        	foreach ($vars['header'] as $headerFieldKey => $headerFieldValue) {
        		$csv .= "\"" . $headerFieldValue . "\"";
        		if ($headerFieldKey != $lastHeaderFieldKey) {
        			$csv .= ',';
        		}
        	}
        	$csv .= PHP_EOL;
        }

        if (isset($vars['data'])) {
        	foreach ($vars['data'] as $dataRecord) {
        		$dataFieldKeys = array_keys($dataRecord);
        		$lastDataFieldKey = end($dataFieldKeys);
        		foreach ($dataRecord as $dataFieldKey => $dataFieldValue) {
        			$csv .= "\"$dataFieldValue\"";
        			if ($dataFieldKey != $lastDataFieldKey) {
        				$csv .= ',';
        			}

        		}
        		$csv .= PHP_EOL;
        	}
        }
 
        return $csv;
    }


}
