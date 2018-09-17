<?php

namespace Drupal\invoice_received_entity\Plugin\ValidateInvoiceReceivedQueue;

use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\invoice_received_entity\Entity\InvoiceReceivedEntity;

/**
 * Processes Tasks for Learning.
 *
 * @QueueWorker(
 *   id = "validate_ir_queue",
 *   title = @Translation("Validate Invoice Received Queue"),
 *   cron = {"time" = 60}
 * )
 */
class ValidateInvoiceReceivedQueue extends QueueWorkerBase {

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    /** @var \Drupal\invoice_entity\InvoiceService  $invoice_service */
    $invoice_service = \Drupal::service('invoice_entity.service');

    if (isset($data['number_key']) && !is_null($data['number_key'])) {
      $entity = InvoiceReceivedEntity::load($data['id']);
      $result = $invoice_service->validateInvoiceReceivedEntity($entity);
    }
  }

}
