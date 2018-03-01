<?php

namespace Drupal\e_invoice_cr\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure e_invoice settings for this site.
 */
class InvoiceSettingsForm extends ConfigFormBase {

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
    $options_env = ["1" => "Production", "2" => "Sandbox"];
    $options_id_type = [
      "01" => "Physical person id",
      "02" => "Company id",
      "03" => "DIMEX",
      "04" => "NITE",
    ];
    $settings = \Drupal::config('e_invoice_cr.settings');
    // Get default values.
    $environment = $settings->get('environment');
    $username = $settings->get('username');
    $password = $settings->get('password');
    $id_type = $settings->get('id_type');
    $id = $settings->get('id');
    $name = $settings->get('name');
    $commercial_name = $settings->get('commercial_name');
    $phone = $settings->get('phone');
    $fax = $settings->get('fax');
    $email = $settings->get('email');
    $postal_code = $settings->get('postal_code');
    $address = $settings->get('address');
    $currency = $settings->get('currency');
    $logo_file = $settings->get('invoice_logo_file');
    $p12_cert = $settings->get('p12_cert');
    $cert_password = $settings->get('cert_password');

    $form['environment'] = [
      '#type' => 'select',
      '#title' => $this->t('Select the environment.'),
      '#default_value' => $environment,
      '#required' => TRUE,
      '#options' => $options_env,
      '#description' => $this->t('Select "Production" to set the production mode or "Sandbox" to set the tests mode.'),
      '#validated' => TRUE,
    ];

    $form['auth_fieldset'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('API login information.'),
      '#description' => t('This module does the API login through the Oauth 2.0 token.'),
    ];

    $form['auth_fieldset']['username'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Username:'),
      '#default_value' => $username,
      '#required' => TRUE,
    ];

    $form['auth_fieldset']['password'] = [
      '#type' => 'password',
      '#title' => $this->t('Password:'),
      '#default_value' => $password,
      '#required' => TRUE,
    ];

    $form['taxpayer_fieldset'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Taxpayer information.'),
    ];

