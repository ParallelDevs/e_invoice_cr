<?php

namespace Drupal\invoice_received_entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\invoice_received_entity\Entity\InvoiceReceivedEntityInterface;

/**
 * Defines a class to build a listing of Invoice received entity entities.
 *
 * @ingroup invoice_received_entity
 */
class InvoiceReceivedEntityListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Invoice received entity ID');
    $header['sender_name'] = $this->t('Sender');
    $header['date'] = $this->t('Date');
    $header['message'] = $this->t('State');
    /* $header['code'] = $this->t('Numeric Key'); */
    $header['status'] = $this->t('Hacienda response');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\invoice_received_entity\Entity\InvoiceReceivedEntity */
    $row['id'] = $entity->id();
    /* $row['code'] = Link::createFromRoute(
      $entity->get('field_ir_numeric_key')->value,
      'entity.invoice_received_entity.edit_form',
      ['invoice_received_entity' => $entity->id()]
    ); */
    $row['sender_name'] = $entity->get('field_ir_senders_name')->value;
    $row['date'] = $entity->get('field_ir_invoice_date')->value;
    $options = $entity->getFieldDefinition('field_ir_message')
      ->getFieldStorageDefinition()->getSetting('allowed_values');
    $row['message'] = is_null($entity->get('field_ir_message')->value) ?
      'None' : $options[$entity->get('field_ir_message')->value];

    switch ($entity->get('field_ir_status')->value) {
      case InvoiceReceivedEntityInterface::IR_WAITING_STATUS:
        $row['status'] = $this->t('Waiting for user response');
        break;

      case InvoiceReceivedEntityInterface::IR_SENT_HACIENDA:
        $row['status'] = $this->t('Sent it');
        break;

      case InvoiceReceivedEntityInterface::IR_ACCEPTED_STATUS:
        $row['status'] = $this->t('Accepted');
        break;

      case InvoiceReceivedEntityInterface::IR_REJECTED_STATUS:
        $row['status'] = $this->t('Rejected');
        break;

      default:
        $row['status'] = $this->t('.');
    }
    return $row + parent::buildRow($entity);
  }

}
