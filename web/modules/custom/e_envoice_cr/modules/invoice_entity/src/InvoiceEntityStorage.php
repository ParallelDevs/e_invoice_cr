<?php

namespace Drupal\invoice_entity;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\invoice_entity\Entity\InvoiceEntityInterface;

/**
 * Defines the storage handler class for Invoice entities.
 *
 * This extends the base storage class, adding required special handling for
 * Invoice entities.
 *
 * @ingroup invoice_entity
 */
class InvoiceEntityStorage extends SqlContentEntityStorage implements InvoiceEntityStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(InvoiceEntityInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {invoice_entity_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {invoice_entity_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(InvoiceEntityInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {invoice_entity_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('invoice_entity_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