    $form['taxpayer_fieldset']['id_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Id type.'),
      '#default_value' => $id_type,
      '#required' => TRUE,
      '#options' => $options_id_type,
      '#description' => $this->t("Select the taxpayer's id type."),
      '#validated' => TRUE,
    ];
    $form['taxpayer_fieldset']['id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Id number:'),
      '#default_value' => $id,
      '#description' => $this->t("The id number must have 12 characters, add zeros at the start if it's necessary."),
      '#required' => TRUE,
      '#size' => 12,
      '#maxlength' => 12,
    ];
    $form['taxpayer_fieldset']['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name:'),
      '#default_value' => $name,
      '#required' => TRUE,
    ];
    $form['taxpayer_fieldset']['commercial_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Tradename:'),
      '#default_value' => $commercial_name,
      '#required' => TRUE,
    ];
    $form['taxpayer_fieldset']['phone'] = [
      '#type' => 'tel',
      '#title' => $this->t('Phone number:'),
      '#default_value' => $phone,
      '#description' => $this->t('Please add the country code to the beginning. This field should only have numbers. No spaces or special characters.'),
      '#required' => TRUE,
    ];
    $form['taxpayer_fieldset']['fax'] = [
      '#type' => 'tel',
      '#title' => $this->t('Fax number:'),
      '#default_value' => $fax,
      '#description' => $this->t('Please add the country code to the beginning. This field should only have numbers. No spaces or special characters.'),
      '#required' => FALSE,
    ];
    $form['taxpayer_fieldset']['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email:'),
      '#default_value' => $email,
      '#required' => TRUE,
    ];
    $form['taxpayer_fieldset']['address_fieldset'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Location.'),
    ];
    $form['taxpayer_fieldset']['address_fieldset']['postal_code'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Zip code:'),
      '#default_value' => $postal_code,
      '#required' => TRUE,
    ];
    $form['taxpayer_fieldset']['address_fieldset']['address'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Others:'),
      '#default_value' => $address,
      '#required' => TRUE,
    ];
    $form['taxpayer_fieldset']['currency'] = [
      '#type' => 'select',
      '#title' => $this->t('Default currency.'),
      '#default_value' => $currency,
      '#required' => TRUE,
      '#options' => ['crc' => 'Colones', 'usd' => 'Dolar (USA)'],
      '#description' => $this->t('Select the default currency to use on this module.'),
      '#validated' => TRUE,
    ];

    $form['company_logo_fieldset'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Logo.'),
    ];

    $form['company_logo_fieldset']['invoice_logo_file'] = [
      '#title' => $this->t('Company Logo'),
      '#type' => 'managed_file',
      '#upload_validators' => [
        'file_validate_extensions' => ['png jpg jpeg'],
        'file_validate_image_resolution' => ["", "300x300"],
      ],
      '#theme' => 'image_widget',
      '#preview_image_style' => 'medium',
      '#upload_location' => 'public://',
      '#required' => FALSE,
    ];

    $form['cert_fieldset'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Certificate information.'),
    ];

    $validators = ['file_validate_extensions' => ['p12']];
    $path = \Drupal::moduleHandler()->getModule('e_invoice_cr')->getPath();
    $form['cert_fieldset']['p12_cert'] = [
      '#type' => 'managed_file',
      '#name' => 'Certificate p12.',
      '#title' => $this->t('Certificate p12.'),
      '#description' => $this->t('Load the authentication certificate p12 to create the signature.'),
      '#default_value' => $p12_cert,
      '#upload_validators' => $validators,
      '#upload_location' => 'public://certs/',
      '#required' => TRUE,
    ];

    $form['cert_fieldset']['cert_password'] = [
      '#type' => 'password',
      '#title' => $this->t('Password:'),
      '#default_value' => $cert_password,
      '#required' => TRUE,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $id_type = $form_state->getValue('id_type');
    switch ($id_type) {
      case "01":
        if (strlen($form_state->getValue('id')) !== 9) {
          $form_state->setErrorByName('id', $this->t("The id should have 9 characters, add zeros at the start if it's necessary."));
        }
        break;

      case "02":
        if (strlen($form_state->getValue('id')) !== 10) {
          $form_state->setErrorByName('id', $this->t("The id should have 10 characters, add zeros at the start if it's necessary."));
        }
        break;

      case "03":
        if (strlen($form_state->getValue('id')) < 11 || strlen($form_state->getValue('id')) > 12) {
          $form_state->setErrorByName('id', $this->t("The id should have 11 or 12 characters, add zeros at the start if it's necessary."));
        }
        break;

      case "04":
        if (strlen($form_state->getValue('id')) !== 10) {
          $form_state->setErrorByName('id', $this->t("The id should have 10 characters, add zeros at the start if it's necessary."));
        }
        break;

    }

    if (!is_numeric($form_state->getValue('id'))) {
      $form_state->setErrorByName('id', $this->t('This field should only have numbers. No spaces or special characters.'));
    }

    if (!is_numeric($form_state->getValue('phone'))) {
      $form_state->setErrorByName('phone', $this->t('This field should only have numbers. No spaces or special characters.'));
    }

    if (strlen($form_state->getValue('fax')) > 0) {
      if (!is_numeric($form_state->getValue('fax'))) {
        $form_state->setErrorByName('fax', $this->t('This field should only have numbers. No spaces or special characters.'));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Retrieve the configuration.
    \Drupal::configFactory()->getEditable('e_invoice_cr.settings')
      // Set the submitted configuration setting.
      ->set('environment', $form_state->getValue('environment'))
      ->set('username', $form_state->getValue('username'))
      ->set('password', $form_state->getValue('password'))
      ->set('id_type', $form_state->getValue('id_type'))
      ->set('id', $form_state->getValue('id'))
      ->set('name', $form_state->getValue('name'))
      ->set('commercial_name', $form_state->getValue('commercial_name'))
      ->set('phone', $form_state->getValue('phone'))
      ->set('fax', $form_state->getValue('fax'))
      ->set('email', $form_state->getValue('email'))
      ->set('postal_code', $form_state->getValue('postal_code'))
      ->set('address', $form_state->getValue('address'))
      ->set('currency', $form_state->getValue('currency'))
      ->set('p12_cert', $form_state->getValue('p12_cert'))
      ->set('cert_password', $form_state->getValue('cert_password'))
      ->set('invoice_logo_file', $form_state->getValue('invoice_logo_file'))
      ->set('invoice_logo_file_crop', $form_state->getValue('image_invoice_crop'))
      ->save('file', $form_state->get('invoice_logo_file'));
    $fid = $form_state->getValue('p12_cert');
    $file_object = file_load($fid[0], $reset = FALSE);
    // Make copies and change the file names.
    file_copy($file_object, 'public://certs/cert.pfx', FILE_EXISTS_REPLACE);
    file_copy($file_object, 'public://certs/cert.p12', FILE_EXISTS_REPLACE);
    parent::submitForm($form, $form_state);
  }

}
