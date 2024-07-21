<?php

namespace Drupal\ovh_api_rest\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Domain buy entities.
 *
 * @ingroup ovh_api_rest
 */
interface DomainBuyInterface extends ContentEntityInterface, EntityChangedInterface, EntityPublishedInterface, EntityOwnerInterface {

  /**
   * Add get/set methods for your configuration properties here.
   */

  /**
   * Gets the Domain buy name.
   *
   * @return string
   *   Name of the Domain buy.
   */
  public function getName();

  /**
   * Sets the Domain buy name.
   *
   * @param string $name
   *   The Domain buy name.
   *
   * @return \Drupal\ovh_api_rest\Entity\DomainBuyInterface
   *   The called Domain buy entity.
   */
  public function setName($name);

  /**
   * Gets the Domain buy creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Domain buy.
   */
  public function getCreatedTime();

  /**
   * Sets the Domain buy creation timestamp.
   *
   * @param int $timestamp
   *   The Domain buy creation timestamp.
   *
   * @return \Drupal\ovh_api_rest\Entity\DomainBuyInterface
   *   The called Domain buy entity.
   */
  public function setCreatedTime($timestamp);

}
