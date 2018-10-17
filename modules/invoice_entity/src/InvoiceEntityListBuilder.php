<?php

namespace Drupal\invoice_entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;
use Drupal\customer_entity\Entity\CustomerEntity;

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
    $header['date'] = $this->t('Date');
    $header['client'] = $this->t('Client');
    $header['consecutive'] = $this->t('Consecutive Number');
    $header['type_of'] = $this->t('Type');
    $header['status'] = $this->t('Status');
    $header['credit'] = $this->t('Credit Term');
    $header['total'] = $this->t('Total');
    $header['download'] = $this->t('Download');
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

    $customer_entity = CustomerEntity::load($entity->get('field_client')->getValue()[0]['target_id']);
    $options = $fd->getSetting('allowed_values');
    $row['id'] = $entity->id();
    $date = str_replace('-', '/', $entity->get('field_invoice_date')->getValue()[0]['value']);
    $row['date'] = substr(str_replace('T', ' - ', $date), 0, -3);
    $row['client'] = Link::createFromRoute(
      $customer_entity->get('name')->getValue()[0]['value'],
      'entity.customer_entity.canonical',
      ['customer_entity' => $entity->get('field_client')->getValue()[0]['target_id']]
    );
    $row['consecutive'] = $entity->get('field_consecutive_number')->getValue()[0]['value'];
    $row['type_of'] = $options[$entity->getInvoiceType()];
    $row['status'] = $state_label;
    if (isset($entity->get('field_credit_term')->getValue()[0]['value'])) {
      $row['credit'] = $entity->get('field_credit_term')->getValue()[0]['value'];
    }
    else {
      $row['credit'] = $this->t('None');
    }
    $row['total'] = $entity->get('field_currency')->getValue()[0]['value'] . ' ' . $entity->get('field_total_invoice')->getValue()[0]['value'];
    $row['download'] = Link::createFromRoute(
      'Descargar',
      'invoice_entity.zip',
      ['id' => $entity->id()]
    );

    return $row + parent::buildRow($entity);
  }

}
