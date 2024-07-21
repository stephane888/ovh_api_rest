<?php

/**
 * @file
 * Contains domain_ovh_entity.page.inc.
 *
 * Page callback for Domain Ovh Endpoint entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Domain Ovh Endpoint templates.
 *
 * Default template: domain_ovh_entity.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_domain_ovh_entity(array &$variables) {
  // Fetch DomainOvhEntity Entity Object.
  $domain_ovh_entity = $variables['elements']['#domain_ovh_entity'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
