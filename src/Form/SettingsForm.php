<?php

namespace Drupal\ovh_api_rest\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Ovh api rest settings for this site.
 */
class SettingsForm extends ConfigFormBase {
  
  /**
   *
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ovh_api_rest_settings';
  }
  
  /**
   *
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'ovh_api_rest.settings'
    ];
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('ovh_api_rest.settings');
    $form['api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t(" Clé d’application (AK) "),
      '#default_value' => $config->get('api_key'),
      '#required' => true
    ];
    $form['api_secret'] = [
      '#type' => 'textfield',
      '#title' => $this->t(" Clé d’application secrète (AS) "),
      '#default_value' => $config->get('api_secret'),
      '#required' => true
    ];
    $form['consumer_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t(" Consumer key  (CK) "),
      '#default_value' => $config->get('consumer_key'),
      '#required' => true
    ];
    $form['zone_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t(" zone_name "),
      '#default_value' => $config->get('zone_name'),
      "#description" => 'example: lesroisdelareno.fr',
      '#required' => true
    ];
    $form['field_type'] = [
      '#type' => 'textfield',
      '#title' => $this->t(" field_type "),
      '#default_value' => $config->get('field_type'),
      "#description" => 'example: A',
      '#required' => true
    ];
    $form['target'] = [
      '#type' => 'textfield',
      '#title' => $this->t(" target "),
      '#default_value' => $config->get('target'),
      "#description" => 'example: 213.1.3.18',
      '#required' => true
    ];
    $form['path'] = [
      '#type' => 'textfield',
      '#title' => $this->t(" path "),
      '#default_value' => $config->get('path'),
      "#description" => 'example: /domain/zone/{lesroisdelareno.fr}/record',
      '#required' => true
    ];
    $form['path'] = [
      '#type' => 'textfield',
      '#title' => $this->t(" path "),
      '#default_value' => $config->get('path'),
      "#description" => 'example: /domain/zone/{lesroisdelareno.fr}/record',
      '#required' => true
    ];
    $form['type_hosting'] = [
      '#type' => 'select',
      '#title' => $this->t(" type_hosting "),
      '#default_value' => $config->get('type_hosting'),
      "#description" => '',
      '#options' => [
        'multi_hebergement' => 'Hebergement sur un multi',
        'vps' => 'Vps',
        'local' => 'local dev'
      ],
      '#required' => true
    ];
    return parent::buildForm($form, $form_state);
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // if ($form_state->getValue('example') != 'example') {
    // $form_state->setErrorByName('example', $this->t('The value is not
    // correct.'));
    // }
    parent::validateForm($form, $form_state);
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('ovh_api_rest.settings');
    $config->set('api_key', $form_state->getValue('api_key'));
    $config->set('api_secret', $form_state->getValue('api_secret'));
    $config->set('consumer_key', $form_state->getValue('consumer_key'));
    $config->set('zone_name', $form_state->getValue('zone_name'));
    $config->set('field_type', $form_state->getValue('field_type'));
    $config->set('target', $form_state->getValue('target'));
    $config->set('path', $form_state->getValue('path'));
    $config->set('type_hosting', $form_state->getValue('type_hosting'));
    $config->save();
    parent::submitForm($form, $form_state);
  }
  
}
