<?php

namespace Drupal\tax_entity;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\tax_entity\Entity\TaxEntityInterface;

/**
 * Defines the storage handler class for Tax entity entities.
 *
 * This extends the base storage class, adding required special handling for
 * Tax entity entities.
 *
 * @ingroup tax_entity
 */
class TaxEntityStorage extends SqlContentEntityStorage implements TaxEntityStorageInterface {

  /**
   * Gets a list of Tax revision IDs for a specific Tax.
   *
   * @param \Drupal\tax_entity\Entity\TaxEntityInterface $entity
   *   The Tax entity.
   *
   * @return int[]
   *   Tax revision IDs (in ascending order).
   */
  public function revisionIds(TaxEntityInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {tax_entity_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * Gets a list of revision IDs having a given user as Tax entity author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Tax entity revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {tax_entity_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\tax_entity\Entity\TaxEntityInterface $entity
   *   The Tax entity entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(TaxEntityInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {tax_entity_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * Unsets the language for all Tax entity with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('tax_entity_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
