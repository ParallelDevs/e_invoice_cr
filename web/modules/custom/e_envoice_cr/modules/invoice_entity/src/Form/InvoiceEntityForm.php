<?php

namespace Drupal\invoice_entity\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\customer_entity\Entity\CustomerEntity;
use Drupal\e_invoice_cr\Communication;
use Drupal\e_invoice_cr\Signature;
use Drupal\invoice_email\InvoiceEmailEvent;
use Drupal\e_invoice_cr\XMLGenerator;

/**
 * Form controller for Invoice edit forms.
 *
 * @ingroup invoice_entity
 */
class InvoiceEntityForm extends ContentEntityForm {

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

    return $form;
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
        'c_type' => $client->get('field_tipo_de_identificacion')->value,
        'c_number' => $client->get('field_intensificacion')->value,
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

    // Load the Symfony event dispatcher object through services.
    $dispatcher = \Drupal::service('event_dispatcher');
    // Creating our event class object.
    $event = new InvoiceEmailEvent($form_state->getValue('name'));
    // Dispatching the event through the ‘dispatch’  method,
    // Passing event name and event object ‘$event’ as parameters.
    $dispatcher->dispatch(InvoiceEmailEvent::SUBMIT, $event);

    return TRUE;
  }

  /**
   * Validate if the fields inside of the reference information are need.
   */
  private function checkReferenceInformationRequired(FormStateInterface $form_state) {
    $message = t('If you document is an Credit Note or Debit Note. You need to fill all the fields in the Reference Information section.');
    $require_in = ['NC', 'ND'];
    $fields = [
      'ref_type_of',
      'ref_doc_key',
      'ref_date',
      'ref_code',
      'ref_reason',
    ];

    foreach ($fields as $field) {
      $this->checkFieldConditionByTypes($form_state, $field, $require_in, $message);
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

}
