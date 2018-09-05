<?php

/**
 * @file
 * Contains invoice_received_entity.page.inc.
 *
 * Page callback for Invoice received entity entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Invoice received entity templates.
 *
 * Default template: invoice_received_entity.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_invoice_received_entity(array &$variables) {
  // Fetch InvoiceReceivedEntity Entity Object.
  $invoice_received_entity = $variables['elements']['#invoice_received_entity'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
