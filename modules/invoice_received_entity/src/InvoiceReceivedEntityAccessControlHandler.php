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
   * Performs access checks.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity storage object.
   * @param string $operation
   *   The entity operation.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user for which to check access.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
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
   * Performs create access checks.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user for which to check access.
   * @param array $context
   *   An array of key-value pairs to pass additional context when needed.
   * @param string $entity_bundle
   *   The bundle of the entity.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add invoice received entity entities');
  }

}
