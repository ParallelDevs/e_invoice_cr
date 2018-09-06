<?php

namespace Drupal\provider_entity;

use Drupal\Core\Entity\ContentEntityStorageInterface;
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
interface ProviderEntityStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Provider revision IDs for a specific Provider.
   *
   * @param \Drupal\provider_entity\Entity\ProviderEntityInterface $entity
   *   The Provider entity.
   *
   * @return int[]
   *   Provider revision IDs (in ascending order).
   */
  public function revisionIds(ProviderEntityInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Provider author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Provider revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\provider_entity\Entity\ProviderEntityInterface $entity
   *   The Provider entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(ProviderEntityInterface $entity);

  /**
   * Unsets the language for all Provider with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
