<?php

namespace Drupal\customer_entity;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\customer_entity\Entity\CustomerEntityInterface;

/**
 * Defines the storage handler class for Customer entities.
 *
 * This extends the base storage class, adding required special handling for
 * Customer entities.
 *
 * @ingroup customer_entity
 */
class CustomerEntityStorage extends SqlContentEntityStorage implements CustomerEntityStorageInterface {

  /**
   * Gets a list of Customer revision IDs for a specific Customer.
   *
   * @param \Drupal\customer_entity\Entity\CustomerEntityInterface $entity
   *   The Customer entity.
   *
   * @return int[]
   *   Customer revision IDs (in ascending order).
   */
  public function revisionIds(CustomerEntityInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {customer_entity_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {customer_entity_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(CustomerEntityInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {customer_entity_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('customer_entity_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
