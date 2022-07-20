<?php

namespace Drupal\ovh_api_rest\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Domain buy entities.
 */
class DomainBuyViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    // Additional information for Views integration, such as table joins, can be
    // put here.
    return $data;
  }

}
