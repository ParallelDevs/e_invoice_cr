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
   * Gets a list of Provider revision IDs for a specific Provider.
   *
   * @param \Drupal\provider_entity\Entity\ProviderEntityInterface $entity
   *   The Provider entity.
   *
   * @return int[]
   *   Provider revision IDs (in ascending order).
   */
  public function revisionIds(ProviderEntityInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {provider_entity_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * Gets a list of revision IDs having a given user as Provider author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Provider revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {provider_entity_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\provider_entity\Entity\ProviderEntityInterface $entity
   *   The Provider entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(ProviderEntityInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {provider_entity_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * Unsets the language for all Provider with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('provider_entity_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
