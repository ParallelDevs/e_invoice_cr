<?php

/**
 * @file
 * Contains customer_entity.page.inc.
 *
 * Page callback for Customer entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Customer templates.
 *
 * Default template: customer_entity.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_customer_entity(array &$variables) {
  // Fetch CustomerEntity Entity Object.
  $customer_entity = $variables['elements']['#customer_entity'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
