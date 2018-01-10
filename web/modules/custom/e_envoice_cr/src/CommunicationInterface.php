<?php

namespace Drupal\e_invoice_cr;


/**
 * Communication with the API.
 */
interface CommunicationInterface {

  /**
   * Send documents.
   *
   * @return object
   *   The response object.
   */
  public function sentDocument($doc = NULL, $type = NULL, $token = NULL);

  /**
   * Consult a document status.
   *
   * @return string
   *   The status.
   */
  public function validateDocument($key = NULL, $token = NULL);

}