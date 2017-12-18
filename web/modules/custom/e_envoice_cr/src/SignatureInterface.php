<?php

namespace Drupal\e_invoice_cr;


/**
 * Signature operations.
 */
interface SignatureInterface {

  /**
   * Sign a document.
   *
   * @return boolean
   *   The status.
   */
  public function signDocument();

}