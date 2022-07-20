<?php

namespace Drupal\ovh_api_rest\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Domain buy entity.
 *
 * @ingroup ovh_api_rest
 *
 * @ContentEntityType(
 *   id = "domain_buy",
 *   label = @Translation("Domain buy"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\ovh_api_rest\DomainBuyListBuilder",
 *     "views_data" = "Drupal\ovh_api_rest\Entity\DomainBuyViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\ovh_api_rest\Form\DomainBuyForm",
 *       "add" = "Drupal\ovh_api_rest\Form\DomainBuyForm",
 *       "edit" = "Drupal\ovh_api_rest\Form\DomainBuyForm",
 *       "delete" = "Drupal\ovh_api_rest\Form\DomainBuyDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\ovh_api_rest\DomainBuyHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\ovh_api_rest\DomainBuyAccessControlHandler",
 *   },
 *   base_table = "domain_buy",
 *   translatable = FALSE,
 *   admin_permission = "administer domain buy entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "published" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/domain_buy/{domain_buy}",
 *     "add-form" = "/admin/structure/domain_buy/add",
 *     "edit-form" = "/admin/structure/domain_buy/{domain_buy}/edit",
 *     "delete-form" = "/admin/structure/domain_buy/{domain_buy}/delete",
 *     "collection" = "/admin/structure/domain_buy",
 *   },
 *   field_ui_base_route = "domain_buy.settings"
 * )
 */
class DomainBuy extends ContentEntityBase implements DomainBuyInterface {
  
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
  
  public function getCartId() {
    return $this->get('cart_id')->value;
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
  
  /**
   *
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);
    
    // Add the published field.
    $fields += static::publishedBaseFieldDefinitions($entity_type);
    
    $fields['cart_id'] = BaseFieldDefinition::create('string')->setLabel(" card_id ")->setDisplayOptions('form', [
      'type' => 'string'
    ])->setDisplayConfigurable('form', TRUE)->setDisplayConfigurable('view', TRUE);
    
    $fields['price'] = BaseFieldDefinition::create('string')->setLabel(" price ")->setDisplayOptions('form', [
      'type' => 'number'
    ])->setDisplayConfigurable('form', TRUE)->setDisplayConfigurable('view', TRUE);
    
    $fields['offer_id'] = BaseFieldDefinition::create('string')->setLabel(" offerId ")->setDisplayOptions('form', [
      'type' => 'string'
    ])->setDisplayConfigurable('form', TRUE)->setDisplayConfigurable('view', TRUE);
    
    $fields['pricing_mode'] = BaseFieldDefinition::create('string')->setLabel(" pricingMode ")->setDisplayOptions('form', [
      'type' => 'string'
    ])->setDisplayConfigurable('form', TRUE)->setDisplayConfigurable('view', TRUE);
    
    $fields['item_id'] = BaseFieldDefinition::create('string')->setLabel(" itemId ")->setDisplayOptions('form', [
      'type' => 'string'
    ])->setDisplayConfigurable('form', TRUE)->setDisplayConfigurable('view', TRUE);
    
    $fields['domaine'] = BaseFieldDefinition::create('string')->setLabel(" domaine ")->setDisplayOptions('form', [
      'type' => 'string'
    ])->setDisplayConfigurable('form', TRUE)->setDisplayConfigurable('view', TRUE);
    
    $fields['periode'] = BaseFieldDefinition::create('string')->setLabel(" Periode ")->setDisplayOptions('form', [
      'type' => 'string'
    ])->setDisplayConfigurable('form', TRUE)->setDisplayConfigurable('view', TRUE);
    
    $fields['type_pack'] = BaseFieldDefinition::create('string')->setLabel(" type_pack ")->setDisplayOptions('form', [
      'type' => 'string'
    ])->setDisplayConfigurable('form', TRUE)->setDisplayConfigurable('view', TRUE);
    
    $fields['domain_id_drupal'] = BaseFieldDefinition::create('entity_reference')->setLabel(t(' Domaine ID from drupal '))->setSetting('target_type', 'domain')->setSetting('handler', 'default')->setDisplayOptions('form', [
      'type' => 'entity_reference_autocomplete',
      'weight' => 5
    ])->setDisplayConfigurable('form', TRUE)->setDisplayConfigurable('view', TRUE);
    
    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')->setLabel(t('Authored by'))->setDescription(t('The user ID of author of the Domain buy entity.'))->setRevisionable(TRUE)->setSetting('target_type', 'user')->setSetting('handler', 'default')->setDisplayOptions('view', [
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
    
    $fields['name'] = BaseFieldDefinition::create('string')->setLabel(t('Name'))->setDescription(t(' The name of the Domain buy entity. '))->setSettings([
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
    
    $fields['status']->setDescription(t('A boolean indicating whether the Domain buy is published.'))->setDisplayOptions('form', [
      'type' => 'boolean_checkbox',
      'weight' => -3
    ]);
    
    $fields['created'] = BaseFieldDefinition::create('created')->setLabel(t('Created'))->setDescription(t(' The time that the entity was created. '));
    
    $fields['changed'] = BaseFieldDefinition::create('changed')->setLabel(t('Changed'))->setDescription(t(' The time that the entity was last edited. '));
    
    return $fields;
  }
  
}
