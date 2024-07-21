<?php

namespace Drupal\ovh_api_rest\Services;

use Stephane888\Debug\Repositories\ConfigDrupal;
use Stephane888\Debug\ExceptionExtractMessage;

/**
 *
 * @author stephane
 *        
 */
class ManageDnsZone extends ManageBase {
  
  /**
   *
   * @param string $domain
   * @param string $targetIP
   * @param string $fieldType
   * @param string $subDomain
   * @param string $ttl
   * @return mixed
   */
  public function addRecordIfNotExit($domain, $fieldType = 'A', $subDomain = '', $ttl = '') {
    $record = $this->getRecord($domain, $fieldType, $subDomain);
    if (!$record) {
      $record = $this->addRecord($domain, $fieldType, $subDomain, $ttl);
    }
    return $record;
  }
  
  /**
   * Ajouter un enregistrement DNS.
   *
   * @param string $param
   */
  public function addRecord($domain, $fieldType = 'A', $subDomain = '', $ttl = '') {
    try {
      $targetIP = $this->defaultConfig()['target'];
      $path = "/domain/zone/$domain/record";
      $body = [
        'fieldType' => $fieldType,
        'subDomain' => $subDomain,
        'target' => $targetIP,
        'ttl' => $ttl
      ];
      return $this->initOVh()->post($path, $body);
    }
    catch (\Exception $e) {
      $errors = ExceptionExtractMessage::errorAllToString($e);
      $this->getLogger('ovh_api_rest')->warning(" Impossible d'ajouter l'enregistrement $fieldType dans le domaine '$domain' sur OVH, <br>" . $errors);
      return false;
    }
  }
  
  /**
   *
   * @param string $domain
   * @param string $fieldType
   * @param string $subDomain
   * @return mixed
   */
  public function getRecord($domain, $fieldType = 'A', $subDomain = '') {
    try {
      $path = "/domain/zone/$domain/record";
      $body = [
        'fieldType' => $fieldType,
        'subDomain' => $subDomain
      ];
      return $this->initOVh()->get($path, $body);
    }
    catch (\Exception $e) {
      $errors = ExceptionExtractMessage::errorAllToString($e);
      $this->getLogger('ovh_api_rest')->warning(" Impossible de lire l'enregistrement $fieldType dans le domaine '$domain' sur OVH, <br>" . $errors);
      return false;
    }
  }
  
}