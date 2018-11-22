<?php

namespace Drupal\invoice_received_entity\Form;

use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\invoice_received_entity\Entity\InvoiceReceivedEntityInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for reverting a Invoice received entity revision.
 *
 * @ingroup invoice_received_entity
 */
class InvoiceReceivedEntityRevisionRevertForm extends ConfirmFormBase {


  /**
   * The Invoice received entity revision.
   *
   * @var \Drupal\invoice_received_entity\Entity\InvoiceReceivedEntityInterface
   */
  protected $revision;

  /**
   * The Invoice received entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $InvoiceReceivedEntityStorage;

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * Constructs a new InvoiceReceivedEntityRevisionRevertForm.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $entity_storage
   *   The Invoice received entity storage.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   */
  public function __construct(EntityStorageInterface $entity_storage, DateFormatterInterface $date_formatter) {
    $this->InvoiceReceivedEntityStorage = $entity_storage;
    $this->dateFormatter = $date_formatter;
  }

  /**
   * Instantiates a new instance of this class.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The service container this instance should use.
   *
   * @return \Drupal\Core\Form\ConfirmFormBase
   *   A new instance of this class.
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager')->getStorage('invoice_received_entity'),
      $container->get('date.formatter')
    );
  }

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'invoice_received_entity_revision_revert_confirm';
  }

  /**
   * Returns the question to ask the user.
   *
   * @return string
   *   The form question. The page title will be set to this value.
   */
  public function getQuestion() {
    return t('Are you sure you want to revert to the revision from %revision-date?', ['%revision-date' => $this->dateFormatter->format($this->revision->getRevisionCreationTime())]);
  }

  /**
   * Returns the route to go to if the user cancels the action.
   *
   * @return \Drupal\Core\Url
   *   A URL object.
   */
  public function getCancelUrl() {
    return new Url('entity.invoice_received_entity.version_history', ['invoice_received_entity' => $this->revision->id()]);
  }

  /**
   * Returns a caption for the button that confirms the action.
   *
   * @return string
   *   The form confirmation text.
   */
  public function getConfirmText() {
    return t('Revert');
  }

  /**
   * Returns additional text to display as a description.
   *
   * @return string
   *   The form description.
   */
  public function getDescription() {
    return '';
  }

  /**
   * Returns a caption for the button that confirms the action.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param string $customer_entity_revision
   *   All previous revisions of the customer entity.
   * @param string $langcode
   *   The form language.
   *
   * @return array
   *   The form structure.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $invoice_received_entity_revision = NULL) {
    $this->revision = $this->InvoiceReceivedEntityStorage->loadRevision($invoice_received_entity_revision);
    $form = parent::buildForm($form, $form_state);

    return $form;
  }

  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // The revision timestamp will be updated when the revision is saved. Keep
    // the original one for the confirmation message.
    $original_revision_timestamp = $this->revision->getRevisionCreationTime();

    $this->revision = $this->prepareRevertedRevision($this->revision, $form_state);
    $this->revision->revision_log = t('Copy of the revision from %date.', ['%date' => $this->dateFormatter->format($original_revision_timestamp)]);
    $this->revision->save();

    $this->logger('content')->notice('Invoice received entity: reverted %title revision %revision.', ['%title' => $this->revision->label(), '%revision' => $this->revision->getRevisionId()]);
    drupal_set_message(t('Invoice received entity %title has been reverted to the revision from %revision-date.', ['%title' => $this->revision->label(), '%revision-date' => $this->dateFormatter->format($original_revision_timestamp)]));
    $form_state->setRedirect(
      'entity.invoice_received_entity.version_history',
      ['invoice_received_entity' => $this->revision->id()]
    );
  }

  /**
   * Prepares a revision to be reverted.
   *
   * @param \Drupal\invoice_received_entity\Entity\InvoiceReceivedEntityInterface $revision
   *   The revision to be reverted.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return \Drupal\invoice_received_entity\Entity\InvoiceReceivedEntityInterface
   *   The prepared revision ready to be stored.
   */
  protected function prepareRevertedRevision(InvoiceReceivedEntityInterface $revision, FormStateInterface $form_state) {
    $revision->setNewRevision();
    $revision->isDefaultRevision(TRUE);
    $revision->setRevisionCreationTime(REQUEST_TIME);

    return $revision;
  }

}
