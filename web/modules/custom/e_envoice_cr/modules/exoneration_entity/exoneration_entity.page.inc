<?php

/**
 * @file
 * Contains exoneration_entity.page.inc.
 *
 * Page callback for Exoneration entity entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Exoneration entity templates.
 *
 * Default template: exoneration_entity.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_exoneration_entity(array &$variables) {
  // Fetch ExonerationEntity Entity Object.
  $exoneration_entity = $variables['elements']['#exoneration_entity'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
