<?php

namespace Drupal\invoice_entity\Entity;

use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Invoice entities.
 *
 * @ingroup invoice_entity
 */
interface InvoiceEntityInterface extends RevisionableInterface, RevisionLogInterface, EntityChangedInterface, EntityOwnerInterface {

  const DOCUMENTATIONINFO = [
    'FE' => [
      'code' => '01',
      'label' => 'Electronic Bill',
      'xmltag' => 'FacturaElectronica',
      'xmlns' => 'https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/facturaElectronica',
    ],
    'ND' => [
      'code' => '02',
      'label' => 'Debit Note',
      'xmltag' => 'NotaDebitoElectronica',
      'xmlns' => 'https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/notaDebitoElectronica',
    ],
    'NC' => [
      'code' => '03',
      'label' => 'Credit Note',
      'xmltag' => 'NotaCreditoElectronica',
      'xmlns' => 'https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/notaCreditoElectronica',
    ],
    'TE' => [
      'code' => '04',
      'label' => 'Electronic Ticket',
      'xmltag' => 'TiqueteElectronico',
      'xmlns' => 'https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/tiqueteElectronico',
    ],
  ];

  const AVAILABLE_CURRENCY = [
    'CRC' => [
      'name' => 'Colones',
      'symbol' => '₡',
    ],
    'USD' => [
      'name' => 'Dolares',
      'symbol' => '$',
    ],
    'EUR' => [
      'name' => 'Euro',
      'symbol' => '€',
    ],
  ];

  /**
   * Gets the Invoice name.
   *
   * @return string
   *   Name of the Invoice.
   */
  public function getName();

  /**
   * Sets the Invoice name.
   *
   * @param string $name
   *   The Invoice name.
   *
   * @return \Drupal\invoice_entity\Entity\InvoiceEntityInterface
   *   The called Invoice entity.
   */
  public function setName($name);

  /**
   * Gets the Invoice type.
   *
   * @return string
   *   Type of the Invoice.
   */
  public function getInvoiceType();

  /**
   * Gets the Invoice creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Invoice.
   */
  public function getCreatedTime();

  /**
   * Sets the Invoice creation timestamp.
   *
   * @param int $timestamp
   *   The Invoice creation timestamp.
   *
   * @return \Drupal\invoice_entity\Entity\InvoiceEntityInterface
   *   The called Invoice entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Invoice published status indicator.
   *
   * Unpublished Invoice are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Invoice is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Invoice.
   *
   * @param bool $published
   *   TRUE to set this Invoice to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\invoice_entity\Entity\InvoiceEntityInterface
   *   The called Invoice entity.
   */
  public function setPublished($published);

  /**
   * Gets the Invoice revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Invoice revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\invoice_entity\Entity\InvoiceEntityInterface
   *   The called Invoice entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Invoice revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Invoice revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\invoice_entity\Entity\InvoiceEntityInterface
   *   The called Invoice entity.
   */
  public function setRevisionUserId($uid);

}
