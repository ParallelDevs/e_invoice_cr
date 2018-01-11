<?php

namespace Drupal\tax_entity\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Tax entity entities.
 *
 * @ingroup tax_entity
 */
interface TaxEntityInterface extends ContentEntityInterface, RevisionLogInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Tax entity name.
   *
   * @return string
   *   Name of the Tax entity.
   */
  public function getName();

  /**
   * Sets the Tax entity name.
   *
   * @param string $name
   *   The Tax entity name.
   *
   * @return \Drupal\tax_entity\Entity\TaxEntityInterface
   *   The called Tax entity entity.
   */
  public function setName($name);

  /**
   * Gets the Tax entity creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Tax entity.
   */
  public function getCreatedTime();

  /**
   * Sets the Tax entity creation timestamp.
   *
   * @param int $timestamp
   *   The Tax entity creation timestamp.
   *
   * @return \Drupal\tax_entity\Entity\TaxEntityInterface
   *   The called Tax entity entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Tax entity published status indicator.
   *
   * Unpublished Tax entity are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Tax entity is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Tax entity.
   *
   * @param bool $published
   *   TRUE to set this Tax entity to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\tax_entity\Entity\TaxEntityInterface
   *   The called Tax entity entity.
   */
  public function setPublished($published);

  /**
   * Gets the Tax entity revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Tax entity revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\tax_entity\Entity\TaxEntityInterface
   *   The called Tax entity entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Tax entity revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Tax entity revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\tax_entity\Entity\TaxEntityInterface
   *   The called Tax entity entity.
   */
  public function setRevisionUserId($uid);

}
