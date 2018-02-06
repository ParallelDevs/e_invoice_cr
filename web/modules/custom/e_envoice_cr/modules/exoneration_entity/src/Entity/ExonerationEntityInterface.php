<?php

namespace Drupal\exoneration_entity\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Exoneration entity entities.
 *
 * @ingroup exoneration_entity
 */
interface ExonerationEntityInterface extends ContentEntityInterface, RevisionLogInterface, EntityChangedInterface, EntityOwnerInterface {

  /**
   * Gets the Exoneration entity name.
   *
   * @return string
   *   Name of the Exoneration entity.
   */
  public function getName();

  /**
   * Sets the Exoneration entity name.
   *
   * @param string $name
   *   The Exoneration entity name.
   *
   * @return \Drupal\exoneration_entity\Entity\ExonerationEntityInterface
   *   The called Exoneration entity entity.
   */
  public function setName($name);

  /**
   * Gets the Exoneration entity creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Exoneration entity.
   */
  public function getCreatedTime();

  /**
   * Sets the Exoneration entity creation timestamp.
   *
   * @param int $timestamp
   *   The Exoneration entity creation timestamp.
   *
   * @return \Drupal\exoneration_entity\Entity\ExonerationEntityInterface
   *   The called Exoneration entity entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Exoneration entity published status indicator.
   *
   * Unpublished Exoneration entity are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Exoneration entity is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Exoneration entity.
   *
   * @param bool $published
   *   TRUE to set this Exoneration entity to published,
   *   FALSE to set it to unpublished.
   *
   * @return \Drupal\exoneration_entity\Entity\ExonerationEntityInterface
   *   The called Exoneration entity entity.
   */
  public function setPublished($published);

  /**
   * Gets the Exoneration entity revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Exoneration entity revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\exoneration_entity\Entity\ExonerationEntityInterface
   *   The called Exoneration entity entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Exoneration entity revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Exoneration entity revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\exoneration_entity\Entity\ExonerationEntityInterface
   *   The called Exoneration entity entity.
   */
  public function setRevisionUserId($uid);

}
