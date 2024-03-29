<?php

namespace Drupal\ovh_api_rest\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\user\UserInterface;
use Stephane888\Debug\Repositories\ConfigDrupal;

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
   * @var array
   */
  protected static $config = [];
  
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
   * On supprime les données de l'utilisateur (donnee_internet_entity) et
   * l'enregistrement du domain;
   *
   * {@inheritdoc}
   */
  public static function preDelete(EntityStorageInterface $storage, array $entities) {
    parent::preDelete($storage, $entities);
    /**
     *
     * @var \Drupal\ovh_api_rest\Entity\DomainOvhEntity $entity
     */
    $entity = reset($entities);
    if (!empty($entity) && $entity->id()) {
      // Delete donnee_internet_entity
      $query = \Drupal::entityTypeManager()->getStorage('donnee_internet_entity')->getQuery();
      $query->condition('domain_ovh_entity', $entity->id());
      $ids = $query->execute();
      if (!empty($ids)) {
        $id = reset($ids);
        $donnee_internet_entity = \Drupal::entityTypeManager()->getStorage('donnee_internet_entity')->load($id);
        if ($donnee_internet_entity)
          $donnee_internet_entity->delete();
      }
      // Delete domain register.
      $subDomain = $entity->getsubDomain();
      $domain = $entity->getZoneName();
      /**
       *
       * @var \Drupal\generate_domain_vps\Services\GenerateDomainVhost $ManageRegisterDomain
       */
      $ManageRegisterDomain = \Drupal::service('generate_domain_vps.vhosts');
      $ManageRegisterDomain->removeDomainOnVps($domain, $subDomain);
      // Delete domain in OVH if necessairy.
    }
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
    return !empty(self::getConfig()['zone_name']) ? self::getConfig()['zone_name'] : '';
  }
  
  /**
   */
  public function getPath() {
    return !empty(self::getConfig()['path']) ? self::getConfig()['path'] : '/domain/zone/example.com/record';
  }
  
  /**
   * Permet d'associer un domain à l'entité
   */
  public function setDomainIdDrupal($domainId) {
    $this->set('domain_id_drupal', [
      [
        'target_id' => $domainId
      ]
    ]);
  }
  
  /**
   *
   * @return string
   */
  public function getDomainIdDrupal() {
    return $this->get('domain_id_drupal')->target_id;
  }
  
  /**
   * Cest une mauvaise id de sauvagarder les données identiques en BD.
   *
   * @return string
   */
  public function getFieldType() {
    return !empty(self::getConfig()['field_type']) ? self::getConfig()['field_type'] : 'A';
  }
  
  /**
   * La valeur resultante contient les caractaires de a-z0-9
   *
   * @return string
   */
  public function getsubDomain() {
    return $this->get('sub_domain')->value;
  }
  
  /**
   * La valeur resultante contient les caractaires de a-z0-9
   *
   * @param string $sub_domain
   */
  public function setSubDomain($sub_domain) {
    $this->set('sub_domain', preg_replace('/[^a-z0-9\-]/', "", $sub_domain));
  }
  
  /**
   * Cest une mauvaise id de sauvagarder les données identiques en BD.
   *
   * @return number
   */
  public function getTarget() {
    return !empty(self::getConfig()['target']) ? self::getConfig()['target'] : '';
  }
  
  /**
   * Cest une mauvaise id de sauvagarder les données identiques en BD.
   *
   * @return int
   */
  public function getTtl() {
    return !empty(self::getConfig()['ttl']) ? self::getConfig()['ttl'] : 0;
  }
  
  /**
   *
   * @return array
   */
  public function getDomainIdOvh() {
    return $this->get('domaine_id')->value;
  }
  
  /**
   *
   * {@inheritdoc}
   * @see \Drupal\Core\Entity\ContentEntityBase::preSave()
   */
  public function preSave($storage) {
    // On valide le sous domain:
    $this->setSubDomain($this->getsubDomain());
    parent::preSave($storage);
    /**
     * On rend la colonne : domain_id_drupal obligatoire.
     */
    if (empty($this->getDomainIdDrupal())) {
      throw new \Exception(" Le champs DomainIdDrupal est manquant ");
    }
  }
  
  /**
   * Charge la configuration.
   *
   * @return array|number|mixed|\Drupal\Component\Render\MarkupInterface|string
   */
  public static function getConfig() {
    if (!self::$config)
      self::$config = ConfigDrupal::config('ovh_api_rest.settings');
    return self::$config;
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
    
    // $fields['zone_name'] =
    // BaseFieldDefinition::create('string')->setLabel(t('zoneName'))->setSettings([
    // 'max_length' => 50,
    // 'text_processing' => 0
    // ])->setDefaultValue('')->setDisplayOptions('view', [
    // 'label' => 'above',
    // 'type' => 'string',
    // 'weight' => -4
    // ])->setDisplayOptions('form', [
    // 'type' => 'string_textfield',
    // 'weight' => -4
    // ])->setDisplayConfigurable('form', TRUE)->setDisplayConfigurable('view',
    // TRUE)->setRequired(TRUE);
    
    //
    // $fields['field_type'] =
    // BaseFieldDefinition::create('string')->setLabel(t('fieldType'))->setSettings([
    // 'max_length' => 50,
    // 'text_processing' => 0
    // ])->setDefaultValue('')->setDisplayOptions('view', [
    // 'label' => 'above',
    // 'type' => 'string',
    // 'weight' => -4
    // ])->setDisplayOptions('form', [
    // 'type' => 'string_textfield',
    // 'weight' => -4
    // ])->setDisplayConfigurable('form', TRUE)->setDisplayConfigurable('view',
    // TRUE)->setRequired(TRUE);
    
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
    ])->setDisplayConfigurable('form', TRUE)->setDisplayConfigurable('view', TRUE)->setConstraints([
      'UniqueField' => []
    ]);
    
    // $fields['target'] =
    // BaseFieldDefinition::create('string')->setLabel(t('Target (@ip)
    // '))->setSettings([
    // 'max_length' => 50,
    // 'text_processing' => 0
    // ])->setDefaultValue('')->setDisplayOptions('view', [
    // 'label' => 'above',
    // 'type' => 'string',
    // 'weight' => -4
    // ])->setDisplayOptions('form', [
    // 'type' => 'string_textfield',
    // 'weight' => -4
    // ])->setDisplayConfigurable('form', TRUE)->setDisplayConfigurable('view',
    // TRUE)->setRequired(TRUE);
    
    // $fields['path'] = BaseFieldDefinition::create('string')->setLabel(t('
    // Path '))->setSettings([
    // 'max_length' => 50,
    // 'text_processing' => 0
    // ])->setDefaultValue('')->setDisplayOptions('view', [
    // 'label' => 'above',
    // 'type' => 'string',
    // 'weight' => -4
    // ])->setDisplayOptions('form', [
    // 'type' => 'string_textfield',
    // 'weight' => -4
    // ])->setDisplayConfigurable('form', TRUE)->setDisplayConfigurable('view',
    // TRUE)->setRequired(TRUE);
    
    // $fields['ttl'] = BaseFieldDefinition::create('string')->setLabel(t(' Ttl
    // '))->setSettings([
    // 'max_length' => 50,
    // 'text_processing' => 0
    // ])->setDefaultValue('')->setDisplayOptions('view', [
    // 'label' => 'above',
    // 'type' => 'string',
    // 'weight' => -4
    // ])->setDisplayOptions('form', [
    // 'type' => 'string_textfield',
    // 'weight' => -4
    // ])->setDisplayConfigurable('form', TRUE)->setDisplayConfigurable('view',
    // TRUE);
    
    $fields['domaine_id'] = BaseFieldDefinition::create('string')->setLabel(t(' Domaine ID from OVH '))->setSettings([
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
    
    $fields['domain_id_drupal'] = BaseFieldDefinition::create('entity_reference')->setLabel(t(' Domaine ID from drupal '))->setSetting('target_type', 'domain')->setSetting('handler', 'default')->setDisplayOptions('form', [
      'type' => 'entity_reference_autocomplete',
      'weight' => 5
    ])->setDisplayConfigurable('form', TRUE)->setDisplayConfigurable('view', TRUE);
    
    $fields['status']->setLabel(' Domain creer sur OVH ? ')->setDescription(t(' Permet de determiner si le domaine est disponible sur OVH. '))->setDisplayOptions('form', [
      'type' => 'boolean_checkbox',
      'weight' => -3
    ])->setDefaultValue(false)->setReadOnly(true);
    
    $fields['created'] = BaseFieldDefinition::create('created')->setLabel(t('Created'))->setDescription(t('The time that the entity was created.'));
    
    $fields['changed'] = BaseFieldDefinition::create('changed')->setLabel(t('Changed'))->setDescription(t('The time that the entity was last edited.'));
    
    return $fields;
  }
  
}
