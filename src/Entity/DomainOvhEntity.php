<?php

namespace Drupal\ovh_api_rest\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;
use Ovh\Api;

/**
 * Defines the Domain Ovh Endpoint entity.
 * permet de generer les domaines sur OVH.
 *
 * @ingroup ovh_api_rest
 *
 * @ContentEntityType(
 *   id = "domain_ovh_entity",
 *   label = @Translation("Domain Ovh Endpoint"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\ovh_api_rest\DomainOvhEntityListBuilder",
 *     "views_data" = "Drupal\ovh_api_rest\Entity\DomainOvhEntityViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\ovh_api_rest\Form\DomainOvhEntityForm",
 *       "add" = "Drupal\ovh_api_rest\Form\DomainOvhEntityForm",
 *       "edit" = "Drupal\ovh_api_rest\Form\DomainOvhEntityForm",
 *       "delete" = "Drupal\ovh_api_rest\Form\DomainOvhEntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\ovh_api_rest\DomainOvhEntityHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\ovh_api_rest\DomainOvhEntityAccessControlHandler",
 *   },
 *   base_table = "domain_ovh_entity",
 *   translatable = FALSE,
 *   admin_permission = "administer domain ovh endpoint entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "published" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/domain_ovh_entity/{domain_ovh_entity}",
 *     "add-form" = "/admin/structure/domain_ovh_entity/add",
 *     "edit-form" = "/admin/structure/domain_ovh_entity/{domain_ovh_entity}/edit",
 *     "delete-form" = "/admin/structure/domain_ovh_entity/{domain_ovh_entity}/delete",
 *     "collection" = "/admin/structure/domain_ovh_entity",
 *   },
 *   field_ui_base_route = "domain_ovh_entity.settings"
 * )
 */
class DomainOvhEntity extends ContentEntityBase implements DomainOvhEntityInterface {
  
  use EntityChangedTrait;
  use EntityPublishedTrait;
  
  /**
   *
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'user_id' => \Drupal::currentUser()->id()
    ];
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }
  
  public function getZoneName() {
    return $this->get('zone_name')->value;
  }
  
  /**
   */
  public function getPath() {
    return $this->get('path')->value;
  }
  
  /**
   */
  public function getFieldType() {
    return $this->get('field_type')->value;
  }
  
  public function getsubDomain() {
    return $this->get('sub_domain')->value;
  }
  
  public function getTarget() {
    return $this->get('target')->value;
  }
  
  public function getTtl() {
    return $this->get('ttl')->value;
  }
  
  public function preSave($storage) {
    // on valide le sous domain:
    $this->set('sub_domain', preg_replace('/[^a-z0-9\-]/', "", $this->getsubDomain()));
    parent::preSave($storage);
  }
  
