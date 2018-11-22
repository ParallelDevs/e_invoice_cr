<?php

namespace Drupal\customer_entity\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for Customer edit forms.
 *
 * @ingroup customer_entity
 */
class CustomerEntityForm extends ContentEntityForm {

  /**
   * Form constructor.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form structure.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\customer_entity\Entity\CustomerEntity */
    $form = parent::buildForm($form, $form_state);
    // Set the name field as required.
    $form['name']['widget'][0]['value']['#required'] = TRUE;
    if (!$this->entity->isNew()) {
      $form['new_revision'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Create new revision'),
        '#default_value' => FALSE,
        '#weight' => 10,
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Fields to evaluate.
    $id = 'field_customer_id';
    $foreign_id = 'field_customer_foreign_id';
    $phone = 'field_phone';
    $address = 'field_direccion_';

    // Validating id field regarding the identification type.
    switch ($form_state->getValue('field_type_id')[0]['value']) {
      case "01":
        if (strlen($form_state->getValue($id)[0]['value']) !== 9) {
          $form_state->setErrorByName($id, $this->t("The id should have 9 characters, add zeros at the start if it\'s necessary."));
        }
        break;

      case "02":
        if (strlen($form_state->getValue($id)[0]['value']) !== 10) {
          $form_state->setErrorByName($id, $this->t("The id should have 10 characters, add zeros at the start if it\'s necessary."));
        }
        break;

      case "03":
        if (strlen($form_state->getValue($id)[0]['value']) < 11 || strlen($form_state->getValue('id')) > 12) {
          $form_state->setErrorByName($id, $this->t("The id should have 11 or 12 characters, add zeros at the start if it\'s necessary."));
        }
        break;

      case "04":
        if (strlen($form_state->getValue($id)[0]['value']) !== 10) {
          $form_state->setErrorByName($id, $this->t("The id should have 10 characters, add zeros at the start if it\'s necessary."));
        }
        break;

    }

    // Validating id field only have numeric values.
    if (!is_numeric($form_state->getValue($id)[0]['value'])) {
      $form_state->setErrorByName($id, $this->t("The ID field should only have numbers. No spaces or special characters."));
    }

    // Validating the foreign id field.
    if (!empty($form_state->getValue($foreign_id)[0]['value'])) {
      if (strlen($form_state->getValue($foreign_id)[0]['value']) < 12) {
        $form_state->setErrorByName($foreign_id,
          $this->t("The foreign id should have 12 characters, add zeros at the start if it's necessary.")
        );
      }

      // Validating foreign id field only have numeric values.
      if (!is_numeric($form_state->getValue($foreign_id)[0]['value'])) {
        $form_state->setErrorByName($foreign_id, $this->t("The foreign ID should only have numbers. No spaces or special characters."));
      }
    }

    // Validating telephone field.
    $phone = $form_state->getValue($phone)[0]['value'];
    if (!empty($phone) && !is_numeric($phone)) {
      $form_state->setErrorByName($phone, $this->t("The telephone number should only have numbers. No spaces or special characters."));
    }

    $count = 0;
    if (isset($form_state->getValue($address)[0])) {
      $filled_fields = array_filter($form_state->getValue($address)[0], function ($value) {
        return !empty($value);
      });
      $count = count($filled_fields);
    }

    // Validating address field.
    if ($count > 0 && $count < 5) {
      $form_state->setErrorByName($address, $this->t('If you are going to add the address information, please fill all the fields relate it.'));
    }

    // Validating additional information field have more of 40 characters.
    $additionalInfo = $form_state->getValue($address);
    if (strlen($additionalInfo[0]['additionalinfo']) > 40) {
      $form_state->setErrorByName('additionalinfo', $this->t('The additional information field need to have a maximum length of 40 characters.'));
    }

    parent::validateForm($form, $form_state);
  }

  /**
   * Form submission handler for the 'save' action.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
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

    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Customer.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Customer.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.customer_entity.canonical', ['customer_entity' => $entity->id()]);
  }

}
