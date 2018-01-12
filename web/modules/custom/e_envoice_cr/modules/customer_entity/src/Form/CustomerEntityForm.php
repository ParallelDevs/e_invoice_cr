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
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\customer_entity\Entity\CustomerEntity */
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
    $error_short_id = 'The @field is too short. The id number must have 12 characters, add zeros at the start if it\'s necessary.';
    $error_only_number = '@field should only have numbers. No spaces or special characters.';

    // Fields to evaluate.
    $id = 'field_intensificacion';
    $foreign_id = 'field_intensificacion_ex';
    $phone = 'field_telefono';

    // Validating id field.
    if (strlen($form_state->getValue($id)) < 12) {
      $form_state->setErrorByName($id, $this->t($error_short_id, array('@field' => 'ID')));
    }

    if (!is_numeric($form_state->getValue($id))) {
      $form_state->setErrorByName($id, $this->t($error_only_number, array('@field' => 'The ID field')));
    }

    // Validating the foreign id field.
    if (empty($form_state->getValue($foreign_id))) {  // Check only if it has a value.
      if (strlen($form_state->getValue($foreign_id)) < 12) {
        $form_state->setErrorByName($foreign_id, $this->t($error_short_id, ['@field' => 'Foreign ID']));
      }

      if (!is_numeric($form_state->getValue($foreign_id))) {
        $form_state->setErrorByName($foreign_id, $this->t($error_only_number, ['@field' => 'The Foreign ID']));
      }
    }

    // Validating telephone field.
    if (!is_numeric($form_state->getValue($phone))) {
      $form_state->setErrorByName($phone, $this->t($error_only_number, array('@field' => 'The telephone number')));
    }
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
