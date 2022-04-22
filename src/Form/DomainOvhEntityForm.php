<?php

namespace Drupal\ovh_api_rest\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Ovh\Api;
use Stephane888\Debug\debugLog;

/**
 * Form controller for Domain Ovh Endpoint edit forms.
 *
 * @ingroup ovh_api_rest
 */
class DomainOvhEntityForm extends ContentEntityForm {
  
  /**
   * The current user account.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $account;
  
  /**
   *
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    $instance = parent::create($container);
    $instance->account = $container->get('current_user');
    return $instance;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var \Drupal\ovh_api_rest\Entity\DomainOvhEntity $entity */
    $form = parent::buildForm($form, $form_state);
    // $configs = \Drupal::config('ovh_api_rest.settings');
    // $application_key = $configs->get('api_key');
    // $application_secret = $configs->get('api_secret');
    // $api_endpoint = 'ovh-eu';
    // $consumer_key = $configs->get('consumer_key');
    // $OVH = new Api($application_key, $application_secret, $api_endpoint, $consumer_key);
    // // $data = $OVH->get('/hosting/web/lesroig.cluster023.hosting.ovh.net/attachedDomain/test862.lesroisdelareno.fr');
    // debugLog::kintDebugDrupal($data, 'list-attachedDomain--test862.lesroisdelareno.fr', true);
    
    //
    return $form;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;
    
    $status = parent::save($form, $form_state);
    
    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label Domain Ovh Endpoint.', [
          '%label' => $entity->label()
        ]));
        break;
      
      default:
        $this->messenger()->addMessage($this->t('Saved the %label Domain Ovh Endpoint.', [
          '%label' => $entity->label()
        ]));
    }
    $form_state->setRedirect('entity.domain_ovh_entity.canonical', [
      'domain_ovh_entity' => $entity->id()
    ]);
  }
  
}
