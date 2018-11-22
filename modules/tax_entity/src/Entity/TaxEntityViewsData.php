<?php

namespace Drupal\tax_entity\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Tax entity entities.
 */
class TaxEntityViewsData extends EntityViewsData {

  /**
   * Returns views data for the customer entity.
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
