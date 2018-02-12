<?php

namespace Drupal\invoice_entity;

use Drupal\e_invoice_cr\Communication;
use Drupal\invoice_entity\Entity\InvoiceEntityInterface;

/**
 * Class InvoiceService.
 */
class InvoiceService implements InvoiceServiceInterface {

  private $invoiceNumber;
  private $secureCode;

  /**
   * Constructs a new InvoiceService object.
   */
  public function __construct() {
    $this->invoiceNumber = $this->getInvoiceVariable('invoice_number');
    $this->secureCode = $this->getInvoiceVariable('secure_code');

    if (is_null($this->invoiceNumber) || is_null($this->secureCode)) {
      $this->invoiceNumber = '0000000001';
      $this->secureCode = '0000000001';
      $this->updateValues();
    }
  }

  /**
   * Call the validateDocument from Communication and return its result.
   *
   * @param string $key
   *  Key to eval.
   *
   * @return array|null|string
   */
  private function responseForKey($key) {
    $con = new Communication();
    return $con->validateDocument($key);
  }

  public function increaseValues() {
    $this->invoiceNumber = str_pad(intval($this->invoiceNumber) + 1, 10, '0', STR_PAD_LEFT);
    $this->secureCode = str_pad(intval($this->secureCode) + 1, 8,'0', STR_PAD_LEFT);
  }

  public function decreaseValues() {
    $this->invoiceNumber = str_pad(intval($this->invoiceNumber) - 1, 10, '0', STR_PAD_LEFT);
    $this->secureCode = str_pad(intval($this->secureCode) - 1, 8,'0', STR_PAD_LEFT);
  }

  public function updateValues() {
    $this->setInvoiceVariable('invoice_number', $this->invoiceNumber);
    $this->setInvoiceVariable('secure_code', $this->secureCode);
  }

  /**
   * {@inheritdoc}
   */
  public function checkInvoiceKey($key) {
    $result = $this->responseForKey($key);
    if (is_null($result)) {
          return FALSE;
    }
    else {
      $messages = explode("\n-", $result[3]->DetalleMensaje);
      $messages = array_filter($messages, function ($val) {
        return substr($val, 0, 2) == '29';
      });

      return !empty($messages);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function validateInvoiceEntity($entity) {
    $key = $entity->get('field_clave_numerica')->value;
    $result = $this->responseForKey($key);
    $state = NULL;
    if (!is_null($result)) {
      $state = $result[2] === 'rechazado' ? 'rejected' : 'published';
      $entity->set('moderation_state', $state);
      $entity->save();
    }

    return [
      'state' => $state,
      'response' => $result,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function generateInvoiceKey($type) {
    // Get date information.
    $day = date("d");
    $mouth = date("m");
    $year = date("y");
    // The id user.
    $settings = \Drupal::config('e_invoice_cr.settings');
    $id_user = $settings->get('id');
    $id_user = str_pad($id_user, 12, '0', STR_PAD_LEFT);
    if (is_null($id_user)) {
      return NULL;
    }
    else {
      $document_code = isset(InvoiceEntityInterface::DOCUMENTATIONINFO[$type]) ?
        InvoiceEntityInterface::DOCUMENTATIONINFO[$type]['code'] : '01';

      // Join the key.
      $key = '506' . $day . $mouth . $year . $id_user . '00100001' . $document_code . $this->invoiceNumber . '1' . $this->secureCode;
      return $key;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getUniqueInvoiceKey($type = 'FE') {
    $current_key = $this->generateInvoiceKey($type);

    if ($current_key != NULL) {
      // Check if the generated key is already use it.
      if ($this->checkInvoiceKey($current_key)) {
        // If is already in use. Increase values and try again.
        $this->increaseValues();
        return $this->getUniqueInvoiceKey($type);
      }
      else {
        return $current_key;
      }
    }

    return $current_key;
  }

  /**
   * Sets variables.
   */
  function setInvoiceVariable($variable_name, $value) {
    $config = \Drupal::service('config.factory')->getEditable('invoice_entity.settings');
    $config->set($variable_name, $value)->save();
  }

  /**
   * Gets variables.
   */
  function getInvoiceVariable($variable_name) {
    $config = \Drupal::config('invoice_entity.settings');
    $value = $config->get($variable_name);
    return $value;
  }

}
