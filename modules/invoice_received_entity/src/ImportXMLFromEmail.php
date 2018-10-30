<?php
 namespace Drupal\invoice_received_entity;
 use Drupal\invoice_received_entity\Entity\InvoiceReceivedEntity;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\provider_entity\Entity\ProviderEntity;
 /**
 * Import XML from a inbox gmail account and mapping the entities in Drupal.
 */
class ImportXMLFromEmail {
   /**
   * Get the unread emails and save the xml attached of that emails locally.
   *
   * @return array
   *   An array with the paths of the xml files saved.
   */
  public function getXmlFilesFromEmails($inbox, $emails) {
    $count = 0;
    $paths = [];
    rsort($emails);
    foreach ($emails as $email_number) {
      $structure = imap_fetchstructure($inbox, $email_number);
      $attachments = [];
      if (isset($structure->parts) && count($structure->parts)) {
        for ($i = 0; $i < count($structure->parts); $i++) {
          $attachments[$i] = [
            'is_attachment' => FALSE,
            'filename' => '',
            'name' => '',
            'attachment' => '',
          ];
          if ($structure->parts[$i]->ifdparameters) {
            foreach ($structure->parts[$i]->dparameters as $object) {
              if (strtolower($object->attribute) == 'filename') {
                $attachments[$i]['is_attachment'] = TRUE;
                $attachments[$i]['filename'] = imap_utf8($object->value);
              }
            }
          }
          if ($structure->parts[$i]->ifparameters) {
            foreach ($structure->parts[$i]->parameters as $object) {
              if (strtolower($object->attribute) == 'name') {
                $attachments[$i]['is_attachment'] = TRUE;
                $attachments[$i]['name'] = imap_utf8($object->value);
              }
            }
          }
          if ($attachments[$i]['is_attachment']) {
            $attachments[$i]['attachment'] = imap_fetchbody($inbox, $email_number, $i + 1);
            // 3 = BASE64.
            if ($structure->parts[$i]->encoding == 3) {
              $attachments[$i]['attachment'] = base64_decode($attachments[$i]['attachment']);
            }
            // 4 = QUOTED-PRINTABLE.
            elseif ($structure->parts[$i]->encoding == 4) {
              $attachments[$i]['attachment'] = quoted_printable_decode($attachments[$i]['attachment']);
            }
          }
        }
      }
      foreach ($attachments as $attachment) {
        if ($attachment['is_attachment'] == 1) {
          if (strpos($attachment['name'], '.xml') !== FALSE) {
            $filename = $attachment['name'];
            $folder = "attachment";
            if (!is_dir($folder)) {
              mkdir($folder);
            }
            $fp = fopen("./" . $folder . "/" . $email_number . "-" . $filename, "w+");
            fwrite($fp, $attachment['attachment']);
            fclose($fp);
            $paths[$count] = "./" . $folder . "/" . $email_number . "-" . $filename;
            $count++;
          }
        }
      }
    }
    return $paths;
  }
   /**
   * Gets the data from xml file, create a invoice received and save that.
   *
   * @return bool
   *   Determinates if the entity saved successfully or not.
   */
  public function createInvoiceReceivedEntityFromXml($file_xml) {
    $settings = \Drupal::config('e_invoice_cr.settings');
    $date = date('Y-m-d\Th:i:s', strtotime($file_xml->FechaEmision));
    $entity = new InvoiceReceivedEntity([], 'invoice_received_entity');
    $entity->set('field_ir_numeric_key', $file_xml->Clave);
    $entity->set('field_ir_senders_id', str_pad($file_xml->Emisor->Identificacion->Numero, 12, '0', STR_PAD_LEFT));
    $entity->set('field_ir_invoice_date', $date);
    $entity->set('field_ir_total_tax', floatval($file_xml->ResumenFactura->TotalImpuesto));
    $entity->set('field_ir_total', floatval($file_xml->ResumenFactura->TotalComprobante));
    $entity->set('field_ir_sale_condition', $file_xml->CondicionVenta);
    $entity->set('field_ir_currency', $file_xml->ResumenFactura->CodigoMoneda);
    $entity->set('field_ir_senders_name', $file_xml->Emisor->NombreComercial);
    $entity->set('field_ir_total_discount', floatval($file_xml->ResumenFactura->TotalDescuento));
    $entity->set('field_ir_total_net_sale', floatval($file_xml->ResumenFactura->TotalVentaNeta));
    $entity->set('field_ir_number_key_r', $file_xml->Receptor->Identificacion->Numero, 12, '0', STR_PAD_LEFT);
    $entity->setNewRevision();
    $entity->setRevisionCreationTime(REQUEST_TIME);
    $entity->setRevisionUserId(\Drupal::currentUser()->id());
    // Invoice's rows.
    /** @var \SimpleXMLElement $serviceDetail */
    $serviceDetail = $file_xml->DetalleServicio;
    if ($serviceDetail->count()) {
      $rowsCount = $serviceDetail->LineaDetalle->count();
      for ($i = 0; $i < $rowsCount; $i++) {
        $entity = $this->addRowToEntity($serviceDetail->LineaDetalle[$i], $entity);
      }
      return $entity->save();
    }
  }
   /**
   * Takes and sets the row data of the xml file in the invoice received entity.
   *
   * @return \Drupal\invoice_received_entity\Entity\InvoiceReceivedEntity
   *   Return the invoice received entity with the row data.
   */
  public function addRowToEntity($row, $entity) {
    $paragraph = Paragraph::create(['type' => 'invoice_row']);
    $paragraph->set('field_code_type', $row->Codigo->Tipo);
    $paragraph->set('field_code', $row->Codigo->Codigo);
    $paragraph->set('field_detail', $row->Detalle);
    $paragraph->set('field_line_total_amount', floatval($row->MontoTotalLinea));
    $paragraph->set('field_quantity', floatval($row->Cantidad));
    $paragraph->set('field_subtotal', floatval($row->SubTotal));
    $paragraph->set('field_total_amount', floatval($row->MontoTotal));
    $paragraph->set('field_unit_measure', $row->UnidadMedida);
    $paragraph->set('field_unit_price', floatval($row->PrecioUnitario));
    $paragraph->isNew();
    $paragraph->save();
    $current = $entity->get('field_ir_rows')->getValue();
    $current[] = [
      'target_id' => $paragraph->id(),
      'target_revision_id' => $paragraph->getRevisionId(),
    ];
    $entity->set('field_ir_rows', $current);
    return $entity;
  }
   /**
   * Checks if the invoice was saved previously in the system.
   *
   * @return bool
   *   If the invoice exists or not in the system.
   */
  public function alreadyExistInvoiceReceivedEntity($number_key) {
    $connection = \Drupal::database();
    $query = $connection->select('invoice_received_entity_field_data', 'ire');
    $query->fields('ire', ['id']);
    $query->leftJoin('invoice_received_entity__field_ir_numeric_key', 'ire_nk',
      'ire.id = ire_nk.entity_id AND ire_nk.deleted = \'0\'');
    $query->condition('ire_nk.field_ir_numeric_key_value', $number_key, '=');
    $result = $query->execute();
    $fetch = $result->fetchAll();
    return !empty($fetch);
  }
   /**
   * Gets the data from the xml file, create a provider entity and save that.
   *
   * @return bool
   *   Determinates if the entity saved successfully or not.
   */
  public function createProviderEntityFromXml($file_xml) {
    $entity = new ProviderEntity([], 'provider_entity');
    $entity->set('langcode', "en");
    $entity->set('status', 1);
    $entity->set('default_langcode', TRUE);
    $entity->set('uuid', $file_xml->Emisor->Identificacion->Numero);
    $entity->set('field_type_id', $file_xml->Emisor->Identificacion->Tipo);
    $entity->set('field_provider_id', $file_xml->Emisor->Identificacion->Numero);
    $entity->set('name', $file_xml->Emisor->Nombre);
    $entity->set('field_commercial_name', $file_xml->Emisor->NombreComercial);
    $entity->set('field_phone', $file_xml->Emisor->Telefono->CodigoPais . $file_xml->Emisor->Telefono->NumTelefono);
    $entity->set('field_email', $file_xml->Emisor->CorreoElectronico);
    $entity->set('field_address', $file_xml->Emisor->Ubicacion->Provincia);
    $address_field = $entity->get('field_address')[0];
    $address_field->set('canton', $file_xml->Emisor->Ubicacion->Canton);
    $address_field->set('district', $file_xml->Emisor->Ubicacion->Distrito);
    $address_field->set('zipcode', $file_xml->Emisor->Ubicacion->Provincia . $file_xml->Emisor->Ubicacion->Canton . $file_xml->Emisor->Ubicacion->Distrito);
    $address_field->set('additionalinfo', $file_xml->Emisor->Ubicacion->OtrasSenas);
    $entity->setNewRevision();
    $entity->setRevisionCreationTime(REQUEST_TIME);
    $entity->setRevisionUserId(\Drupal::currentUser()->id());
    return $entity->save();
  }
   /**
   * Checks if the provider was saved previously in the system.
   *
   * @return bool
   *   If the provider already exists or not in the system.
   */
  public function alreadyExistProviderEntity($id) {
    $connection = \Drupal::database();
    $query = $connection->select('provider_entity', 'provider_entity');
    $query->fields('provider_entity', ['id']);
    $query->leftJoin('provider_entity__field_provider_id', 'provider_entity_id',
      'provider_entity.id = provider_entity_id.entity_id AND provider_entity_id.deleted = \'0\'');
    $query->condition('provider_entity_id.field_provider_id_value', $id, '=');
    $result = $query->execute();
    $fetch = $result->fetchAll();
    return !empty($fetch);
  }
 }