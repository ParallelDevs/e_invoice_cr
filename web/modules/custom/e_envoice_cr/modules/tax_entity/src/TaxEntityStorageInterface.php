<?php

namespace Drupal\tax_entity;

use Drupal\Core\Entity\ContentEntityStorageInterface;
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
interface TaxEntityStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Tax entity revision IDs for a specific Tax entity.
   *
   * @param \Drupal\tax_entity\Entity\TaxEntityInterface $entity
   *   The Tax entity entity.
   *
   * @return int[]
   *   Tax entity revision IDs (in ascending order).
   */
  public function revisionIds(TaxEntityInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Tax entity author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Tax entity revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\tax_entity\Entity\TaxEntityInterface $entity
   *   The Tax entity entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(TaxEntityInterface $entity);

  /**
   * Unsets the language for all Tax entity with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
