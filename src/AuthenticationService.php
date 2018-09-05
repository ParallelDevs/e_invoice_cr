<?php

namespace Drupal\e_invoice_cr;

/**
 * Implements the authentication functionality.
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
    $client_id = "";
    if ($environment === "1") {
      $url = 'https://idp.comprobanteselectronicos.go.cr/auth/realms/rut/protocol/openid-connect/token';
      $client_id = "api-prod";
    }
    else {
      $url = 'https://idp.comprobanteselectronicos.go.cr/auth/realms/rut-stag/protocol/openid-connect/token';
      $client_id = "api-stag";
    }
    if ($username !== "" && $password !== "") {
      $data = [
        'client_id' => $client_id,
        'client_secret' => '',
        'grant_type' => 'password',
        'username' => $username,
        'password' => $password,
        'scope' => '',
      ];
      // Use key 'http' even if you send the request to https://.
      $options = [
        'http' => [
          'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
          'method'  => 'POST',
          'content' => http_build_query($data),
        ],
      ];
      $context = stream_context_create($options);
      $result = file_get_contents($url, FALSE, $context);
      if ($result === FALSE) {
        echo $result;
      }

      // Get a token object.
      $token = json_decode($result);
      // Return a json object whith token and refresh token.
      return $token->access_token;
    }
    else {
      return "";
    }

  }

}
