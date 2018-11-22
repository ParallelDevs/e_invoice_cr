<?php

namespace Drupal\e_invoice_cr;

/**
 * Implements the XML signature process.
 */
class Signature implements SignatureInterface {

  /**
   * Sign a document.
   *
   * @param string $doc_name
   *   The invoice document file name.
   *
   * @return bool
   *   The status.
   */
  public function signDocument($doc_name = "") {
    // Locate where is the jar.
    $res = chdir(drupal_get_path('module', 'e_invoice_cr') . '/jar');
    if ($res) {
      $base_path = DRUPAL_ROOT . '/sites/default/files/';
      // Check the directory.
      $signed_path = $base_path . 'xml_signed/';
      if (file_prepare_directory($signed_path, FILE_CREATE_DIRECTORY)) {
        // Define the paths.
        $cert_path = $base_path . "certs/";
        $settings = \Drupal::config('e_invoice_cr.settings');
        $pass = $settings->get('cert_password');
        $doc_path = $base_path . "xml/";
        $signed_path = $base_path . "xml_signed/";
        // Build the java command.
        $command = 'java -jar java-xades4j-signer.jar ' . $cert_path . ' "' . $pass . '" ' . $doc_path . ' ' . $signed_path . ' ' . $doc_name . ' 2>&1';
        // Execute the command.
        exec($command, $response);
        // Send the response.
        return $response;
      }
      else {
        $message = t('Error. The xml_signed directory could not be created.');
        drupal_set_message($message, 'error');
        return FALSE;
      }
    }
    else {
      $message = t('Error. There were problems running the chdir command.');
      drupal_set_message($message, 'error');
      return FALSE;
    }
  }

}
