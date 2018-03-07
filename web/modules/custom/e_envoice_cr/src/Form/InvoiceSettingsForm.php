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
    $options_env = [
      "1" => $this->t("Production"),
      "2" => $this->t("Sandbox"),
    ];
    $options_id_type = [
      "01" => $this->t("Physical person id"),
      "02" => $this->t("Company id"),
      "03" => $this->t("DIMEX"),
      "04" => $this->t("NITE"),
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
    $logo_file = $settings->get('invoice_logo_file');
    $p12_cert = $settings->get('p12_cert');
    $cert_password = $settings->get('cert_password');
    $email_text = $settings->get('email_text');
    $email_subject = $settings->get('email_subject');
    $email_copies = $settings->get('email_copies');
    if (is_null($email_text)) {
      $email_text = "Find attached an Electronic Invoice with Key Number @invoice_id issued by @company on @date at @hour.\nYou can also download it at @url\n\nThis is an automatic notification, please do not reply this email.";
    }
    if (is_null($email_subject)) {
      $email_subject = "Electronic invoice issued by @company.";
    }

    $form['environment'] = [
      '#type' => 'select',
      '#title' => $this->t('Select the environment.'),
      '#default_value' => $environment,
      '#required' => TRUE,
      '#options' => $options_env,
      '#description' => $this->t('Select "Production" to set the production mode or "Sandbox" to set the tests mode.'),
      '#validated' => TRUE,
    ];
    $form['settings_tab'] = [
      '#type' => 'horizontal_tabs',
      '#tree' => TRUE,
      '#prefix' => '<div id="settings-invoice-wrapper">',
      '#suffix' => '</div>',
    ];

    $form['settings_tab']['stuff']['auth_group'] = [
      '#type' => 'details',
      '#title' => $this->t('API login information.'),
      '#description' => t('This module does the API login through the Oauth 2.0 token.'),
      '#collapsed' => FALSE,
    ];

    $form['settings_tab']['stuff']['auth_group']['username'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Username:'),
      '#default_value' => $username,
      '#required' => TRUE,
    ];

    $form['settings_tab']['stuff']['auth_group']['password'] = [
      '#type' => 'password',
      '#title' => $this->t('Password:'),
      '#default_value' => $password,
      '#required' => TRUE,
    ];

    $form['settings_tab']['stuff']['taxpayer_group'] = [
      '#type' => 'details',
      '#title' => $this->t('Taxpayer information.'),
      '#collapsed' => FALSE,
    ];

    $form['settings_tab']['stuff']['taxpayer_group']['id_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Id type.'),
      '#default_value' => $id_type,
      '#required' => TRUE,
      '#options' => $options_id_type,
      '#description' => $this->t("Select the taxpayer's id type."),
      '#validated' => TRUE,
    ];
    $form['settings_tab']['stuff']['taxpayer_group']['id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Id number:'),
      '#default_value' => $id,
      '#required' => TRUE,
      '#size' => 12,
      '#maxlength' => 12,
    ];
    $form['settings_tab']['stuff']['taxpayer_group']['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name:'),
      '#default_value' => $name,
      '#required' => TRUE,
    ];
    $form['settings_tab']['stuff']['taxpayer_group']['commercial_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Tradename:'),
      '#default_value' => $commercial_name,
      '#required' => TRUE,
    ];
    $form['settings_tab']['stuff']['taxpayer_group']['phone'] = [
      '#type' => 'tel',
      '#title' => $this->t('Phone number:'),
      '#default_value' => $phone,
      '#description' => $this->t('Please add the country code to the beginning. This field should only have numbers. No spaces or special characters.'),
      '#required' => TRUE,
    ];
    $form['settings_tab']['stuff']['taxpayer_group']['fax'] = [
      '#type' => 'tel',
      '#title' => $this->t('Fax number:'),
      '#default_value' => $fax,
      '#description' => $this->t('Please add the country code to the beginning. This field should only have numbers. No spaces or special characters.'),
      '#required' => FALSE,
    ];
    $form['settings_tab']['stuff']['taxpayer_group']['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email:'),
      '#default_value' => $email,
      '#required' => TRUE,
    ];
    $form['settings_tab']['stuff']['taxpayer_group']['address_fieldset'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Location.'),
    ];
    $form['settings_tab']['stuff']['taxpayer_group']['address_fieldset']['postal_code'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Zip code:'),
      '#default_value' => $postal_code,
      '#required' => TRUE,
    ];
    $form['settings_tab']['stuff']['taxpayer_group']['address_fieldset']['address'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Others:'),
      '#default_value' => $address,
      '#required' => TRUE,
    ];

    $form['settings_tab']['stuff']['email_text_group'] = [
      '#type' => 'details',
      '#title' => $this->t('Email notifications.'),
      '#collapsed' => FALSE,
    ];

    $form['settings_tab']['stuff']['email_text_group']['invoice_logo_file'] = [
      '#title' => $this->t('Company Logo'),
      '#type' => 'managed_file',
      '#description' => $this->t('Add a company logo that it will be print on the invoice documents.'),
      '#upload_validators' => [
        'file_validate_extensions' => ['png jpg jpeg'],
        'file_validate_image_resolution' => ["300x300", ""],
      ],
      '#default_value' => $logo_file,
      '#theme' => 'image_widget',
      '#preview_image_style' => 'medium',
      '#upload_location' => 'public://',
      '#required' => FALSE,
    ];
    $form['settings_tab']['stuff']['email_text_group']['email_subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Email subject'),
      '#description' => $this->t("Add a subject text that it will be printed on the email invoice notifications. Use @company to print your company name."),
      '#default_value' => $email_subject,
      '#required' => TRUE,
    ];
    $form['settings_tab']['stuff']['email_text_group']['email_text'] = [
      '#title' => $this->t('Email notifications text'),
      '#type' => 'textarea',
      '#description' => $this->t("Add a text that it will be printed on the email invoice notifications sent to the clients.\nUse @company to print your company name, @invoice_id to print the invoice id, @date to print the invoice date, @hour to print the hour and @url to print the pdf invoice link."),
      '#default_value' => $email_text,
      '#required' => TRUE,
    ];
    $form['settings_tab']['stuff']['email_text_group']['email_copies'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Send always copy to'),
      '#description' => $this->t("Insert email addresses to send always a copy, separate the emails using a comma, example: test@test.com, test2@test2.com."),
      '#default_value' => $email_copies,
      '#required' => FALSE,
    ];

    $form['settings_tab']['stuff']['cert_group'] = [
      '#type' => 'details',
      '#title' => $this->t('Certificate information.'),
      '#collapsed' => FALSE,
    ];

    $validators = ['file_validate_extensions' => ['p12']];
    $path = \Drupal::moduleHandler()->getModule('e_invoice_cr')->getPath();
    $form['settings_tab']['stuff']['cert_group']['p12_cert'] = [
      '#type' => 'managed_file',
      '#name' => 'Certificate p12.',
      '#title' => $this->t('Certificate p12.'),
      '#description' => $this->t('Load the authentication certificate p12 to create the signature.'),
      '#default_value' => $p12_cert,
      '#upload_validators' => $validators,
      '#upload_location' => 'public://certs/',
      '#required' => TRUE,
    ];

    $form['settings_tab']['stuff']['cert_group']['cert_password'] = [
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
      ->set('p12_cert', $form_state->getValue('p12_cert'))
      ->set('cert_password', $form_state->getValue('cert_password'))
      ->set('invoice_logo_file', $form_state->getValue('invoice_logo_file'))
      ->set('email_text', $form_state->getValue('email_text'))
      ->set('email_subject', $form_state->getValue('email_subject'))
      ->set('email_copies', $form_state->getValue('email_copies'))
      ->save('file', $form_state->get('invoice_logo_file'));

    $fid = $form_state->getValue('p12_cert');
    $file_object = file_load($fid[0], $reset = FALSE);
    // Make copies and change the file names.
    file_copy($file_object, 'public://certs/cert.pfx', FILE_EXISTS_REPLACE);
    file_copy($file_object, 'public://certs/cert.p12', FILE_EXISTS_REPLACE);
    parent::submitForm($form, $form_state);
  }

}
