<?php

namespace Drupal\tax_entity;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Tax entity entity.
 *
 * @see \Drupal\tax_entity\Entity\TaxEntity.
 */
class TaxEntityAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\tax_entity\Entity\TaxEntityInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished tax entity entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published tax entity entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit tax entity entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete tax entity entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add tax entity entities');
  }

}
