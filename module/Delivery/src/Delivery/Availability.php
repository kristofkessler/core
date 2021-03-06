<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Available
 *
 * @author seng
 */
namespace Delivery;

use VuFindSearch\Query\Query;
use VuFind\Search\Factory\SolrDefaultBackendFactory;

class Availability {

    protected $deliveryConfig;
    protected $solrDriver;

    public function __construct($solrDriver = null, $deliveryConfig = null) 
    {
        if (!empty($solrDriver)) {
            $this->setSolrDriver($solrDriver);
        }
        if (!empty($deliveryConfig)) {
            $this->setDeliveryConfig($deliveryConfig);
        }
    }

    public function setSolrDriver($driver)
    {
        $this->solrDriver = $driver;
    }

    public function setDeliveryConfig($config)
    {
        $this->deliveryConfig = $config->toArray();
    }

    public function getSignature() 
    {
        $deliveryConfig = $this->deliveryConfig;
        $iln = $deliveryConfig['iln'];
        $format = array_shift($this->solrDriver->getFormats());
        $signatureData = $this->solrDriver->getSignatureData($iln);
        $licenceData = $this->solrDriver->getLicenceData($iln);

        $sigel = '';
        $signature = '';
        $available = false;
        $sortedSignatureData = array();

        foreach ($deliveryConfig['sigel_all'] as $sigel) {
            foreach ($signatureData as $index => $signatureDate) {
                if (preg_match('#'.$sigel.'$#', $signatureDate['sigel'])) {
                    $sortedSignatureData[] = $signatureDate;
                    unset($signatureData[$index]);
                    break;
                }
            }
        }

        //$sortedSignatureData = array_merge($sortedSignatureData, $signatureData);
        if (in_array($format, $deliveryConfig['formats'])) {
            if (empty($signatureData) && $this->checkSigel(array(), $format)) {
                return '!'.$sigel.'! '.$signature;
            }
            foreach ($sortedSignatureData as $signatureDate) {
                $sigel = $signatureDate['sigel'];
                $signature = $signatureDate['signature'];
                if ($this->checkSigel($signatureDate, $format)) {
                    if (empty($licenceData)) {
                        return '!'.$sigel.'! '.$signature;
                    } else {
                        foreach ($licenceData as $licenceDate) {
                            if (!$this->checkLicence($licenceDate, $format)) {
                                return '';
                            }
                        }
                        return '!'.$sigel.'! '.$signature;
                    }
                }
            }
        }
        return '';
    }

    public function checkItem()
    {
        $deliveryConfig = $this->deliveryConfig;
        $iln = $deliveryConfig['iln'];
        $formats = $this->solrDriver->getFormats();
        $format = array_shift($formats);
        $signatureData = $this->solrDriver->getSignatureData($iln);
        $licenceData = $this->solrDriver->getLicenceData($iln);

        if (in_array($format, $deliveryConfig['formats'])) {
            if (empty($signatureData) && $this->checkSigel(array(), $format)) {
                return true;
            }
            foreach ($signatureData as $signatureDate) {
                $sigel = $signatureDate['sigel'];
                $signature = $signatureDate['signature'];
                if ($this->checkSigel($signatureDate, $format)) {
                    if (empty($licenceData)) {
                        return true;
                    } else {
                        foreach ($licenceData as $licenceDate) {
                            if (!$this->checkLicence($licenceDate, $format)) {
                                return false;
                            }
                        }
                        return true;
                    }
                }
            }
        }
        return false;
    }

    private function performCheck($item, $data, $format, $extraFullMatch = '') 
    {
        if (empty($this->deliveryConfig[$item.'_'.$format])) {
            $format = 'all';
        }
        if (!empty($this->deliveryConfig[$item.'_'.$format])) {
            foreach ($this->deliveryConfig[$item.'_'.$format] as $regex) {
                $noMatch = false;
                if (strpos($regex, '!') === 0) {
                    if (empty($data)) {
                        return true;
                    }
                    $regex = substr($regex, 1);
                    $noMatch = true;
//echo 'noMatch ';
                }
/*
echo 'item:'.$item.' ';
echo 'format:'.$format.' ';
echo 'regex:'.$regex.' ';
echo 'data:'.$data.' ';
echo '<br>';
*/
                if ($regex == $extraFullMatch || (!$noMatch && preg_match('#'.$regex.'$#', $data)) || ($noMatch && !preg_match('#'.$regex.'$#', $data))) {
//echo 'HIT<br>';
                    return true;
                }
            }
        } else {
            return true;
        }
        return false;
    }

    private function checkSigel($signatureDate, $format, $sigelOnly = false) 
    {
        $format = str_replace(' ', '_', $format);
        $sigelOk = $this->performCheck('sigel', $signatureDate['sigel'], $format, 'ppnlink');
        if ($sigelOk) {
            if ($sigelOnly) {
                return true;
            } else {
                $sigelOk = $this->performCheck('licencenote', $signatureDate['licence_note'], $format);
            }
        }
        if ($sigelOk) {
            $sigelOk = $this->performCheck('footnote', $signatureDate['foot_note'], $format);
        }
        if ($sigelOk) {
            $sigelOk = $this->performCheck('location', $signatureDate['location_note'], $format);
        }
        return $sigelOk;
    }

    private function checkLicence($licenceDate, $format) {
	return $this->performCheck('licence', $licenceDate['licence_type'], $format);
    }

    public function checkPpnLink($serviceLocator, $ppn) {
        return false;
        $deliveryConfig = $this->deliveryConfig;
        $request = 'id:'.$ppn.' AND collection_details:GBV_ILN_'.$deliveryConfig['iln'].' -format:Article';
        $query = new Query();
        $query->setHandler('AllFields');
        $query->setString($request);
        $solr_backend_factory = new SolrDefaultBackendFactory();
        $service = $solr_backend_factory->createService($serviceLocator);
        $result = $service->search($query, 0, 10);
        $resultArray = $result->getResponse();
        $sigelList = $resultArray['docs'][0]['standort_str_mv'];
        $ilnList = $resultArray['docs'][0]['collection_details'];
        $format = $resultArray['docs'][0]['format'][0];

        $ppnValid = false;
        foreach ($ilnList as $iln) {
            if ($iln == 'GBV_ILN_'.$deliveryConfig['iln']) {
                $ppnValid = true;
                break;
            }
        }
        if ($ppnValid) {
            foreach ($sigelList as $sigel) {
                if ($this->checkSigel(array('sigel' => $sigel), $format, true) ) {
                    return true;
                }
            }
        }
        return false;
    }
 
}

?>
