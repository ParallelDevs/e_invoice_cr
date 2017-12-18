<?php

namespace Drupal\e_invoice_cr\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
* An example controller.
*/
class SignatureController extends ControllerBase {

  /**
  * {@inheritdoc}
  */
  public function content() {
    $build = array(
      '#type' => 'markup',
      '#markup' => t('Hello World!'),
    );
    return $build;
  }

  public function getConnectionToken() {
    return true;
  }

  public function signDocument() {
    chdir("modules/custom/e_invoice_cr/jar");
    $cert_path = "/Users/macairi7/Documents/Projects/own/drupal-8-training/web/sites/default/files/certs/";
    $pass = "2017";
    $command = "java -jar xades4j-1.4.1-SNAPSHOT-jar-with-dependencies.jar " . $cert_path . " ". $pass . " 2>&1";
    exec($command, $response);
    $rsult = $response;
    $build = array(
      '#type' => 'markup',
      '#markup' => t('Hello World!'),
    );
    return $build;

  }

}