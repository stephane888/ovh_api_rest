<?php

namespace Drupal\ovh_api_rest;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Domain buy entities.
 *
 * @ingroup ovh_api_rest
 */
class DomainBuyListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Domain buy ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var \Drupal\ovh_api_rest\Entity\DomainBuy $entity */
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.domain_buy.edit_form',
      ['domain_buy' => $entity->id()]
    );
    return $row + parent::buildRow($entity);
  }

}
