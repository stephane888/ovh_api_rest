<?php

namespace Drupal\ovh_api_rest;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Domain Ovh Endpoint entity.
 *
 * @see \Drupal\ovh_api_rest\Entity\DomainOvhEntity.
 */
class DomainOvhEntityAccessControlHandler extends EntityAccessControlHandler {
  
  /**
   *
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\ovh_api_rest\Entity\DomainOvhEntityInterface $entity */
    switch ($operation) {
      
      case 'view':
        
        if (!$entity->isPublished()) {
          if ($account->id() == $entity->getOwnerId()) {
            return AccessResult::allowed();
          }
          return AccessResult::allowedIfHasPermission($account, 'view unpublished domain ovh endpoint entities');
        }
        else {
          // if entity is published, show
          return AccessResult::allowed();
        }
        
        return AccessResult::allowedIfHasPermission($account, 'view published domain ovh endpoint entities');
      
      case 'update':
        
        return AccessResult::allowedIfHasPermission($account, 'edit domain ovh endpoint entities');
      
      case 'delete':
        
        return AccessResult::allowedIfHasPermission($account, 'delete domain ovh endpoint entities');
    }
    
    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }
  
  /**
   *
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add domain ovh endpoint entities');
  }
  
}
