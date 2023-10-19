<?php

namespace Drupal\ovh_api_rest\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Component\Serialization\Json;
use Symfony\Component\HttpFoundation\JsonResponse;
use Ovh\Api;
use Stephane888\Debug\ExceptionExtractMessage;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\generate_domain_vps\Services\GenerateDomainVhost;
use Stephane888\Debug\Repositories\ConfigDrupal;
use Stephane888\DrupalUtility\HttpResponse;
use Ovh\Exceptions\ApiException;

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
   * Permet de tester la creation de Domaine.
   */
  public function TestCreateDomaine($subdomaine) {
    $conf = ConfigDrupal::config('ovh_api_rest.settings');
    $application_key = $conf['api_key'];
    $application_secret = $conf['api_secret'];
    $api_endpoint = 'ovh-eu';
    $consumer_key = $conf['consumer_key'];
    $field_type = $conf['field_type'];
    $target = $conf['target'];
    $path = $conf['path'];
    $zone_name = $conf['zone_name'];
    
    $ttl = $conf['ttl'];
    $OVH = new Api($application_key, $application_secret, $api_endpoint, $consumer_key);
    /**
     *
     * @var \GuzzleHttp\Client $GuzzleHttp
     */
    $GuzzleHttp = $OVH->getHttpClient();
    $body = [
      'fieldType' => $field_type,
      'subDomain' => $subdomaine,
      'target' => $target
    ];
    try {
      $result = $OVH->post($path, $body);
      /**
       * Le refreash semble pas fonctionner, mais cela est un probleme au niveau
       * du SDK.
       *
       * @var string $endpoind
       */
      $endpoind = '/v1/domain/zone/' . $zone_name . '/refresh';
      $r2 = $OVH->post($endpoind);
      $data = [
        'body' => $body,
        'result' => $result,
        'refresh' => $r2
      ];
      return HttpResponse::response($data);
    }
    catch (ApiException $e) {
      return HttpResponse::response(ExceptionExtractMessage::errorAll($e), 400, $e->getMessage());
    }
    catch (\Exception $e) {
      $db = [
        'body' => $body,
        'exception' => ExceptionExtractMessage::errorAll($e)
      ];
      return HttpResponse::response($db, 400, $e->getMessage());
    }
    
    // dump($conf);
    // $body = [
    // 'fieldType' => $entity->getFieldType(),
    // 'subDomain' => $entity->getsubDomain(),
    // 'target' => $entity->getTarget()
    // // 'ttl' => $entity->getTtl()
    // ];
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
      $conf = ConfigDrupal::config('ovh_api_rest.settings');
      $application_key = $conf['api_key'];
      $application_secret = $conf['api_secret'];
      $api_endpoint = 'ovh-eu';
      $consumer_key = $conf['consumer_key'];
      $target = $conf['target'];
      $field_type = $conf['field_type'];
      $zone_name = $conf['zone_name'];
      $OVH = new Api($application_key, $application_secret, $api_endpoint, $consumer_key);
      $body = [
        'fieldType' => $field_type,
        'subDomain' => $entity->getsubDomain(),
        'target' => $target
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
            $errors = ExceptionExtractMessage::errorAll($e);
            $this->getLogger('ovh_api_rest')->critical($e->getMessage() . '<br>' . ExceptionExtractMessage::errorAllToString($e));
            //
            return $this->reponse([
              $errors,
              $body
            ], 400, ' impossible de cree le domaine sur OVH :' . $e->getMessage());
          }
        // Connexion du domaine à l'espace d'hebergement.
        $sub_domain = $entity->getsubDomain() . '.' . $zone_name;
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
            //
            $errors = ExceptionExtractMessage::errorAll($e);
            $this->getLogger('ovh_api_rest')->critical($e->getMessage() . '<br>' . ExceptionExtractMessage::errorAllToString($e));
            //
            return $this->reponse([
              $errors,
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
            //
            $errors = ExceptionExtractMessage::errorAll($e);
            $this->getLogger('ovh_api_rest')->critical($e->getMessage() . '<br>' . ExceptionExtractMessage::errorAllToString($e));
            //
            return $this->reponse([
              $errors,
              $body
            ], 400, ' Echec purgeCache :' . $e->getMessage());
          }
        return $this->reponse($entity->toArray());
      }
      elseif ($conf['type_hosting'] == 'vps') {
        if ($run_ovh) {
          // Creation du domaine.
          try {
            // Creation du domaine.
            $resp = $OVH->post($entity->getPath(), $body);
            $this->ovhReponse['create-domain'] = $resp;
            if (!empty($resp['id'])) {
              $entity->set('status', true);
              $entity->set('domaine_id', $resp['id']);
              $entity->save();
            }
            else
              $run_ovh = false;
          }
          catch (\Exception $e) {
            $run_ovh = false;
            $entity->set('status', false);
            $entity->save();
            //
            $errors = ExceptionExtractMessage::errorAll($e);
            $this->getLogger('ovh_api_rest')->critical($e->getMessage() . '<br>' . ExceptionExtractMessage::errorAllToString($e));
            //
            return $this->reponse([
              'body' => $body,
              'errors' => $errors
            ], 400, ' impossible de cree le domaine sur OVH :' . $e->getMessage());
          }
          // Refresh du DNS
          try {
            if ($run_ovh) {
              $endpoind = '/domain/zone/' . $zone_name . '/refresh';
              $this->ovhReponse['update-dns'] = $OVH->post($endpoind);
            }
          }
          catch (\Exception $e) {
            // ( si l'erreur se produit ici, on va continuer et
            // notifier l'utilisateur ).
            $this->getLogger('ovh_api_rest')->critical('ERROR lors du refresh du domaine :: ' . $e->getMessage() . '<br>' . ExceptionExtractMessage::errorAllToString($e));
          }
        }
        // on essaie de creer les fichiers pour le vhost.
        if ($run_ovh)
          try {
            $subDomain = $entity->getsubDomain();
            $domain = $zone_name;
            $this->GenerateDomainVhost->createDomainOnVPS($domain, $subDomain);
            return $this->reponse([
              'body' => $body,
              'ovh-api' => $this->ovhReponse,
              'domain_ovh_entity' => $entity->toArray()
            ]);
          }
          catch (\Exception $e) {
            //
            $errors = ExceptionExtractMessage::errorAll($e);
            $this->getLogger('ovh_api_rest')->critical($e->getMessage() . '<br>' . ExceptionExtractMessage::errorAllToString($e));
            //
            return $this->reponse([
              'body' => $body,
              'errors' => $errors
            ], 400, ' impossible de generer le vhost :' . $e->getMessage());
          }
        else {
          return $this->reponse([
            $this->ovhReponse,
            $body
          ], 400, ' Erreur lors de la creation du domaine ');
        }
      }
      elseif ($conf['type_hosting'] == 'local') {
        if ($run_ovh)
          try {
            // On essaie de creer les fichiers pour le vhost.
            $subDomain = $entity->getsubDomain();
            $domain = $zone_name;
            $this->GenerateDomainVhost->createDomainOnVPS($domain, $subDomain);
            return $this->reponse($entity->toArray());
          }
          catch (\Exception $e) {
            //
            $errors = ExceptionExtractMessage::errorAll($e);
            $this->getLogger('ovh_api_rest')->critical($e->getMessage() . '<br>' . ExceptionExtractMessage::errorAllToString($e));
            //
            return $this->reponse([
              $errors,
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
