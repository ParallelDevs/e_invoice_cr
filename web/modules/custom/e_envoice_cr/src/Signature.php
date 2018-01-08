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
      $base_path = DRUPAL_ROOT . '/sites/default/files/';
      // check the directory
      $signed_path = $base_path . 'xml_signed/';
      if (file_prepare_directory($signed_path, FILE_CREATE_DIRECTORY)) {
        // define the paths
        $cert_path = $base_path . "certs/";
        $settings = \Drupal::config('e_invoice_cr.settings');
        $pass = $settings->get('cert_password');
        $doc_path = $base_path . "xml/";
        $signed_path = $base_path . "xml_signed/";
        // build the java command
        $command = 'java -jar java-xades4j-signer.jar ' . $cert_path . ' "'. $pass . '" ' . $doc_path . ' ' . $signed_path . ' 2>&1';
        // execute the command
        exec($command, $response);
        // send the response
        return $response;
      } else {
        $message = 'Error. No se pudo crear el directorio xml_signed.';
        drupal_set_message(t($message), 'error');
        return false;
      }
    } else {
      $message = 'Error. No se puedo ejecutar chdir correctamente.';
      drupal_set_message(t($message), 'error');
      return false;
    }
  }
}