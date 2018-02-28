<?php

namespace Drupal\invoice_email\EventSubscriber;

use Drupal\entity_print\Event\PrintEvents;
use Drupal\entity_print\Plugin\PrintEngineBase;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Class EntityPrintSubscriber.
 */
class EntityPrintSubscriber implements EventSubscriberInterface {

  /**
   * Subscriber Callback for the event.
   */
  public function alterConfiguration(GenericEvent $event) {
    // Check the url /print/pdf/entity_type/entity_id?orientation=portrait.
//    $request = \Drupal::request()->query->get('orientation');
//    if (!empty($request) && ($request == PrintEngineBase::PORTRAIT || $request == PrintEngineBase::LANDSCAPE)) {
//      $configuration = $event->getArgument('configuration');
//      $configuration['orientation'] = $request;
//      $event->setArgument('configuration', $configuration);
//    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      PrintEvents::CONFIGURATION_ALTER => 'alterConfiguration',
    ];
  }

}
