<?php

namespace Drupal\invoice_received_entity\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\e_invoice_cr\Communication;
use Drupal\e_invoice_cr\Signature;
use Drupal\e_invoice_cr\XMLGenerator;
use Drupal\file\Entity\File;
use Drupal\invoice_received_entity\Entity\InvoiceReceivedEntity;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * Form controller for Invoice received entity edit forms.
 *
 * @ingroup invoice_received_entity
 */
class InvoiceReceivedEntityForm extends ContentEntityForm {

  private $file_xml;

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\invoice_received_entity\Entity\InvoiceReceivedEntity */
    $form = parent::buildForm($form, $form_state);

    if (!$this->entity->isNew()) {
      $form['new_revision'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Create new revision'),
        '#default_value' => FALSE,
        '#weight' => 10,
      ];
      $form['field_ir_xml_file']['#access'] = FALSE;
    } else {
      $form['field_ir_message']['#access'] = FALSE;
      $form['field_ir_message_detail']['#access'] = FALSE;
    }

    $entity = $this->entity;

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;
    $result = TRUE;

    if ($this->entity->isNew()) {
      $this->createEntityFromXML($form, $form_state);
    } else {
      // Look up for changes in the message field
      $entityOriginal = InvoiceReceivedEntity::load($entity->id());

      $current_state = $entityOriginal->get('field_ir_message')->value;
      $new_state = $form_state->getValue('field_ir_message')[0]['value'];

      if ($current_state !== $new_state) {
        $result = $this->sendInvoice($form_state);
      }
    }
    if ($result) {
      // Save as a new revision if requested to do so.
      if (!$form_state->isValueEmpty('new_revision') && $form_state->getValue('new_revision') != FALSE) {
        $entity->setNewRevision();

        // If a new revision is created, save the current user as revision author.
        $entity->setRevisionCreationTime(REQUEST_TIME);
        $entity->setRevisionUserId(\Drupal::currentUser()->id());
      } else {
        $entity->setNewRevision(FALSE);
      }

      $status = parent::save($form, $form_state);

      switch ($status) {
        case SAVED_NEW:
          drupal_set_message($this->t('Created the %label Invoice received entity.', [
            '%label' => $entity->label(),
          ]));
          break;

        default:
          drupal_set_message($this->t('Saved the %label Invoice received entity.', [
            '%label' => $entity->label(),
          ]));
      }
    }
    $form_state->setRedirect('entity.invoice_received_entity.canonical', ['invoice_received_entity' => $entity->id()]);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if ($this->entity->isNew()) {
      $field_list = $form_state->getValue('field_ir_xml_file');
      $file = File::load($field_list[0]['fids'][0]);

      if (!is_null($file)) {
        $simpleXml = simplexml_load_file($file->getFileUri());
        if ($this->alreadyExist($simpleXml->Clave)) {
          $form_state->setErrorByName('field_ir_xml_file',
            $this->t('The document you are trying to upload have been already uploaded.'));
        } else {
          $this->file_xml = $simpleXml;
        }
      }else{
        $form_state->setErrorByName('field_ir_xml_file', $this->t("The XML file field is required."));
      }
    }

    return parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  function createEntityFromXML(array $form, FormStateInterface $form_state) {
    $this->entity->set('document_key', 'Unassigned');
    $settings = \Drupal::config('e_invoice_cr.settings');
    $date = date('Y-m-d\Th:i:s', strtotime($this->file_xml->FechaEmision));

    $this->entity->set('field_ir_numeric_key', $this->file_xml->Clave);
    $this->entity->set('field_ir_senders_id', str_pad($this->file_xml->Emisor->Identificacion->Numero, 12, '0', STR_PAD_LEFT));
    $this->entity->set('field_ir_invoice_date', $date);
    $this->entity->set('field_ir_total_tax', $this->file_xml->ResumenFactura->TotalImpuesto);
    $this->entity->set('field_ir_total', $this->file_xml->ResumenFactura->TotalComprobante);
    $this->entity->set('field_ir_sale_condition', $this->file_xml->CondicionVenta);
    $this->entity->set('field_ir_currency', $this->file_xml->ResumenFactura->CodigoMoneda);
    $this->entity->set('field_ir_senders_name', $this->file_xml->Emisor->NombreComercial);
    //$this->entity->set('field_ir_credit_term', );
    $this->entity->set('field_ir_total_discount', $this->file_xml->ResumenFactura->TotalDescuento);
    $this->entity->set('field_ir_total_net_sale', $this->file_xml->ResumenFactura->TotalVentaNeta);
    $this->entity->set('field_ir_number_key_r', str_pad($settings->get('id'), 12, '0', STR_PAD_LEFT));

    // Invoice's rows
    /** @var \SimpleXMLElement $serviceDetail */
    $serviceDetail = $this->file_xml->DetalleServicio;
    $rowsCount = $serviceDetail->LineaDetalle->count();
    for ($i = 0; $i < $rowsCount; $i++) {
      $this->addRowToEntity($serviceDetail->LineaDetalle[$i]);
    }

  }

