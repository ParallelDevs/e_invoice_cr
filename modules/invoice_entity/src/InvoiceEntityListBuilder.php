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
    $header['type_of'] = $this->t('Type');
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
    /** @var \Drupal\Core\Field\BaseFieldDefinition $fd */
    $fd = $entity->getFieldDefinition('type_of');
    $options = $fd->getSetting('allowed_values');
    $row['id'] = $entity->id();
    $row['type_of'] = $options[$entity->getInvoiceType()];
    $row['status'] = $state_label;
    return $row + parent::buildRow($entity);
  }

}
