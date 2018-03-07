<?php

namespace Drupal\invoice_entity;

use Drupal\invoice_entity\Entity\InvoiceEntity;

/**
 * Interface InvoiceServiceInterface.
 */
interface InvoiceServiceInterface {

  /**
   * Check if the key is already used.
   *
   * @param string $key
   *   The key to eval.
   *
   * @return bool
   *   Return true if it found the key.
   */
  public function checkInvoiceKey($key);

  /**
   * Check the current state of the invoice.
   *
   * @param \Drupal\invoice_entity\Entity\InvoiceEntity $entity
   *   Entity to eval.
   *
   * @return array
   *   Return an array with the operation result information.
   */
  public function validateInvoiceEntity(InvoiceEntity $entity);

  /**
   * Generate the invoice key and return it.
   *
   * @param string $type
   *   The type of the invoice.
   *
   * @return string
   *   Return the generated key.
   */
  public function generateInvoiceKey($type);

  /**
   * Generate the invoice consecutive number.
   *
   * @param string $type
   *   Type of document.
   *
   * @return string
   *   The consecutive number.
   */
  public function generateConsecutive($type);

  /**
   * Generate and check if the generated key is already used.
   *
   * @param string $type
   *   The type of the invoice.
   *
   * @return string
   *   Return the new unique key.
   */
  public function getUniqueInvoiceKey($type = 'FE');

  /**
   * Set variable value.
   *
   * @param string $variable_name
   *   Variable machine name.
   * @param string $value
   *   New value for the variable.
   */
  public static function setInvoiceVariable($variable_name, $value);

  /**
   * Gets variables.
   *
   * @return string
   *   Get value of the requested variable.
   */
  public function getInvoiceVariable($variable_name);

  /**
   * Check if all the necessary information have been filled.   *
   *
   * @return bool
   *   Return true if all the information is filled.
   */
  public function checkSettingsData();

}
