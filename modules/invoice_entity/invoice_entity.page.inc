<?php

/**
 * @file
 * Contains invoice_entity.page.inc.
 *
 * Page callback for Invoice entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Invoice templates.
 *
 * Default template: invoice_entity.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_invoice_entity(array &$variables) {
  // Fetch InvoiceEntity Entity Object.
  $invoice_entity = $variables['elements']['#invoice_entity'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
