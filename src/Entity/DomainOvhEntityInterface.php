<?php

namespace Drupal\ovh_api_rest\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Domain Ovh Endpoint entities.
 *
 * @ingroup ovh_api_rest
 */
interface DomainOvhEntityInterface extends ContentEntityInterface, EntityChangedInterface, EntityPublishedInterface, EntityOwnerInterface {

  /**
   * Add get/set methods for your configuration properties here.
   */

  /**
   * Gets the Domain Ovh Endpoint name.
   *
   * @return string
   *   Name of the Domain Ovh Endpoint.
   */
  public function getName();

  /**
   * Sets the Domain Ovh Endpoint name.
   *
   * @param string $name
   *   The Domain Ovh Endpoint name.
   *
   * @return \Drupal\ovh_api_rest\Entity\DomainOvhEntityInterface
   *   The called Domain Ovh Endpoint entity.
   */
  public function setName($name);

  /**
   * Gets the Domain Ovh Endpoint creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Domain Ovh Endpoint.
   */
  public function getCreatedTime();

  /**
   * Sets the Domain Ovh Endpoint creation timestamp.
   *
   * @param int $timestamp
   *   The Domain Ovh Endpoint creation timestamp.
   *
   * @return \Drupal\ovh_api_rest\Entity\DomainOvhEntityInterface
   *   The called Domain Ovh Endpoint entity.
   */
  public function setCreatedTime($timestamp);

}
