<?php

namespace Drupal\ovh_api_rest\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Returns responses for Ovh api rest routes.
 */
class OvhApiRestController extends ControllerBase {

  /**
   * Builds the response.
   */
  public function build() {

    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('It works!'),
    ];

    return $build;
  }

}
