<?php

namespace Drupal\imap_settings\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure imap_settings settings for this site.
 */
class ImapSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'imap_settings_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'imap_settings.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $settings = \Drupal::config('imap_settings.settings');
    $flags = [
      "" => $this->t("No flag selected"),
      "/service" => $this->t("service"),
      "/user" => $this->t("user"),
      "/authuser" => $this->t("authuser"),
      "/anonymous" => $this->t("anonymous"),
      "/debug" => $this->t("debug"),
      "/secure" => $this->t("secure"),
      "/imap" => $this->t("imap"),
      "/pop3" => $this->t("pop3"),
      "/nntp" => $this->t("nntp"),
      "/norsh" => $this->t("norsh"),
      "/ssl" => $this->t("ssl"),
      "/validate-cert" => $this->t("validate-cert"),
      "/novalidate-cert" => $this->t("novalidate-cer"),
      "/tls" => $this->t("tls"),
      "/notls" => $this->t("notls"),
      "/readonly" => $this->t("readonly"),
    ];
    // Get default values.
    $remote = $settings->get('remote');
    $port = $settings->get('port');
    $flag = $settings->get('flag');
    $mailbox = $settings->get('mailbox');
    $username = $settings->get('username');
    $password = $settings->get('password');

    if (strcmp($mailbox, '') == 0) {
      $mailbox = 'INBOX';
    }

    $form['description'] = [
      '#markup' => '<p>' . $this->t('This page allows you to configure settings which determines how open an IMAP Stream to a mailbox. This function can also be used to open streams to POP3 and NNTP servers, but some functions and features are only available on IMAP servers.') . '</p>',
    ];

    $form['form'] = [
      '#id' => 'form',
      '#type' => 'details',
      '#title' => $this->t('IMAP Settings'),
      '#open' => TRUE,
    ];

    $form['form']['remote'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Remote system name:'),
      '#default_value' => $remote,
      '#required' => TRUE,
      '#description' => $this->t('Internet domain name or bracketed IP address of server.'),
      '#validated' => FALSE,
    ];
    $form['form']['port'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Port:'),
      '#default_value' => $port,
      '#required' => TRUE,
      '#description' => $this->t('Optional TCP port number, default is the default port for that service.'),
      '#size' => 12,
      '#maxlength' => 12,
      '#validated' => TRUE,
    ];
    $form['form']['flags'] = [
      '#type' => 'select',
      '#title' => $this->t('Select a optional flag.'),
      '#default_value' => $flag,
      '#required' => FALSE,
      '#options' => $flags,
      '#description' => $this->t('Optional flags.'),
      '#validated' => TRUE,
    ];
    $form['form']['mailbox'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Mailbox name:'),
      '#default_value' => $mailbox,
      '#description' => $this->t('Remote mailbox name.'),
      '#required' => TRUE,
      '#validated' => FALSE,
    ];
    $form['form']['username'] = [
      '#type' => 'email',
      '#title' => $this->t('Username:'),
      '#default_value' => $username,
      '#description' => $this->t('A username required by the IMAP configuration.'),
      '#required' => TRUE,
      '#validated' => FALSE,
    ];
    $form['form']['password'] = [
      '#type' => 'password',
      '#title' => $this->t('Password:'),
      '#default_value' => $password,
      '#required' => TRUE,
      '#validated' => FALSE,
      '#description' => $this->t('A password required by the IMAP configuration.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    if (!is_numeric($values['port'])) {
      $form_state->setErrorByName('port', $this->t('This field should only have numbers. No spaces or special characters.'));
    }

    if (strcmp($values['flags'], '') == 0) {
      $form_state->setErrorByName('flags', $this->t('This field should have selected a option.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    // Retrieve the configuration.
    \Drupal::configFactory()->getEditable('imap_settings.settings')
      // Set the submitted configuration setting.
      ->set('remote', $values['remote'])
      ->set('port', $values['port'])
      ->set('flag', $values['flags'])
      ->set('mailbox', $values['mailbox'])
      ->set('username', $values['username'])
      ->set('password', $values['password'])
      ->save();

    parent::submitForm($form, $form_state);
  }

}
