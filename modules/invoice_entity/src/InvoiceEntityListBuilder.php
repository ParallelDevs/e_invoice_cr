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
   * Builds the header row for the invoice entities.
   *
   * @return array
   *   A render array structure of header strings.
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
   * Builds a row for an entity in the invoice entities.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The invoice entity for this row of the list.
   *
   * @return array
   *   A render array structure of header strings.
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

    /* @var $customer_entity \Drupal\customer_entity\Entity\CustomerEntity */
    $customer_entity = CustomerEntity::load($entity->get('field_client')->getValue()[0]['target_id']);

    $date = $entity->get('field_invoice_date')->getValue()[0]['value'];
    $currency = $entity->get('field_currency')->getValue()[0]['value'];
    $total_invoice = $entity->get('field_total_invoice')->getValue()[0]['value'];

    $row['id'] = $entity->id();
    // The date is obtained and formatted.
    $row['date'] = substr(str_replace('T', ' - ', $date), 0, -3);
    // The client's id is obtained and a link is created to redirect it to
    // its data.
    $row['client'] = Link::createFromRoute(
      $customer_entity->get('name')->getValue()[0]['value'],
      'entity.customer_entity.canonical',
      ['customer_entity' => $entity->get('field_client')->getValue()[0]['target_id']]
    );
    $row['consecutive'] = $entity->get('field_consecutive_number')->getValue()[0]['value'];
    $row['type_of'] = $options[$entity->getInvoiceType()];
    $row['status'] = $state_label;

    // Validate if the invoice entity has or not a credit term.
    if (isset($entity->get('field_credit_term')->getValue()[0]['value'])) {
      $row['credit'] = $entity->get('field_credit_term')->getValue()[0]['value'];
    }
    else {
      $row['credit'] = $this->t('None');
    }

    $row['total'] = $currency . ' ' . $total_invoice;

    // Verify if the invoice has already been validated, to enable the option
    // to download the documents that reference it.
    if (strcmp($state, 'published') == 0) {
      $row['download'] = Link::createFromRoute(
        $this->t('Download'),
        'invoice_entity.zip',
        ['id' => $entity->id()]
      );
    }
    else {
      $row['download'] = $this->t('Download');
    }

    return $row + parent::buildRow($entity);
  }

}
