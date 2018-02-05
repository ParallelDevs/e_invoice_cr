<?php

namespace Drupal\e_invoice_cr;

use GuzzleHttp\Exception\ClientException;

/**
 * Provide the API communication functionality.
 */
class Communication implements CommunicationInterface {

  /**
   * {@inheritdoc}
   */
  public function sentDocument($doc = NULL, $body = NULL, $token = NULL) {
    // Get the config info.
    $settings = \Drupal::config('e_invoice_cr.settings');
    $environment = $settings->get('environment');
    // Start the client.
    $client = \Drupal::httpClient();
    // Build the body info.
    $body = [
      'clave' => $body['key'],
      'fecha' => '2018-01-15T10:03:00-0600',
      'emisor' => [
        'tipoIdentificacion' => $body['e_type'],
        'numeroIdentificacion' => $body['e_number'],
      ],
      'receptor' => [
        'tipoIdentificacion' => $body['c_type'],
        'numeroIdentificacion' => $body['c_number'],
      ],
      'comprobanteXml' => base64_encode($doc),
    ];
    // Set the headers and body data.
    $options = [
      'headers' => [
        'Authorization' => 'Bearer ' . $token,
        'Content-type' => 'application/json',
      ],
      "body" => json_encode($body),
    ];
    // Validate environment.
    if ($environment === "1") {
      $url = 'https://api.comprobanteselectronicos.go.cr/recepcion/v1/recepcion';
    }
    else {
      $url = 'https://api.comprobanteselectronicos.go.cr/recepcion-sandbox/v1/recepcion';
    }
    try {
      // Do the request.
      $request = $client->request('POST', $url, $options);
      return $request;
    }
    catch (ClientException $e) {
      drupal_set_message(t('Communication error. @error', ['@error' => $e->getMessage()]), 'error');
      return NULL;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function validateDocument($key = NULL) {
    // Get authentication token for the API.
    $token = \Drupal::service('e_invoice_cr.authentication')->getLoginToken();

    // Get the config info.
    $settings = \Drupal::config('e_invoice_cr.settings');
    $environment = $settings->get('environment');
    // Start the client.
    $client = \Drupal::httpClient();
    // Validate environment.
    if ($environment === "1") {
      $url = 'https://api.comprobanteselectronicos.go.cr/recepcion/v1/recepcion/' . $key;
    }
    else {
      $url = 'https://api.comprobanteselectronicos.go.cr/recepcion-sandbox/v1/recepcion/' . $key;
    }
    // Set the headers data.
    $options = [
      'headers' => [
        'Authorization' => 'Bearer ' . $token,
        'Content-type' => 'application/json',
      ],
    ];
    // Do the request.
    try {
      // Do the request.
      $request = $client->get($url, $options);
      $body_responce = \GuzzleHttp\json_decode($request->getBody()->getContents());
      $result = [];
      foreach ($body_responce as $index => $item) {
        if ($index === "respuesta-xml") {
          $item = simplexml_load_string(base64_decode($item));
        }
        $result[] = $item;
      }
      return $result;
    }
    catch (ClientException $e) {
      drupal_set_message(t('Communication error. @error', ['@error' => $e->getMessage()]), 'error');
      return NULL;
    }

  }

}