  /**
   *
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);
    
    // Add the published field.
    $fields += static::publishedBaseFieldDefinitions($entity_type);
    
    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')->setLabel(t('Authored by'))->setDescription(t('The user ID of author of the Domain Ovh Endpoint entity.'))->setRevisionable(TRUE)->setSetting('target_type', 'user')->setSetting('handler', 'default')->setDisplayOptions('view', [
      'label' => 'hidden',
      'type' => 'author',
      'weight' => 0
    ])->setDisplayOptions('form', [
      'type' => 'entity_reference_autocomplete',
      'weight' => 5,
      'settings' => [
        'match_operator' => 'CONTAINS',
        'size' => '60',
        'autocomplete_type' => 'tags',
        'placeholder' => ''
      ]
    ])->setDisplayConfigurable('form', TRUE)->setDisplayConfigurable('view', TRUE);
    
    $fields['name'] = BaseFieldDefinition::create('string')->setLabel(t(" Identification du domaine "))->setSettings([
      'max_length' => 50,
      'text_processing' => 0
    ])->setDefaultValue('')->setDisplayOptions('view', [
      'label' => 'above',
      'type' => 'string',
      'weight' => -4
    ])->setDisplayOptions('form', [
      'type' => 'string_textfield',
      'weight' => -4
    ])->setDisplayConfigurable('form', TRUE)->setDisplayConfigurable('view', TRUE)->setRequired(TRUE);
    
    $fields['zone_name'] = BaseFieldDefinition::create('string')->setLabel(t('zoneName'))->setSettings([
      'max_length' => 50,
      'text_processing' => 0
    ])->setDefaultValue('')->setDisplayOptions('view', [
      'label' => 'above',
      'type' => 'string',
      'weight' => -4
    ])->setDisplayOptions('form', [
      'type' => 'string_textfield',
      'weight' => -4
    ])->setDisplayConfigurable('form', TRUE)->setDisplayConfigurable('view', TRUE)->setRequired(TRUE);
    
    $fields['field_type'] = BaseFieldDefinition::create('string')->setLabel(t('fieldType'))->setSettings([
      'max_length' => 50,
      'text_processing' => 0
    ])->setDefaultValue('')->setDisplayOptions('view', [
      'label' => 'above',
      'type' => 'string',
      'weight' => -4
    ])->setDisplayOptions('form', [
      'type' => 'string_textfield',
      'weight' => -4
    ])->setDisplayConfigurable('form', TRUE)->setDisplayConfigurable('view', TRUE)->setRequired(TRUE);
    
    $fields['sub_domain'] = BaseFieldDefinition::create('string')->setLabel(t('subDomain'))->setSettings([
      'max_length' => 50,
      'text_processing' => 0
    ])->setDefaultValue('')->setDisplayOptions('view', [
      'label' => 'above',
      'type' => 'string',
      'weight' => -4
    ])->setDisplayOptions('form', [
      'type' => 'string_textfield',
      'weight' => -4
    ])->setDisplayConfigurable('form', TRUE)->setDisplayConfigurable('view', TRUE)->setRequired(TRUE)->setConstraints([
      'UniqueField' => []
    ]);
    
    $fields['target'] = BaseFieldDefinition::create('string')->setLabel(t('Target (@ip) '))->setSettings([
      'max_length' => 50,
      'text_processing' => 0
    ])->setDefaultValue('')->setDisplayOptions('view', [
      'label' => 'above',
      'type' => 'string',
      'weight' => -4
    ])->setDisplayOptions('form', [
      'type' => 'string_textfield',
      'weight' => -4
    ])->setDisplayConfigurable('form', TRUE)->setDisplayConfigurable('view', TRUE)->setRequired(TRUE);
    
    $fields['path'] = BaseFieldDefinition::create('string')->setLabel(t(' Path '))->setSettings([
      'max_length' => 50,
      'text_processing' => 0
    ])->setDefaultValue('')->setDisplayOptions('view', [
      'label' => 'above',
      'type' => 'string',
      'weight' => -4
    ])->setDisplayOptions('form', [
      'type' => 'string_textfield',
      'weight' => -4
    ])->setDisplayConfigurable('form', TRUE)->setDisplayConfigurable('view', TRUE)->setRequired(TRUE);
    
    $fields['ttl'] = BaseFieldDefinition::create('string')->setLabel(t(' Ttl '))->setSettings([
      'max_length' => 50,
      'text_processing' => 0
    ])->setDefaultValue('')->setDisplayOptions('view', [
      'label' => 'above',
      'type' => 'string',
      'weight' => -4
    ])->setDisplayOptions('form', [
      'type' => 'string_textfield',
      'weight' => -4
    ])->setDisplayConfigurable('form', TRUE)->setDisplayConfigurable('view', TRUE);
    
    $fields['domaine_id'] = BaseFieldDefinition::create('string')->setLabel(t(' Domaine ID '))->setSettings([
      'max_length' => 50,
      'text_processing' => 0
    ])->setDefaultValue('')->setDisplayOptions('view', [
      'label' => 'above',
      'type' => 'string',
      'weight' => -4
    ])->setDisplayOptions('form', [
      'type' => 'string_textfield',
      'weight' => -4
    ])->setDisplayConfigurable('form', TRUE)->setDisplayConfigurable('view', TRUE)->setReadOnly(true);
    
    $fields['status']->setLabel(' Domain creer sur OVH ? ')->setDescription(t(' Permet de determiner si le domaine est disponible sur OVH. '))->setDisplayOptions('form', [
      'type' => 'boolean_checkbox',
      'weight' => -3
    ])->setDefaultValue(false)->setReadOnly(true);
    
    $fields['created'] = BaseFieldDefinition::create('created')->setLabel(t('Created'))->setDescription(t('The time that the entity was created.'));
    
    $fields['changed'] = BaseFieldDefinition::create('changed')->setLabel(t('Changed'))->setDescription(t('The time that the entity was last edited.'));
    
    return $fields;
  }
  
}
