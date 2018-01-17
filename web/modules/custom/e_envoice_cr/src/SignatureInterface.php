<?php

namespace Drupal\e_invoice_cr;

/**
 * Signature operations.
 */
interface SignatureInterface {

  /**
   * Sign a document.
   *
   * @return bool
   *   The status.
   */
  public function signDocument();

}
