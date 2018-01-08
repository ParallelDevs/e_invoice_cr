<?php

namespace Drupal\e_invoice_cr\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure e_invoice settings for this site.
 */
class invoiceSettingsForm extends ConfigFormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'e_invoice_cr_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'e_invoice_cr.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $base_url = $host = \Drupal::request()->getHost();
    $options_env = ["1" => "Produccion", "2" => "Sandbox"];
    $options_id_type = ["01" => "Cédula Física", "02" => "Cédula Jurídica", "03" => "DIMEX", "04" => "NITE"];
    $settings = \Drupal::config('e_invoice_cr.settings');
    // get default values
    $environment = $settings->get('environment');
    $username = $settings->get('username');
    $password = $settings->get('password');
    $id_type = $settings->get('id_type');
    $id = $settings->get('id');
    $name = $settings->get('name');
    $commercial_name = $settings->get('commercial_name');
    $phone = $settings->get('phone');
    $email = $settings->get('email');
    $postal_code = $settings->get('postal_code');
    $address = $settings->get('address');
    $currency = $settings->get('currency');
    $p12_cert= $settings->get('p12_cert');
    $cert_password = $settings->get('cert_password');

    $form['environment'] = [
      '#type' => 'select',
      '#title' => t('Seleccione el ambiente.'),
      '#default_value' => $environment,
      '#required' => TRUE,
      '#options' => $options_env,
      '#description' => t('Selecione "Producción" para poner el module en modo de producción o "Sandbox" para modo de pruebas.'),
      '#validated' => TRUE,
    ];

    $form['auth_fieldset'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Información de login para el API.'),
      '#description' => t('Este module realiza en login an el API de hacienda por medio de un Auth 2.0 token. Puede ir a la pagina de hacienda para obtener esta información.'),
    ];

    $form['auth_fieldset']['username'] = [
      '#type' => 'textfield',
      '#title' => 'Nombre de usuario:',
      '#default_value' => $username,
      '#required' => TRUE,
    ];

    $form['auth_fieldset']['password'] = [
      '#type' => 'password',
      '#title' => 'Contraseña:',
      '#default_value' => $password,
      '#required' => TRUE,
    ];

    $form['taxpayer_fieldset'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Información de la entidad contribuyente.'),
    ];

    $form['taxpayer_fieldset']['id_type'] = [
      '#type' => 'select',
      '#title' => t('Tipo de identificación.'),
      '#default_value' => $id_type,
      '#required' => TRUE,
      '#options' => $options_id_type,
      '#description' => t('Seleccione el tipo de identificación del contribuyente.'),
      '#validated' => TRUE,
    ];
    $form['taxpayer_fieldset']['id'] = [
      '#type' => 'textfield',
      '#title' => 'Numero identificación:',
      '#default_value' => $id,
      '#description' => t('El numero de identificación del contribuyente debe tener una extensión de 12 caracteres, agregue ceros al principio si es necesario.'),
      '#required' => TRUE,
      '#size' => 12,
    ];
    $form['taxpayer_fieldset']['name'] = [
      '#type' => 'textfield',
      '#title' => 'Nombre del contribuyente:',
      '#default_value' => $name,
      '#required' => TRUE,
    ];
    $form['taxpayer_fieldset']['commercial_name'] = [
      '#type' => 'textfield',
      '#title' => 'Nombre comercial del contribuyente:',
      '#default_value' => $commercial_name,
      '#required' => TRUE,
    ];
    $form['taxpayer_fieldset']['phone'] = [
      '#type' => 'tel',
      '#title' => 'Numero telefónico:',
      '#default_value' => $phone,
      '#required' => TRUE,
    ];
    $form['taxpayer_fieldset']['email'] = [
      '#type' => 'email',
      '#title' => 'Correo electrónico:',
      '#default_value' => $email,
      '#required' => TRUE,
    ];
    $form['taxpayer_fieldset']['address_fieldset'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Ubicación.'),
    ];
    $form['taxpayer_fieldset']['address_fieldset']['postal_code'] = [
      '#type' => 'textfield',
      '#title' => 'Código Postal:',
      '#default_value' => $postal_code,
      '#required' => TRUE,
    ];
    $form['taxpayer_fieldset']['address_fieldset']['address'] = [
      '#type' => 'textfield',
      '#title' => 'Otras Señas:',
      '#default_value' => $address,
      '#required' => FALSE,
    ];
    $form['taxpayer_fieldset']['currency'] = [
      '#type' => 'select',
      '#title' => t('Moneda por defecto.'),
      '#default_value' => $currency,
      '#required' => TRUE,
      '#options' => ['crc' => 'Colones', 'usd' => 'Dólares (Estadounidense)'],
      '#description' => t('Selecione la moneda por defecto para usar en este modulo.'),
      '#validated' => TRUE,
    ];

    $form['cert_fieldset'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Información del certificado.'),
    ];

    $validators = array(
      'file_validate_extensions' => array('p12'),
    );
    $path = \Drupal::moduleHandler()->getModule('e_invoice_cr')->getPath();
    $form['cert_fieldset']['p12_cert'] = array(
      '#type' => 'managed_file',
      '#name' => 'Certificado de autenticidad p12.',
      '#title' => t('Certificado de autenticidad p12.'),
      '#description' => t('Cargue el certificado de autenticidad p12 para la creación de la firma digital, en la pagina de hacienda puede generar tanto el de pruebas como el de producción.'),
      '#default_value' => $p12_cert,
      '#upload_validators' => $validators,
      '#upload_location' => 'public://certs/',
      '#required' => TRUE,
    );

    $form['cert_fieldset']['cert_password'] = [
      '#type' => 'password',
      '#title' => 'Contraseña del certificado:',
      '#default_value' => $cert_password,
      '#required' => TRUE,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Retrieve the configuration
    \Drupal::configFactory()->getEditable('e_invoice_cr.settings')
      // Set the submitted configuration setting
      ->set('environment', $form_state->getValue('environment'))
      ->set('username', $form_state->getValue('username'))
      ->set('password', $form_state->getValue('password'))
      ->set('id_type', $form_state->getValue('id_type'))
      ->set('id', $form_state->getValue('id'))
      ->set('name', $form_state->getValue('name'))
      ->set('commercial_name', $form_state->getValue('commercial_name'))
      ->set('phone', $form_state->getValue('phone'))
      ->set('email', $form_state->getValue('email'))
      ->set('postal_code', $form_state->getValue('postal_code'))
      ->set('address', $form_state->getValue('address'))
      ->set('currency', $form_state->getValue('currency'))
      ->set('p12_cert', $form_state->getValue('p12_cert'))
      ->set('cert_password', $form_state->getValue('cert_password'))
      ->save();
    $fid = $form_state->getValue('p12_cert');
    $file_object = file_load($fid[0], $reset = FALSE);
    // make copies and change the file names
    file_copy($file_object, 'public://certs/cert.pfx', FILE_EXISTS_REPLACE);
    file_copy($file_object, 'public://certs/cert.p12', FILE_EXISTS_REPLACE);
    parent::submitForm($form, $form_state);
  }
}