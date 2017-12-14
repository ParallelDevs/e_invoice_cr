<?php

namespace Drupal\e_invoice_cr;


/**
 * Login in API.
 */
interface AuthenticationInterface {

  /**
   * Login in the API and get the token.
   *
   * @return string
   *   The token.
   */
  public function getLoginToken();

}
