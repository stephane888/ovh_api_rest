<?php

namespace Drupal\ovh_api_rest\Services;

use Drupal\Core\Controller\ControllerBase;
use Stephane888\Debug\ExceptionExtractMessage;
use Stephane888\Debug\Repositories\ConfigDrupal;
use Ovh\Api;

/**
 *
 * @author stephane
 *        
 */
class ManageBase extends ControllerBase {
  
  /**
   *
   * @var Api
   */
  protected $OVH;
  
  /**
   *
   * @var array
   */
  private $conf = [];
  
  /**
   * Refresh after post or delete.
   */
  protected function refreshDomain() {
    try {
      $endpoind = '/domain/zone/' . $this->entity->getZoneName() . '/refresh';
      $this->initOVh()->post($endpoind);
    }
    catch (\Exception $e) {
      $errors = ExceptionExtractMessage::errorAll($e);
      $this->getLogger('ovh_api_rest')->warning(" impossible de rafraichir le serveur DNS sur OVH, <br> " . implode("<br>", $errors));
    }
  }
  
  /**
   *
   * @return \Ovh\Api
   */
  protected function initOVh() {
    if (!$this->OVH) {
      $conf = $this->defaultConfig();
      $application_key = $conf['api_key'];
      $application_secret = $conf['api_secret'];
      $api_endpoint = 'ovh-eu';
      $consumer_key = $conf['consumer_key'];
      $this->OVH = new Api($application_key, $application_secret, $api_endpoint, $consumer_key);
    }
    return $this->OVH;
  }
  
  protected function defaultConfig() {
    if (!$this->conf) {
      $this->conf = ConfigDrupal::config('ovh_api_rest.settings');
    }
    return $this->conf;
  }
  
}