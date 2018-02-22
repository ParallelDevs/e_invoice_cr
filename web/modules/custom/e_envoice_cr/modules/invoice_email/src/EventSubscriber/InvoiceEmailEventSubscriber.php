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
    $events[InvoiceEmailEvent::SUBMIT][] = ['invoiceEmailSendPdf', 800];
    return $events;
  }

  /**
   * Subscriber Callback for the event.
   */
  public function invoiceEmailSendPdf(InvoiceEmailEvent $event) {
    // Add text.
    $eventId = $event->getReferenceID();
    $entityId = $event->getEntityId();

    // Send email.
    $mailManager = \Drupal::service('plugin.manager.mail');
    $module = 'invoice_email';
    $key = 'invoice_validated';
    $to = 'mau18nm@gmail.com';
    $params['message'] = "testttt";
    $params['title'] = "title 1";
    $langcode = \Drupal::currentUser()->getPreferredLangcode();
    $send = true;
    $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
    // Look for errors.
    if ($result['result'] != true) {
      $message = t('There was a problem sending your email notification to @email.', array('@email' => $to));
      drupal_set_message($message, 'error');
      \Drupal::logger('mail-log')->error($message);
      return;
    }
    else {
      $message = t('An email notification has been sent to @email ', array('@email' => $to));
      drupal_set_message($message);
      \Drupal::logger('mail-log')->notice($message);
    }
  }

}
