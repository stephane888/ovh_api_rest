<?php

namespace Drupal\ovh_api_rest\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Component\Serialization\Json;
use Symfony\Component\HttpFoundation\JsonResponse;
use Ovh\Api;
use Stephane888\Debug\Utility as UtilityError;

/**
 * Returns responses for Ovh api rest routes.
 */
class OvhApiRestController extends ControllerBase {
  
  /**
   * Builds the response.
   */
  public function CreateDomaine($entity_id) {
    $entity = $this->entityTypeManager()->getStorage("domain_ovh_entity")->load($entity_id);
    if ($entity) {
      //
      $configs = \Drupal::config('ovh_api_rest.settings');
      $application_key = $configs->get('api_key');
      $application_secret = $configs->get('api_secret');
      $api_endpoint = 'ovh-eu';
      $consumer_key = $configs->get('consumer_key');
      $OVH = new Api($application_key, $application_secret, $api_endpoint, $consumer_key);
      $body = [
        'fieldType' => $entity->getFieldType(),
        'subDomain' => $entity->getsubDomain(),
        'target' => $entity->getTarget(),
        'ttl' => $entity->getTtl()
      ];
      //
      $run_ovh = true;
      if ($run_ovh)
        try {
          // Creation du domaine.
          $resp = $OVH->post($entity->getPath(), $body);
          if (!empty($resp['id'])) {
            $entity->set('status', true);
            $entity->set('domaine_id', $resp['id']);
            $entity->save();
          }
        }
        catch (\Exception $e) {
          $run_ovh = false;
          $entity->set('status', false);
          $entity->save();
          //
          return $this->reponse([
            UtilityError::errorAll($e),
            $body
          ], 400, ' impossible de cree le domaine sur OVH :' . $e->getMessage());
        }
      // Connexion du domaine à lespace d'hebergement.
      $sub_domain = $entity->getsubDomain() . '.' . $entity->get('zone_name')->value;
      if ($run_ovh)
        try {
          $body = [
            'cdn' => 'active',
            'domain' => $sub_domain,
            'firewall' => 'active',
            'ownLog' => null,
            'path' => 'www/public/web',
            'runtimeId' => NULL,
            'ssl' => true
          ];
          $OVH->post('/hosting/web/lesroig.cluster023.hosting.ovh.net/attachedDomain', $body);
        }
        catch (\Exception $e) {
          $run_ovh = false;
          return $this->reponse([
            UtilityError::errorAll($e),
            $body
          ], 400, " impossible de cree liée le domaine à l'espace d'hebergement :" . $e->getMessage());
        }
      // Refresh domain
      if ($run_ovh)
        try {
          $OVH->post('/hosting/web/lesroig.cluster023.hosting.ovh.net/attachedDomain/' . $sub_domain . '/purgeCache');
        }
        catch (\Exception $e) {
          $run_ovh = false;
          return $this->reponse([
            UtilityError::errorAll($e),
            $body
          ], 400, ' Echec purgeCache :' . $e->getMessage());
        }
      return $this->reponse($entity->toArray());
    }
    return $this->reponse($entity_id, 400, ' Identifiant non reconnu ');
  }
  
  /**
   *
   * @param array|string $configs
   * @param number $code
   * @param string $message
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  protected function reponse($configs, $code = null, $message = null) {
    if (!is_string($configs))
      $configs = Json::encode($configs);
    $reponse = new JsonResponse();
    if ($code)
      $reponse->setStatusCode($code, $message);
    $reponse->setContent($configs);
    return $reponse;
  }
  
}