  /**
   * {@inheritdoc}
   */
  function addRowToEntity($row) {
    $paragraph = Paragraph::create(['type' => 'invoice_row']);
    $paragraph->set('field_code_type', $row->Codigo->Tipo);
    $paragraph->set('field_code', $row->Codigo->Codigo);
    $paragraph->set('field_detail', $row->Detalle);
    $paragraph->set('field_line_total_amount', $row->MontoTotalLinea);
    $paragraph->set('field_quantity', $row->Cantidad);
    $paragraph->set('field_subtotal', $row->SubTotal);
    $paragraph->set('field_total_amount', $row->MontoTotal);
    //$paragraph->set('field_row_type', $row->MontoTotal);
    $paragraph->set('field_unit_measure', $row->UnidadMedida);
    $paragraph->set('field_unit_price', $row->PrecioUnitario);
    $paragraph->isNew();
    $paragraph->save();
    $current = $this->entity->get('field_ir_rows')->getValue();
    $current[] = [
      'target_id' => $paragraph->id(),
      'target_revision_id' => $paragraph->getRevisionId()
    ];
    $this->entity->set('field_ir_rows', $current);
  }

  /**
   * {@inheritdoc}
   */
  function sendInvoice(FormStateInterface $form_state) {
    /** @var \Drupal\invoice_entity\InvoiceService $invoice_service */
    $invoice_service = \Drupal::service('invoice_entity.service');

    try {
      // Get authentication token for the API.
      $token = \Drupal::service('e_invoice_cr.authentication')->getLoginToken();
    }
    catch (Exception $e) {
      drupal_set_message(t('Error getting the authentication token.'), 'error');
      $form_state->setRebuild();
      $form_state->setSubmitHandlers([]);
    }

    $settingsFilled = $invoice_service->checkSettingsData();
    if (!$token || !$settingsFilled) {
      $error_message = !$token ?
        $this->t('Error getting the authentication token.') :
        $this->t('There is some missing configuration. Please go to: /admin/e-invoice-cr/settings');
      drupal_set_message($error_message, 'error');
      $form_state->setRebuild();
      $form_state->setSubmitHandlers([]);
      return FALSE;
    }
    else {
      $message = $this->entity->get('field_ir_message')->value;
      $newNumberKey = $invoice_service->getUniqueInvoiceKey($message, TRUE);
      $consecutive = $invoice_service->generateMessageConsecutive($message);

      $settings = \Drupal::config('e_invoice_cr.settings');
      $date_text = $this->entity->get('field_ir_invoice_date')->value;
      $date_object = strtotime($date_text);
      $date = \Drupal::service('date.formatter')->format($date_object, 'date_text', 'c');

      // Create XML document.
      // Generate the XML file with the invoice data.
      $xml_generator = new XMLGenerator();
      // Get the xml doc built.
      $xml = $xml_generator->generateReceiverMessageXML($this->entity, $newNumberKey, $consecutive);
      $xml->saveXML();
      // Create dir.
      $path = "public://xml/";
      $user_current = \Drupal::currentUser();
      $doc_name = "document-message" . $user_current->id() . "-" . $consecutive;
      file_prepare_directory($path, FILE_CREATE_DIRECTORY);
      $result = $xml->save('public://xml/' . $doc_name . ".xml", LIBXML_NOEMPTYTAG);

      // Sign document.
      $signature = new Signature();
      $response = $signature->signDocument($doc_name);

      // Look for possibles errors.
      foreach ($response as $item) {
        if ((strpos($item, 'Error') !== FALSE) || (strpos($item, 'Failed') !== FALSE)) {
          $message = t('There were errors during the signature process, the signature could be wrong.');
          drupal_set_message($message, 'warning');
        }
      }

      // Send document to API.
      $body_data = [
        'key' => $newNumberKey,
        'date' => $date,
        'e_type' => $settings->get('id_type'),
        'e_number' => $settings->get('id'),
      ];
      $communication = new Communication();
      // Get the document.
      $doc_uri = DRUPAL_ROOT . '/sites/default/files/xml_signed/' . $doc_name . 'segned.xml';
      // Get the xml content.
      $document = file_get_contents($doc_uri);
      // Sent the document.
      $response = $communication->sentDocument($document, $body_data, $token);
      // Show a error message.
      if (!is_null($response)) {
        if ($response->getStatusCode() != 202 && $response->getStatusCode() != 200) {
          // Reduce the consecutive.
          $invoice_service->decreaseValues();
          $message = t('The was a problem sending the electronic document.');
          drupal_set_message($message, 'error');
          $form_state->setRebuild();
          $form_state->setSubmitHandlers([]);
          return FALSE;
        }
        else {
          // Show a success message.
          $message = t('The electronic document was sent to its verification.');
          drupal_set_message($message, 'status');
          $this->entity->set('field_ir_status', InvoiceReceivedEntity::IR_SENT_HACIENDA);
          $this->entity->set('document_key', $newNumberKey);
          $this->entity->set('field_ir_number_key_r', $consecutive);
          $invoice_service->validateInvoiceReceivedEntity($this->entity);
          $invoice_service->increaseValues();
          $invoice_service->updateValues();
          //$this->checkInvoiceState($newNumberKey);
          return TRUE;
        }
      }
      else {
        return FALSE;
      }
    }

  }

  /**
   * {@inheritdoc}
   */
  function alreadyExist($number_key) {
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
   * @inheritdoc
   */
  function checkInvoiceState($number_key) {
    $queue = \Drupal::queue('validate_ir_queue');
    $data['id'] = $this->entity->id();
    $data['number_key'] = $number_key;
    $queue->createQueue();
    $queue->createItem($data);
    $queue->claimItem();
  }

}
