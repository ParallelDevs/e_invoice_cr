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
   * {@inheritdoc}
   */
  public function revisionIds(InvoiceReceivedEntityInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {invoice_received_entity_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {invoice_received_entity_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(InvoiceReceivedEntityInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {invoice_received_entity_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('invoice_received_entity_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
