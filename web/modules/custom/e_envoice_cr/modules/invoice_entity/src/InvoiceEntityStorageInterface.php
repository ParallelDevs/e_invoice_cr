<?php

namespace Drupal\invoice_entity;

use Drupal\Core\Entity\ContentEntityStorageInterface;
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
interface InvoiceEntityStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Invoice revision IDs for a specific Invoice.
   *
   * @param \Drupal\invoice_entity\Entity\InvoiceEntityInterface $entity
   *   The Invoice entity.
   *
   * @return int[]
   *   Invoice revision IDs (in ascending order).
   */
  public function revisionIds(InvoiceEntityInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Invoice author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Invoice revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\invoice_entity\Entity\InvoiceEntityInterface $entity
   *   The Invoice entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(InvoiceEntityInterface $entity);

  /**
   * Unsets the language for all Invoice with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
