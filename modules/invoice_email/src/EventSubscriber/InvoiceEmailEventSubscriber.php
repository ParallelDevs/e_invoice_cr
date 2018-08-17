<?php

namespace Drupal\invoice_email\EventSubscriber;

use Drupal\invoice_email\InvoiceEmailEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class InvoiceEmailEventSubscriber.
 */
class InvoiceEmailEventSubscriber implements EventSubscriberInterface {

  /**
   * Constructs a new InvoiceEmailEventSubscriber object.
   */
  public function __construct() {

  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[InvoiceEmailEvent::SUBMIT][] = ['invoiceSendEmail', 800];
    return $events;
  }

  /**
   * Subscriber Callback for the event.
   */
  public function invoiceSendEmail(InvoiceEmailEvent $event) {
    // Build the email message.
    $entityId = $event->getEntityId();
    if (!is_null($entityId)) {
      $em = \Drupal::entityTypeManager();
      $entity = $em->getStorage("invoice_entity")->load($entityId);
      if (!is_null($entity) && !empty($entity->get("field_client")->getValue())) {
        // Define some required data.
        $invoice_id = $entity->get("field_numeric_key")->getValue()[0]['value'];
        $consecutive = $entity->get("field_consecutive_number")->getValue()[0]['value'];
        $invoice_date = $entity->get("field_invoice_date")->getValue()[0]['value'];
        $date_object = strtotime($invoice_date);
        $date = \Drupal::service('date.formatter')->format($date_object, 'custom', 'Y-m-d');
        $hour = \Drupal::service('date.formatter')->format($date_object, 'custom', 'H:i:s');
        $customerId = $entity->get("field_client")->getValue();
        $customer = $em->getStorage("customer_entity")->load($customerId[0]['target_id']);
        $fieldEmail = $customer->get("field_email")->getValue();
        $customerEmail = $fieldEmail[0]['value'];
        global $base_url;
        // Invoice pdf url.
        $pdf_url = $base_url . "/print/pdf/invoice_entity/" . $entityId;
        // Get data from configuration form.
        $settings = \Drupal::config('e_invoice_cr.settings');
        $company = $settings->get('name');
        $email_text = $settings->get('email_text');
        $copies = $settings->get('email_copies');
        $email_subject = $settings->get('email_subject');
        $email_subject = str_replace("@company", $company, $email_subject);
        // Build the message.
        $email_text = str_replace("@invoice_id", $invoice_id, $email_text);
        $email_text = str_replace("@company", $company, $email_text);
        $email_text = str_replace("@date", $date, $email_text);
        $email_text = str_replace("@hour", $hour, $email_text);
        $email_text = str_replace("@url", $pdf_url, $email_text);
        // Generate pdf file.
        $path = "public://pdf_invoice/";
        $file_name = "invoice_" . $entityId;
        file_prepare_directory($path, FILE_CREATE_DIRECTORY);
        $result = $this->generatePdfFile($path, $file_name, $entity);
        if ($result === FALSE || $result === 0) {
          $e_message = t('Email error. There was a problem attaching the pdf invoice.');
          drupal_set_message($e_message, 'error');
          \Drupal::logger('mail-log')->error($e_message);
        }
        else {
          // Set up the email attachment.
          $file = new \stdClass();
          $file->uri = $path . $file_name . ".pdf";
          $file->filename = $file_name . ".pdf";
          $file->filemime = 'application/pdf';
          $params['files'][] = $file;
        }

        // Attach xml.
        $uri = 'public://xml_signed/document-1-' . $consecutive . 'segned.xml';
        $file = new \stdClass();
        $file->uri = $uri;
        $file->filename = $consecutive . 'segned.xml';
        $file->filemime = 'application/xml';
        $params['files'][] = $file;

        // Set the email parameters.
        $mailManager = \Drupal::service('plugin.manager.mail');
        $module = 'invoice_email';
        $key = 'invoice_validated';
        $to = $customerEmail;
        if (!is_null($copies) && $copies !== "") {
          $params['cc'] = $copies;
        }
        $params['message'] = $email_text;
        $params['title'] = $email_subject;
        $langcode = \Drupal::currentUser()->getPreferredLangcode();
        $send = TRUE;

        // Send the email.
        $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);

        // Look for errors.
        if ($result['result'] != TRUE) {
          $message = t('There was a problem sending your email notification to @email.', ['@email' => $to]);
          drupal_set_message($message, 'error');
          \Drupal::logger('mail-log')->error($message);
          return;
        }
        else {
          $message = t('An email notification has been sent to @email', ['@email' => $to]);
          drupal_set_message($message);
          \Drupal::logger('mail-log')->notice($message);
        }
      }
    }
  }

  /**
   * Generates a pdf file.
   */
  public function generatePdfFile($path, $file_name, $entity) {
    $print_engine = \Drupal::service('plugin.manager.entity_print.print_engine')->createSelectedInstance('pdf');
    $html = \Drupal::service('entity_print.print_builder')->printHtml($entity, TRUE, FALSE);
    $print_engine->addPage($html);
    $output = $print_engine->getBlob();
    $file_name = $file_name . ".pdf";
    $result = file_put_contents($path . $file_name, $output);
    return $result;
  }

}
