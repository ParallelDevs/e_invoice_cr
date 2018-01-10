<?php

namespace Drupal\invoice_email;

use Symfony\Component\EventDispatcher\Event;

class InvoiceEmailEvent extends Event{
  // set the event
  const SUBMIT = 'event.send.email.invoice';
  protected $referenceID;

  public function __construct($referenceID) {
    $this->referenceID = $referenceID;
  }

  public function getReferenceID() {
    return $this->referenceID;
  }
}
