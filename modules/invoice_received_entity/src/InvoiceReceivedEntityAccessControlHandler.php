<?php

namespace Drupal\invoice_received_entity;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Invoice received entity entity.
 *
 * @see \Drupal\invoice_received_entity\Entity\InvoiceReceivedEntity.
 */
class InvoiceReceivedEntityAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\invoice_received_entity\Entity\InvoiceReceivedEntityInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished invoice received entity entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published invoice received entity entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit invoice received entity entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete invoice received entity entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add invoice received entity entities');
  }

}
