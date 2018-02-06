<?php

namespace Drupal\exoneration_entity;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\exoneration_entity\Entity\ExonerationEntityInterface;

/**
 * Defines the storage handler class for Exoneration entity entities.
 *
 * This extends the base storage class, adding required special handling for
 * Exoneration entity entities.
 *
 * @ingroup exoneration_entity
 */
class ExonerationEntityStorage extends SqlContentEntityStorage implements ExonerationEntityStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(ExonerationEntityInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {exoneration_entity_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {exoneration_entity_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(ExonerationEntityInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {exoneration_entity_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('exoneration_entity_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
