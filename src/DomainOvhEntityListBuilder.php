<?php

namespace Drupal\ovh_api_rest;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Domain Ovh Endpoint entities.
 *
 * @ingroup ovh_api_rest
 */
class DomainOvhEntityListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Domain Ovh Endpoint ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var \Drupal\ovh_api_rest\Entity\DomainOvhEntity $entity */
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.domain_ovh_entity.edit_form',
      ['domain_ovh_entity' => $entity->id()]
    );
    return $row + parent::buildRow($entity);
  }

}
