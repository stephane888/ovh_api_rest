<?php

namespace Drupal\ovh_api_rest;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Domain buy entity.
 *
 * @see \Drupal\ovh_api_rest\Entity\DomainBuy.
 */
class DomainBuyAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\ovh_api_rest\Entity\DomainBuyInterface $entity */

    switch ($operation) {

      case 'view':

        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished domain buy entities');
        }


        return AccessResult::allowedIfHasPermission($account, 'view published domain buy entities');

      case 'update':

        return AccessResult::allowedIfHasPermission($account, 'edit domain buy entities');

      case 'delete':

        return AccessResult::allowedIfHasPermission($account, 'delete domain buy entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add domain buy entities');
  }


}
