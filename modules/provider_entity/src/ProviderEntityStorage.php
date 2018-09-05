<?php

namespace Drupal\provider_entity;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\provider_entity\Entity\ProviderEntityInterface;

/**
 * Defines the storage handler class for Provider entities.
 *
 * This extends the base storage class, adding required special handling for
 * Provider entities.
 *
 * @ingroup provider_entity
 */
class ProviderEntityStorage extends SqlContentEntityStorage implements ProviderEntityStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(ProviderEntityInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {provider_entity_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {provider_entity_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(ProviderEntityInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {provider_entity_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('provider_entity_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
