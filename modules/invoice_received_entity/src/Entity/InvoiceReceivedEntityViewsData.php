<?php

namespace Drupal\invoice_received_entity\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Invoice received entity entities.
 */
class InvoiceReceivedEntityViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    // Additional information for Views integration, such as table joins, can
    // be put here.
    return $data;
  }

}
