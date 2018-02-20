<?php

namespace Drupal\tax_entity\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for Tax entity edit forms.
 *
 * @ingroup tax_entity
 */
class TaxEntityForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\tax_entity\Entity\TaxEntity */
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

    $states = [
      'visible' => [':input[id="edit-exoneration-value"]' => ['checked' => TRUE]],
      'required' => [':input[id="edit-exoneration-value"]' => ['checked' => FALSE]],
    ];

    // Only visible if the tax has a exoneration.
    $form['ex_document_type']['#states'] = $states;
    $form['ex_document_number']['#states'] = $states;
    $form['ex_institution']['#states'] = $states;
    $form['ex_date']['#states'] = $states;
    $form['ex_percentage']['#states'] = $states;

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $entity = parent::validateForm($form, $form_state);
    $this->checkExoneration($form, $form_state);
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

    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Tax entity.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Tax entity.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.tax_entity.canonical', ['tax_entity' => $entity->id()]);
  }

  /**
   * Function to check if all the data relate to exoneration was filled.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  private function checkExoneration(array $form, FormStateInterface $form_state) {
    if ($form_state->getValue('exoneration')['value']) {
      $type = empty($form_state->getValue('ex_document_type')[0]['value']);
      $number = empty($form_state->getValue('ex_document_number')[0]['value']);
      $date = empty($form_state->getValue('ex_date')[0]['value']);
      $percentage = empty($form_state->getValue('ex_percentage')[0]['value']);

      if ($type || $number || $date || $percentage ) {
        $form_state->setErrorByName('exoneration', t('If you check in "Add exoneration" you must fill all the fields relate with it.'));
      }
    }
  }

}
