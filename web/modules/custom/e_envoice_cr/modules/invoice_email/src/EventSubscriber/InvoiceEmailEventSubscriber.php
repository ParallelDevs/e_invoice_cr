<?php

namespace Drupal\invoice_email\EventSubscriber;

use Drupal\invoice_email\InvoiceEmailEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\paragraphs\Entity\Paragraph;

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
    $events[InvoiceEmailEvent::SUBMIT][] = ['invoiceEmailSendPdf', 800];
    return $events;
  }

  /**
   * Subscriber Callback for the event.
   */
  public function invoiceEmailSendPdf(InvoiceEmailEvent $event) {
    // Build the email message.
    $entityId = $event->getEntityId();
    if (!is_null($entityId)) {
      $em = \Drupal::entityTypeManager();
      $entity = $em->getStorage("invoice_entity")->load($entityId);
      if (!is_null($entity)) {
        $rows = $entity->get("field_rows")->getValue();
        $customerId = $entity->get("field_client")->getValue();
        $customer = $em->getStorage("customer_entity")->load($customerId[0]['target_id']);
        $fieldEmail = $customer->get("field_email")->getValue();
        $customerEmail = $fieldEmail[0]['value'];
        $details = "";
        foreach ($rows as $index => $item) {
          $paragraph = Paragraph::load($item['target_id']);
          $detail = $paragraph->get('field_detail')->value;
          $details = $detail . "\n";
        }
        global $base_url;
        $pdfUrl = $base_url . "/print/pdf/invoice_entity/" . $entityId;
        $message = t("This is the confirmation of an invoice generated.\nInvoice details: \n@details\nTo see the complete pdf invoice go to: @url",
          ['@details' => $details, '@url' => $pdfUrl]);

        // Set the email parameters.
        $mailManager = \Drupal::service('plugin.manager.mail');
        $module = 'invoice_email';
        $key = 'invoice_validated';
        $to = $customerEmail;
        $params['message'] = $message;
        $params['title'] = "Electronic invoice.";
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

}
