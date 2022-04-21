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
    $form['api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t(" Clé d’application (AK) "),
      '#default_value' => $this->config('ovh_api_rest.settings')->get('api_key'),
      '#required' => true
    ];
    $form['api_secret'] = [
      '#type' => 'textfield',
      '#title' => $this->t(" Clé d’application secrète (AS) "),
      '#default_value' => $this->config('ovh_api_rest.settings')->get('api_secret'),
      '#required' => true
    ];
    $form['consumer_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t(" Consumer key  (CK) "),
      '#default_value' => $this->config('ovh_api_rest.settings')->get('consumer_key'),
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
    // $form_state->setErrorByName('example', $this->t('The value is not correct.'));
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
    $config->save();
    parent::submitForm($form, $form_state);
  }
  
}
