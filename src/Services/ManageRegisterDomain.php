<?php

namespace Drupal\ovh_api_rest\Services;

use Stephane888\Debug\Repositories\ConfigDrupal;
use Drupal\generate_domain_vps\Services\GenerateDomainVhost;
use Stephane888\Debug\Utility as UtilityError;
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
  protected $entity = null;
  
  /**
   *
   * @var Api
   */
  protected $OVH;
  
  /**
   *
   * @param string $domainId
   *        id du domain.
   */
  function removeDomain($domainId) {
    $query = $this->entityTypeManager()->getStorage("domain_ovh_entity")->getQuery();
    $query->condition('domain_id_drupal', $domainId);
    $ids = $query->execute();
    if (!empty($ids)) {
      $id = reset($ids);
      /**
       *
       * @var \Drupal\ovh_api_rest\Entity\DomainOvhEntity $entity
       */
      $this->entity = $this->entityTypeManager()->getStorage("domain_ovh_entity")->load($id);
    }
    
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
        try {
          $path = '/domain/zone/' . $this->entity->getZoneName() . '/record/' . $this->entity->getDomainIdOvh();
          $this->OVH->delete($path);
          $this->refreshDomain();
          \Drupal::messenger()->addStatus('domaine suprimer sur ovh ');
        }
        catch (\Exception $e) {
          $errors = UtilityError::errorAll($e);
          $this->getLogger('ovh_api_rest')->warning('impossible le domaine sur OVH, <br>' . implode("<br>", $errors));
        }
      }
    }
    else
      \Drupal::messenger()->addWarning(' entite non trouvé, (domaine pas enregistré chez ovh ?) :  ' . $domainId);
  }
  
  /**
   * Refresh after post or delete.
   */
  protected function refreshDomain() {
    try {
      $endpoind = '/domain/zone/' . $this->entity->getZoneName() . '/refresh';
      $this->OVH->post($endpoind);
    }
    catch (\Exception $e) {
      $errors = UtilityError::errorAll($e);
      $this->getLogger('ovh_api_rest')->warning(' impossible de rafraichir le serveur DNS sur OVH, <br>' . implode("<br>", $errors));
    }
  }
  
}