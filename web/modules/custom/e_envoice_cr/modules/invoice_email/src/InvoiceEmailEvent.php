<?php

namespace Drupal\invoice_email;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class InvoiceEmailEvent.
 */
class InvoiceEmailEvent extends Event {
  // Set the event.
  const SUBMIT = 'event.send.email.invoice';
  protected $referenceID;

  /**
   * Constructs a new InvoiceEmailEvent object.
   */
  public function __construct($referenceID) {
    $this->referenceID = $referenceID;
  }

  /**
   * Gets the reference Id.
   */
  public function getReferenceID() {
    return $this->referenceID;
  }

}
