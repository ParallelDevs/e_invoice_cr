<?php

namespace Drupal\invoice_received_entity\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Invoice received entity entities.
 *
 * @ingroup invoice_received_entity
 */
interface InvoiceReceivedEntityInterface extends ContentEntityInterface, RevisionLogInterface, EntityChangedInterface, EntityOwnerInterface {

  public const IR_WAITING_STATUS = 0;
  public const IR_SENT_HACIENDA = 1;
  public const IR_REJECTED_STATUS = 2;
  public const IR_ACCEPTED_STATUS = 3;
  public const IR_MESSAGES_STATES = [
    1 => [
      "state" => 'Accepted',
      "code" => '05',
    ],
    2 => [
      "state" => 'Partially Accepted',
      "code" => '06',
    ],
    3 => [
      "state" => 'Rejected',
      "code" => '06',
    ],
  ];

  /**
   * Gets the Invoice received entity name.
   *
   * @return string
   *   Name of the Invoice received entity.
   */
  public function getName();

  /**
   * Sets the Invoice received entity name.
   *
   * @param string $name
   *   The Invoice received entity name.
   *
   * @return \Drupal\invoice_received_entity\Entity\InvoiceReceivedEntityInterface
   *   The called Invoice received entity entity.
   */
  public function setName($name);

  /**
   * Gets the Invoice received entity creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Invoice received entity.
   */
  public function getCreatedTime();

  /**
   * Sets the Invoice received entity creation timestamp.
   *
   * @param int $timestamp
   *   The Invoice received entity creation timestamp.
   *
   * @return \Drupal\invoice_received_entity\Entity\InvoiceReceivedEntityInterface
   *   The called Invoice received entity entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Invoice received entity published status indicator.
   *
   * Unpublished Invoice received entity are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Invoice received entity is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Invoice received entity.
   *
   * @param bool $published
   *   TRUE to set this Invoice received entity to published, FALSE to set it 
   *   to unpublished.
   *
   * @return \Drupal\invoice_received_entity\Entity\InvoiceReceivedEntityInterface
   *   The called Invoice received entity entity.
   */
  public function setPublished($published);

  /**
   * Gets the Invoice received entity revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Invoice received entity revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\invoice_received_entity\Entity\InvoiceReceivedEntityInterface
   *   The called Invoice received entity entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Invoice received entity revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Invoice received entity revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\invoice_received_entity\Entity\InvoiceReceivedEntityInterface
   *   The called Invoice received entity entity.
   */
  public function setRevisionUserId($uid);

}
