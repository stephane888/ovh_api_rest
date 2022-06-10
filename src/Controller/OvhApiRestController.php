<?php

namespace Drupal\ovh_api_rest\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Component\Serialization\Json;
use Symfony\Component\HttpFoundation\JsonResponse;
use Ovh\Api;
use Stephane888\Debug\Utility as UtilityError;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\generate_domain_vps\Services\GenerateDomainVhost;

/**
 * Returns responses for Ovh api rest routes.
 */
class OvhApiRestController extends ControllerBase {
  /**
   *
   * @var GenerateDomainVhost
   */
  protected $GenerateDomainVhost;
  
  /**
   *
   * @var array
   */
  protected $ovhReponse;
  
  /**
   *
   * @param GenerateDomainVhost $GenerateDomainVhost
   */
  function __construct(GenerateDomainVhost $GenerateDomainVhost) {
    $this->GenerateDomainVhost = $GenerateDomainVhost;
  }
  
  /**
   *
   * @param ContainerInterface $container
   * @return \Drupal\generate_domain_vps\Controller\GenerateDomainVpsController
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('generate_domain_vps.vhosts'));
  }
  
  /**
   * Builds the response.
   */
  public function CreateDomaine($entity_id) {
    /**
     *
     * @var \Drupal\ovh_api_rest\Entity\DomainOvhEntity $entity
     */
    $entity = $this->entityTypeManager()->getStorage("domain_ovh_entity")->load($entity_id);
    if ($entity) {
      //
      $configs = \Drupal::config('ovh_api_rest.settings');
      $conf = $configs->getRawData();
      $application_key = $conf['api_key'];
      $application_secret = $conf['api_secret'];
      $api_endpoint = 'ovh-eu';
      $consumer_key = $conf['consumer_key'];
      $OVH = new Api($application_key, $application_secret, $api_endpoint, $consumer_key);
      $body = [
        'fieldType' => $entity->getFieldType(),
        'subDomain' => $entity->getsubDomain(),
        'target' => $entity->getTarget(),
        'ttl' => $entity->getTtl()
      ];
      //
      $run_ovh = true;
      // hebergement multi-utilisateur.
      if ($conf['type_hosting'] == 'multi_hebergement') {
        if ($run_ovh)
          try {
            // Creation du domaine.
            $resp = $OVH->post($entity->getPath(), $body);
            $this->ovhReponse['create-domain'] = $resp;
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
            $this->ovhReponse['link-to-space'] = $OVH->post('/hosting/web/lesroig.cluster023.hosting.ovh.net/attachedDomain', $body);
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
            $this->ovhReponse['refresh'] = $OVH->post('/hosting/web/lesroig.cluster023.hosting.ovh.net/attachedDomain/' . $sub_domain . '/purgeCache');
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
      elseif ($conf['type_hosting'] == 'vps') {
        if ($run_ovh)
          try {
            // Creation du domaine.
            $resp = $OVH->post($entity->getPath(), $body);
            $this->ovhReponse['create-domain'] = $resp;
            if (!empty($resp['id'])) {
              $entity->set('status', true);
              $entity->set('domaine_id', $resp['id']);
              $entity->save();
              // on applique les modifications du DNS;
              $endpoind = '/domain/zone/' . $entity->get('zone_name')->value . '/refresh';
              $this->ovhReponse['update-dns'] = $OVH->post($endpoind);
            }
            else
              $run_ovh = false;
          }
          catch (\Exception $e) {
            $run_ovh = false;
            $entity->set('status', false);
            $entity->save();
            $this->getLogger("ovh_api_rest")->warning("Impossible de crrer le domaine sur OVH : <br>" . $e->getMessage());
            //
            return $this->reponse([
              UtilityError::errorAll($e),
              $body
            ], 400, ' impossible de cree le domaine sur OVH :' . $e->getMessage());
          }
        // on essaie de creer les fichiers pour le vhost.
        if ($run_ovh)
          try {
            $subDomain = $entity->getsubDomain();
            $domain = $entity->get('zone_name')->value;
            $this->GenerateDomainVhost->createDomainOnVPS($domain, $subDomain);
            return $this->reponse([
              'body' => $body,
              'ovh-api' => $this->ovhReponse,
              'domain_ovh_entity' => $entity->toArray()
            ]);
          }
          catch (\Exception $e) {
            return $this->reponse([
              UtilityError::errorAll($e),
              $body
            ], 400, ' impossible de generer le vhost :' . $e->getMessage());
          }
      }
      elseif ($conf['type_hosting'] == 'local') {
        if ($run_ovh)
          try {
            // on essaie de creer les fichiers pour le vhost.
            $subDomain = $entity->getsubDomain();
            $domain = $entity->get('zone_name')->value;
            $this->GenerateDomainVhost->createDomainOnVPS($domain, $subDomain);
            return $this->reponse($entity->toArray());
          }
          catch (\Exception $e) {
            return $this->reponse([
              UtilityError::errorAll($e),
              $body
            ], 400, ' impossible de generer le vhost :' . $e->getMessage());
          }
      }
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
