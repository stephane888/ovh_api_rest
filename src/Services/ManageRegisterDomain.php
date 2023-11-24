<?php

namespace Drupal\ovh_api_rest\Services;

use Drupal\generate_domain_vps\Services\GenerateDomainVhost;
use Stephane888\Debug\ExceptionExtractMessage;

class ManageRegisterDomain extends ManageBase {
  
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
      $conf = $this->defaultConfig();
      // On supprime la configuration sur le serveur.
      if ($conf['type_hosting'] == 'vps') {
        try {
          $path = '/domain/zone/' . $this->entity->getZoneName() . '/record/' . $this->entity->getDomainIdOvh();
          $this->initOVh()->delete($path);
          $this->refreshDomain();
          \Drupal::messenger()->addStatus('domaine suprimer sur ovh ');
        }
        catch (\Exception $e) {
          $errors = ExceptionExtractMessage::errorAllToString($e);
          $this->getLogger('ovh_api_rest')->warning(' Impossible de supprimmer le domaine sur OVH, <br>' . $errors);
        }
      }
    }
    else
      \Drupal::messenger()->addWarning(' entite non trouvé, (domaine pas enregistré chez ovh ?) :  ' . $domainId);
  }
  
}