<?php

/**
 * @file
 * Contains tax_entity.page.inc.
 *
 * Page callback for Tax entity entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Tax entity templates.
 *
 * Default template: tax_entity.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_tax_entity(array &$variables) {
  // Fetch TaxEntity Entity Object.
  $tax_entity = $variables['elements']['#tax_entity'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
