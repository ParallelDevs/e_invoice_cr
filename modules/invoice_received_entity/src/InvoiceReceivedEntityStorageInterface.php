<?php

namespace Drupal\invoice_received_entity;

use Drupal\Core\Entity\ContentEntityStorageInterface;
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
interface InvoiceReceivedEntityStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Invoice received entity revision IDs for a specific Invoice received entity.
   *
   * @param \Drupal\invoice_received_entity\Entity\InvoiceReceivedEntityInterface $entity
   *   The Invoice received entity entity.
   *
   * @return int[]
   *   Invoice received entity revision IDs (in ascending order).
   */
  public function revisionIds(InvoiceReceivedEntityInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Invoice received entity author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Invoice received entity revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\invoice_received_entity\Entity\InvoiceReceivedEntityInterface $entity
   *   The Invoice received entity entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(InvoiceReceivedEntityInterface $entity);

  /**
   * Unsets the language for all Invoice received entity with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
