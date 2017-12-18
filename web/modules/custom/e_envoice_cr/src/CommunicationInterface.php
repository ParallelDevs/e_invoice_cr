<?php

namespace Drupal\e_invoice_cr;


/**
 * Communication with the API.
 */
interface CommunicationInterface {

  /**
   * Send documents.
   *
   * @return boolean
   *   The token.
   */
  public function sentDocument();

}