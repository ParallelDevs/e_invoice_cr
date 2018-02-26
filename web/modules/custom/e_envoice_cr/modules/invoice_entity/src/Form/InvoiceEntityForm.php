<?php

namespace Drupal\invoice_entity\Form;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\customer_entity\Entity\CustomerEntity;
use Drupal\e_invoice_cr\Communication;
use Drupal\e_invoice_cr\Signature;
use Drupal\e_invoice_cr\XMLGenerator;
use Drupal\tax_entity\Entity\TaxEntity;

/**
 * Form controller for Invoice edit forms.
 *
 * @ingroup invoice_entity
 */
class InvoiceEntityForm extends ContentEntityForm {

  private $currency;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityManagerInterface $entity_manager, EntityTypeBundleInfoInterface $entity_type_bundle_info = NULL, TimeInterface $time = NULL) {
    parent::__construct($entity_manager, $entity_type_bundle_info, $time);
    $settings = \Drupal::config('e_invoice_cr.settings');
    if (!isset($settings) && is_null($settings)) {
      invoice_entity_config_error();
      $this->currency = NULL;
    }
    else {
      $this->currency = $settings->get('currency') === 'crc' ? '₡' : '$';
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\invoice_entity\Entity\InvoiceEntity */
    $form = parent::buildForm($form, $form_state);

    if (!$this->entity->isNew()) {
      $form['new_revision'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Create new revision'),
        '#default_value' => FALSE,
        '#weight' => 10,
      ];
    }

    $entity = $this->entity;

    $this->invoiceFormStructure($form, $form_state);

    return $form;
  }

  /**
   * Give to the invoice form the structure need it.
   */
  private function invoiceFormStructure(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\invoice_entity\InvoiceService $invoice_service */
    $invoice_service = \Drupal::service('invoice_entity.service');

    // The library.
    $form['#attached']['library'][] = 'invoice_entity/invoice-rows';
    $form['#attached']['library'][] = 'invoice_entity/invoice-rows-js';
    // Get all tax entities.
    $tax_info = $this->getMainTaxesInfo();

    $form['#attached']['drupalSettings']['taxsObject'] = $tax_info;
    // Get default currency.
    if (is_null($this->currency)) {
      invoice_entity_config_error();
      // Disable the submit button.
      $form['actions']['submit']['#disabled'] = TRUE;
    }

    $form['field_clave_numerica']['#disabled'] = 'disabled';
    $form['field_consecutivo']['#disabled'] = 'disabled';
    if ($this->entity->isNew()) {
      // Generate the invoice keys.
      $type_of = $form_state->getUserInput()['type_of'];
      $key = $type_of ? $invoice_service->getUniqueInvoiceKey($type_of) : $invoice_service->getUniqueInvoiceKey();
      if ($key == NULL) {
        invoice_entity_config_error();
      }
      else {
        $invoice_service->updateValues();
      }
      $form['field_clave_numerica']['widget'][0]['value']['#default_value'] = $key;
      $form['field_consecutivo']['widget'][0]['value']['#default_value'] = $invoice_service->generateConsecutive($type_of);
    }
    $this->formatField($form['field_total_discount']['widget'][0]['value'], TRUE, TRUE);
    $this->formatField($form['field_total_ventaneta']['widget'][0]['value'], TRUE, TRUE);
    $this->formatField($form['field_total_impuesto']['widget'][0]['value'], TRUE, TRUE);
    $this->formatField($form['field_totalcomprobante']['widget'][0]['value'], TRUE, TRUE);
    $this->formatField($form['field_total_impuesto']['widget'][0]['value'], FALSE, TRUE);
    $visible = [
      'select[id="edit-field-condicion-venta"]' => ['value' => '02'],
    ];
    $form['field_plazo_credito']['widget'][0]['value']['#states']['visible'] = $visible;
    for ($i = 0; $i >= 0; $i++) {
      if (array_key_exists($i, $form['field_filas']['widget'])) {
        // Rows.
        $this->formatField($form['field_filas']['widget'][$i]['subform']['field_preciounitario']['widget'][0]['value'], TRUE, FALSE);
        $this->formatField($form['field_filas']['widget'][$i]['subform']['field_monto_total_linea']['widget'][0]['value'], FALSE, TRUE);
        $this->formatField($form['field_filas']['widget'][$i]['subform']['field_montototal']['widget'][0]['value'], FALSE, TRUE);
        $this->formatField($form['field_filas']['widget'][$i]['subform']['field_subtotal']['widget'][0]['value'], FALSE, TRUE);
        $this->formatField($form['field_filas']['widget'][$i]['subform']['field_row_discount']['widget'][0]['value'], FALSE, TRUE);
        $visible_condition = [
          ':input[id="field-adddis-' . $i . '"]' => ['checked' => TRUE],
        ];
        $form['field_filas']['widget'][$i]['subform']['field_add_discount']['widget']['value']['#attributes']['id'] = 'field-adddis-' . $i;
        $form['field_filas']['widget'][$i]['subform']['field_discount_percentage']['widget'][0]['value']['#states']['visible'] = $visible_condition;
        $form['field_filas']['widget'][$i]['subform']['field_discount_reason']['widget'][0]['value']['#states']['visible'] = $visible_condition;
        $visible = [
          'select[data-drupal-selector="edit-field-filas-' . $i . '-subform-field-unit-measure"]' => ['value' => 'Otros'],
        ];
        $form['field_filas']['widget'][$i]['subform']['field_another_unit_measure']['widget'][0]['value']['#states']['visible'] = $visible;
      }
      else {
        break;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $entity = parent::validateForm($form, $form_state);
    $this->checkFieldConditionByTypes($form_state, 'field_medio_de_pago', [
      'FE',
      'TE',
    ], 'If you document is a Electronic Bill or Electronic Ticket. You need to specify the payment method.');

    $this->checkReferenceInformationRequired($form_state);

    return $entity;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = &$this->entity;

    // Save as a new revision if requested to do so.
    if (!$form_state->isValueEmpty('new_revision') && $form_state->getValue('new_revision') != FALSE) {
      $entity->setNewRevision();

      // If a new revision is created, save the current user as revision author.
      $entity->setRevisionCreationTime(REQUEST_TIME);
      $entity->setRevisionUserId(\Drupal::currentUser()->id());
    }
    else {
      $entity->setNewRevision(FALSE);
    }

    // Send and return a boolean if it was or not successful.
    $sent = $this->sendInvoice($form, $form_state);

    // If it was successful.
    if ($sent) {
      $status = parent::save($form, $form_state);

      switch ($status) {
        case SAVED_NEW:
          drupal_set_message($this->t('Created the %label Invoice.', [
            '%label' => $entity->label(),
          ]));
          break;

        default:
          drupal_set_message($this->t('Saved the %label Invoice.', [
            '%label' => $entity->label(),
          ]));
      }
      $form_state->setRedirect('entity.invoice_entity.canonical', ['invoice_entity' => $entity->id()]);
    }
    else {
      $form_state->setRebuild();
      $form_state->setSubmitHandlers([]);
    }
  }

  /**
   * Generate the xml document, sign it and send it to it's validation.
   *
   * @return bool
   *   Return true if did have no error.
   */
  public function sendInvoice(array $form, FormStateInterface $form_state) {
    // Authentication.
    try {
      // Get authentication token for the API.
      $token = \Drupal::service('e_invoice_cr.authentication')->getLoginToken();
    }
    catch (Exception $e) {
      drupal_set_message(t('Error getting the authentication token.'), 'error');
      $form_state->setRebuild();
      $form_state->setSubmitHandlers([]);
    }

    if (!$token) {
      drupal_set_message(t('Error getting the authentication token.'), 'error');
      $form_state->setRebuild();
      $form_state->setSubmitHandlers([]);
      return FALSE;
    }
    else {
      /** @var \Drupal\invoice_entity\InvoiceService $invoice_service */
      $invoice_service = \Drupal::service('invoice_entity.service');

      $settings = \Drupal::config('e_invoice_cr.settings');
      $date_text = $this->entity->get('field_fecha_emision')->value;
      $date_object = strtotime($date_text);
      $date = \Drupal::service('date.formatter')->format($date_object, 'date_text', 'c');
      $client_id = $this->entity->get('field_cliente')->target_id;
      $client = CustomerEntity::load($client_id);

      // Create XML document.
      // Generate the XML file with the invoice data.
      $xml_generator = new XMLGenerator();
      // Get the xml doc built.
      $xml = $xml_generator->generateXmlByEntity($this->entity);
      $xml->saveXML();
      // Create dir.
      $path = "public://xml/";
      $user_current = \Drupal::currentUser();
      $id_cons = $this->entity->get('field_consecutivo')->value;
      $doc_name = "document-" . $user_current->id() . "-" . $id_cons;
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
        'key' => $this->entity->get('field_clave_numerica')->value,
        'date' => $date,
        'e_type' => $settings->get('id_type'),
        'e_number' => $settings->get('id'),
        'c_type' => $client->get('field_type_id')->value,
        'c_number' => $client->get('field_customer_id')->value,
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
          $invoice_service->increaseValues();
        }
        $invoice_service->updateValues();
      }
      else {
        return FALSE;
      }
    }

    return TRUE;
  }

  /**
   * Validate if the fields inside of the reference information are need.
   */
  private function checkReferenceInformationRequired(FormStateInterface $form_state) {
    $message_notes = t('If you document is an Credit Note or Debit Note. You need to fill all the fields in the Reference Information section.');
    $message = t("If you're going to add a Reference please, fill all the fields in it.");
    $require_in = ['NC', 'ND'];
    $fields = [
      'ref_type_of',
      'ref_doc_key',
      'ref_date',
      'ref_code',
      'ref_reason',
    ];
    $filledValues = 0;
    foreach ($fields as $field) {
      $filledValues += !empty($form_state->getValue($field)[0]['value']) ? 1 : 0;
      $this->checkFieldConditionByTypes($form_state, $field, $require_in, $message_notes);
    }
    if ($filledValues > 0 && $filledValues < count($fields)) {
      $form_state->setErrorByName('ref_type_of', $message);
    }
  }

  /**
   * Check if the field is required regarding the type of the document.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   * @param string $field
   *   The field to check.
   * @param array $types
   *   The documents type where the field is required.
   * @param string $error_message
   *   The error message if the field is required and is not filled.
   */
  private function checkFieldConditionByTypes(FormStateInterface &$form_state, $field, array $types, $error_message) {
    $type_of = $form_state->getValue('type_of')[0]['value'];
    $required = in_array($type_of, $types);
    $value = $form_state->getValue($field)[0]['value'];
    if ($required && (is_null($value) || empty($value))) {
      $form_state->setErrorByName($field, $error_message);
    }
  }

  /**
   * Function that return an array with the basic information about taxes.
   *
   * @return array
   *   Array with some information about the taxes.
   */
  private function getMainTaxesInfo() {
    $entities = TaxEntity::loadMultiple();
    $tax_info = [];
    /** @var \Drupal\tax_entity\Entity\TaxEntity $tax */
    foreach ($entities as $tax) {
      $tax_info[$tax->id()] = [
        'tax_percentage' => $tax->get('field_tax_percentage')->value,
        'exoneration' => $tax->get('exoneration')->value,
        'ex_percentage' => $tax->get('ex_percentage')->value,
      ];
    }
    return $tax_info;
  }

  /**
   * Add some settings to a specific field.
   *
   * @param array $field
   *   The field that you want to change.
   * @param bool $addCurrency
   *   Add the currency symbol to the title.
   * @param bool $addReadOnly
   *   Add the read only property to the field.
   */
  private function formatField(array &$field, $addCurrency, $addReadOnly) {
    if ($addCurrency) {
      $field['#title'] .= ' ' . $this->currency;
    }
    if ($addReadOnly) {
      $field['#attributes'] = ['readonly' => 'readonly'];
    }
  }

}
