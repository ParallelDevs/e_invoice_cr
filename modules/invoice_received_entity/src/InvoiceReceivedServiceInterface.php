<?php

namespace Drupal\invoice_received_entity;

/**
 * Interface InvoiceReceivedServiceInterface.
 */
interface InvoiceReceivedServiceInterface {

  /**
   * Get the unread emails and save the xml attached of that emails locally.
   *
   * @return array
   *   An array with the paths of the xml files saved.
   */
  public function getXmlFilesFromEmails($inbox, $emails);

  /**
   * Gets the data from xml file, create a invoice received and save that.
   *
   * @return bool
   *   Determinates if the entity saved successfully or not.
   */
  public function createInvoiceReceivedEntityFromXml($file_xml);

  /**
   * Takes and sets the row data of the xml file in the invoice received entity.
   *
   * @return \Drupal\invoice_received_entity\Entity\InvoiceReceivedEntity
   *   Return the invoice received entity with the row data.
   */
  public function addRowToEntity($row, $entity);

  /**
   * Checks if the invoice was saved previously in the system.
   *
   * @return bool
   *   If the invoice exists or not in the system.
   */
  public function alreadyExistInvoiceReceivedEntity($number_key);

  /**
   * Gets the data from the xml file, create a provider entity and save that.
   *
   * @return bool
   *   Determinates if the entity saved successfully or not.
   */
  public function createProviderEntityFromXml($file_xml);

  /**
   * Checks if the provider was saved previously in the system.
   *
   * @return bool
   *   If the provider already exists or not in the system.
   */
  public function alreadyExistProviderEntity($id);

  /**
   * Find (if exists) the last entity saved for get the entity_key values.
   *
   * @param string $table_name
   *   The table name in database.
   *
   * @return array
   *   An array with the entity_key values.
   */
  public function getNewData($table_name);

}
