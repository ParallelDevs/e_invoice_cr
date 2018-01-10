<?php
namespace Drupal\e_invoice_cr;


/**
 * .
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
      'fecha' => $body['date'],
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
    } else {
      $url = 'https://api.comprobanteselectronicos.go.cr/recepcion-sandbox/v1/recepcion';
    }
    // Do the request.
    $request = $client->request('POST', $url, $options);
    return $request;
  }

  /**
   * {@inheritdoc}
   */
  public function validateDocument() {

  }
}