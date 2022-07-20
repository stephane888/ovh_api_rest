<?php

/**
 * @file
 * Contains domain_buy.page.inc.
 *
 * Page callback for Domain buy entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Domain buy templates.
 *
 * Default template: domain_buy.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_domain_buy(array &$variables) {
  // Fetch DomainBuy Entity Object.
  $domain_buy = $variables['elements']['#domain_buy'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
