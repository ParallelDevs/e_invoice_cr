<?php

namespace Drupal\invoice_entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;

/**
 * Defines a class to build a listing of Invoice entities.
 *
 * @ingroup invoice_entity
 */
class InvoiceEntityListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Invoice ID');
    $header['status'] = $this->t('Status');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\invoice_entity\Entity\InvoiceEntity */
    $state = $entity->get('moderation_state')->value;
    $state_label = "";
    switch ($state) {
      case "draft":
        $state_label = t("In validation");
        break;

      case "published":
        $state_label = t("Accepted");
        break;

      case "rejected":
        $state_label = t("Rejected");
        break;

    }
    $row['id'] = $entity->id();
    $row['status'] = $state_label;
    return $row + parent::buildRow($entity);
  }

}
