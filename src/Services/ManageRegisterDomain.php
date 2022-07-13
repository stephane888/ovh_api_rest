<?php

namespace Drupal\ovh_api_rest\Services;

use Stephane888\Debug\Repositories\ConfigDrupal;
use Drupal\generate_domain_vps\Services\GenerateDomainVhost;
use Ovh\Api;
use Drupal\Core\Controller\ControllerBase;

class ManageRegisterDomain extends ControllerBase {
  
  /**
   *
   * @var GenerateDomainVhost
   */
  protected $GenerateDomainVhost;
  
  /**
   *
   * @var \Drupal\ovh_api_rest\Entity\DomainOvhEntity
   */
  protected $entity;
  
  /**
   *
   * @var Api
   */
  protected $OVH;
  
  /**
   *
   * @param string $domainId
   */
  function removeDomain($entity_id) {
    /**
     *
     * @var \Drupal\ovh_api_rest\Entity\DomainOvhEntity $entity
     */
    $this->entity = $this->entityTypeManager()->getStorage("domain_ovh_entity")->load($entity_id);
    if ($this->entity && $this->entity->getDomainIdOvh()) {
      //
      $conf = ConfigDrupal::config('ovh_api_rest.settings');
      $application_key = $conf['api_key'];
      $application_secret = $conf['api_secret'];
      $api_endpoint = 'ovh-eu';
      $consumer_key = $conf['consumer_key'];
      $this->OVH = new Api($application_key, $application_secret, $api_endpoint, $consumer_key);
      
      // On supprime la configuration sur le serveur.
      if ($conf['type_hosting'] == 'vps') {
        $path = '/domain/zone/' . $this->entity->getZoneName() . '/record/' . $this->entity->getDomainIdOvh();
        $this->OVH->delete($path);
        $this->refreshDomain();
      }
    }
  }
  
  /**
   * Refresh after post or delete.
   */
  protected function refreshDomain() {
    $endpoind = '/domain/zone/' . $this->entity->getZoneName() . '/refresh';
    $this->OVH->post($endpoind);
  }
  
}