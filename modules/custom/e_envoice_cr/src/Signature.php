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
    $jar_path = "modules/custom/e_invoice_cr/jar/";
    // define the paths
    $cert_path = "public://certs/";
    $settings = \Drupal::config('e_invoice_cr.settings');
    $pass = $settings->get('password');
    $doc_path = "public://xml/";
    $signed_path = "public://xml_signed/";
    // build the java command
    $command = "java -jar " . $jar_path . "xades4j-1.4.1-SNAPSHOT-jar-with-dependencies.jar " . $cert_path . " '". $pass . "' " . $doc_path . " " . $signed_path . " 2>&1";
    // execute the command
    exec($command, $response);
    // send the response
    return $response;
  }
}