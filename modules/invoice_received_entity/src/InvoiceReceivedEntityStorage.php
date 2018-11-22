<?php

namespace Drupal\invoice_received_entity;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\invoice_received_entity\Entity\InvoiceReceivedEntityInterface;

/**
 * Defines the storage handler class for Invoice received entity entities.
 *
 * This extends the base storage class, adding required special handling for
 * Invoice received entity entities.
 *
 * @ingroup invoice_received_entity
 */
class InvoiceReceivedEntityStorage extends SqlContentEntityStorage implements InvoiceReceivedEntityStorageInterface {

  /**
   * Gets a list of revision IDs for a specific Invoice received entity.
   *
   * @param \Drupal\invoice_received_entity\Entity\InvoiceReceivedEntityInterface $entity
   *   The Invoice received entity entity.
   *
   * @return int[]
   *   Invoice received entity revision IDs (in ascending order).
   */
  public function revisionIds(InvoiceReceivedEntityInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {invoice_received_entity_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * Gets a list of revision IDs having a given user as entity author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Invoice received entity revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {invoice_received_entity_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\invoice_received_entity\Entity\InvoiceReceivedEntityInterface $entity
   *   The Invoice received entity entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(InvoiceReceivedEntityInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {invoice_received_entity_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * Unsets language for all Invoice received entity with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('invoice_received_entity_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
