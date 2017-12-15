<?php
namespace Drupal\e_invoice_cr;


/**
 * .
 */
class Signature implements SignatureInterface {

  /**
   * {@inheritdoc}
   */
  public function signDocument() {
    // locate where is the jar
    $res = chdir( "modules/custom/e_envoice_cr/jar" );
    if ($res) {
      // define the paths
      global $base_url;
      $cert_path = "../../../../sites/default/files/certs/";
      $settings = \Drupal::config('e_invoice_cr.settings');
      $pass = $settings->get('cert_password');
      $doc_path = "../../../../sites/default/files/xml/";
      $signed_path = "../../../../sites/default/files/xml_signed/";
      // build the java command
      $command = 'java -jar xades4j-1.4.1-SNAPSHOT-jar-with-dependencies.jar ' . $cert_path . ' "'. $pass . '" ' . $doc_path . ' ' . $signed_path . ' 2>&1';
      // execute the command
      exec($command, $response);
      // send the response
      return $response;
    } else {
      return false;
    }
  }
}