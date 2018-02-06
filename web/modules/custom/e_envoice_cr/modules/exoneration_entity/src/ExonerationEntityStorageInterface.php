<?php

namespace Drupal\exoneration_entity;

use Drupal\Core\Entity\ContentEntityStorageInterface;
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
interface ExonerationEntityStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Exoneration entity revision IDs for a specific Exoneration entity.
   *
   * @param \Drupal\exoneration_entity\Entity\ExonerationEntityInterface $entity
   *   The Exoneration entity entity.
   *
   * @return int[]
   *   Exoneration entity revision IDs (in ascending order).
   */
  public function revisionIds(ExonerationEntityInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Exoneration entity author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Exoneration entity revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\exoneration_entity\Entity\ExonerationEntityInterface $entity
   *   The Exoneration entity entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(ExonerationEntityInterface $entity);

  /**
   * Unsets the language for all Exoneration entity with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
