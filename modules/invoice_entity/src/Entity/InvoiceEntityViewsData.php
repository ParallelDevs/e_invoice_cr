<?php

namespace Drupal\invoice_entity\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Invoice entities.
 */
class InvoiceEntityViewsData extends EntityViewsData {

  /**
   * Returns views data for the invoice entity.
   *
   * @return array
   *   Views data in the format of hook_views_data().
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    // Additional information for Views integration, such as table joins, can be
    // put here.
    return $data;
  }

}
