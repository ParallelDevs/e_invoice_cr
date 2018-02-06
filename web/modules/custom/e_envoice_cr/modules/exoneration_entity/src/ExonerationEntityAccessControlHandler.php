<?php

namespace Drupal\exoneration_entity;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Exoneration entity entity.
 *
 * @see \Drupal\exoneration_entity\Entity\ExonerationEntity.
 */
class ExonerationEntityAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\exoneration_entity\Entity\ExonerationEntityInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished exoneration entity entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published exoneration entity entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit exoneration entity entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete exoneration entity entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add exoneration entity entities');
  }

}
