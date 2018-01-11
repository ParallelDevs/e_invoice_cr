<?php
namespace Drupal\e_invoice_cr;


/**
 * .
 */
class AuthenticationService {

  /**
   * It gets the connection token.
   */
  public function getLoginToken() {
    $settings = \Drupal::config('e_invoice_cr.settings');
    $username = $settings->get('username');
    $password = $settings->get('password');
    $environment = $settings->get('environment');
    // Access token url.
    $url = "";
    if ($environment === "1") {
      $url = 'https://idp.comprobanteselectronicos.go.cr/auth/realms/rut/protocol/openid-connect/token';
    } else {
      $url = 'https://idp.comprobanteselectronicos.go.cr/auth/realms/rut-stag/protocol/openid-connect/token';
    }
    if ($username !== "" && $password !== "") {
      $data = array('client_id' => 'api-stag', // Test: 'api-stag' Production: 'api-prod'.
        'client_secret' => '', // Always empty.
        'grant_type' => 'password', // Always 'password'.
        // Go to https://www.hacienda.go.cr/ATV/login.aspx to generate a username and password credentials.
        'username' => $username,
        'password' => $password,
        'scope' =>''); // Always empty.
      // Use key 'http' even if you send the request to https://.
      $options = array(
        'http' => array(
          'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
          'method'  => 'POST',
          'content' => http_build_query($data)
        )
      );
      $context  = stream_context_create($options);
      $result = file_get_contents($url, false, $context);
      if ($result === FALSE) { echo $result; }
      $token = json_decode($result); // Get a token object.

      return $token->access_token; // Return a json object whith token and refresh token.
    } else {
      return "";
    }
  }
}