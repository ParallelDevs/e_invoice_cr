<?php

namespace Drupal\provider_entity\Entity;

use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Provider entities.
 *
 * @ingroup provider_entity
 */
interface ProviderEntityInterface extends RevisionableInterface, RevisionLogInterface, EntityChangedInterface, EntityOwnerInterface {

  /**
   * Gets the Provider name.
   *
   * @return string
   *   Name of the Provider.
   */
  public function getName();

  /**
   * Sets the Provider name.
   *
   * @param string $name
   *   The Provider name.
   *
   * @return \Drupal\provider_entity\Entity\ProviderEntityInterface
   *   The called Provider entity.
   */
  public function setName($name);

  /**
   * Gets the Provider creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Provider.
   */
  public function getCreatedTime();

  /**
   * Sets the Provider creation timestamp.
   *
   * @param int $timestamp
   *   The Provider creation timestamp.
   *
   * @return \Drupal\provider_entity\Entity\ProviderEntityInterface
   *   The called Provider entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Provider published status indicator.
   *
   * Unpublished Provider are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Provider is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Provider.
   *
   * @param bool $published
   *   TRUE to set this Provider to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\provider_entity\Entity\ProviderEntityInterface
   *   The called Provider entity.
   */
  public function setPublished($published);

  /**
   * Gets the Provider revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Provider revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\provider_entity\Entity\ProviderEntityInterface
   *   The called Provider entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Provider revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Provider revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\provider_entity\Entity\ProviderEntityInterface
   *   The called Provider entity.
   */
  public function setRevisionUserId($uid);

}
