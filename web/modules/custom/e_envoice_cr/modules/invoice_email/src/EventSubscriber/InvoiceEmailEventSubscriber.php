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
  static function getSubscribedEvents() {
    $events[InvoiceEmailEvent::SUBMIT][] = array('invoiceEmailSendPdf', 800);
    return $events;
  }

  /**
   * Subscriber Callback for the event.
   * @param InvoiceEmailEvent $event
   */
  public function invoiceEmailSendPdf(InvoiceEmailEvent $event) {
    // add texct
    drupal_set_message("Merlin is working here.... " . $event->getReferenceID() . " as Reference");
  }
}