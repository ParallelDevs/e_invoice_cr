<?php

namespace Drupal\invoice_entity\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Invoice entity.
 *
 * @ingroup invoice_entity
 *
 * @ContentEntityType(
 *   id = "invoice_entity",
 *   label = @Translation("Invoice"),
 *   handlers = {
 *     "storage" = "Drupal\invoice_entity\InvoiceEntityStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\invoice_entity\InvoiceEntityListBuilder",
 *     "views_data" = "Drupal\invoice_entity\Entity\InvoiceEntityViewsData",
 *     "translation" = "Drupal\invoice_entity\InvoiceEntityTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\invoice_entity\Form\InvoiceEntityForm",
 *       "add" = "Drupal\invoice_entity\Form\InvoiceEntityForm",
 *       "edit" = "Drupal\invoice_entity\Form\InvoiceEntityForm",
 *       "delete" = "Drupal\invoice_entity\Form\InvoiceEntityDeleteForm",
 *     },
 *     "access" = "Drupal\invoice_entity\InvoiceEntityAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\invoice_entity\InvoiceEntityHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "invoice_entity",
 *   data_table = "invoice_entity_field_data",
 *   revision_table = "invoice_entity_revision",
 *   revision_data_table = "invoice_entity_field_revision",
 *   translatable = TRUE,
 *   admin_permission = "administer invoice entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "vid",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/e-invoice-cr/invoice_entity/{invoice_entity}",
 *     "add-form" = "/admin/structure/e-invoice-cr/invoice_entity/add",
 *     "edit-form" = "/admin/structure/e-invoice-cr/invoice_entity/{invoice_entity}/edit",
 *     "delete-form" = "/admin/structure/e-invoice-cr/invoice_entity/{invoice_entity}/delete",
 *     "version-history" = "/admin/structure/e-invoice-cr/invoice_entity/{invoice_entity}/revisions",
 *     "revision" = "/admin/structure/e-invoice-cr/invoice_entity/{invoice_entity}/revisions/{invoice_entity_revision}/view",
 *     "revision_revert" = "/admin/structure/e-invoice-cr/invoice_entity/{invoice_entity}/revisions/{invoice_entity_revision}/revert",
 *     "revision_delete" = "/admin/structure/e-invoice-cr/invoice_entity/{invoice_entity}/revisions/{invoice_entity_revision}/delete",
 *     "translation_revert" = "/admin/structure/e-invoice-cr/invoice_entity/{invoice_entity}/revisions/{invoice_entity_revision}/revert/{langcode}",
 *     "collection" = "/admin/structure/e-invoice-cr/invoice_entity",
 *   },
 *   field_ui_base_route = "invoice_entity.settings"
 * )
 */
class InvoiceEntity extends RevisionableContentEntityBase implements InvoiceEntityInterface {

  use EntityChangedTrait;

  /**
   * Changes the values of an invoice entity before it is created.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage_controller
   *   The entity storage object.
   * @param array $values
   *   An array of values to set, keyed by property name. If the entity type
   *   has bundles the bundle key has to be specified.
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'user_id' => \Drupal::currentUser()->id(),
    ];
  }

  /**
   * Acts on an invoice entity before the presave hook is invoked.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage object.
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);

    foreach (array_keys($this->getTranslationLanguages()) as $langcode) {
      $translation = $this->getTranslation($langcode);

      // If no owner has been set explicitly, make the anonymous user the owner.
      if (!$translation->getOwner()) {
        $translation->setOwnerId(0);
      }
    }

    if (!$this->getRevisionUser()) {
      $this->setRevisionUserId($this->getOwnerId());
    }
  }

  /**
   * Gets the Invoice name.
   *
   * @return string
   *   Name of the Invoice.
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * Sets the Invoice name.
   *
   * @param string $name
   *   The Invoice name.
   *
   * @return \Drupal\invoice_entity\Entity\InvoiceEntityInterface
   *   The called Invoice entity.
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * Gets the Invoice type.
   *
   * @return string
   *   Type of the Invoice.
   */
  public function getInvoiceType() {
    return $this->get('type_of')->value;
  }

  /**
   * Gets the Invoice creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Invoice.
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * Sets the Invoice creation timestamp.
   *
   * @param int $timestamp
   *   The Invoice creation timestamp.
   *
   * @return \Drupal\invoice_entity\Entity\InvoiceEntityInterface
   *   The called Invoice entity.
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * Returns the Invoice published status indicator.
   *
   * Unpublished Invoice are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Invoice is published.
   */
  public function isPublished() {
    return (bool) $this->getEntityKey('status');
  }

  /**
   * Sets the published status of a Invoice.
   *
   * @param bool $published
   *   TRUE to set this Invoice to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\invoice_entity\Entity\InvoiceEntityInterface
   *   The called Invoice entity.
   */
  public function setPublished($published) {
    $this->set('status', $published ? TRUE : FALSE);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Invoice entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the Invoice entity.'))
      ->setRevisionable(TRUE)
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['type_of'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Type of document'))
      ->setRevisionable(FALSE)
      ->setTranslatable(FALSE)
      ->setSettings([
        'allowed_values' => [
          'FE' => t('Electronic Bill'),
          'TE' => t('Electronic Ticket'),
          'NC' => t('Credit Note'),
          'ND' => t('Debit Note'),
        ],
      ])
      ->setRequired(TRUE)
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'options_select',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // Reference Information fields.
    $fields['ref_type_of'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Type of document'))
      ->setRevisionable(FALSE)
      ->setTranslatable(FALSE)
      ->setSettings([
        'allowed_values' => [
          'FE' => t('Electronic Bill'),
          'TE' => t('Electronic Ticket'),
          'NC' => t('Credit Note'),
          'ND' => t('Debit Note'),
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['ref_doc_key'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Document Number'))
      ->setRevisionable(FALSE)
      ->setTranslatable(FALSE)
      ->setSettings([
        'max_length' => 50,
        'min_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['ref_date'] = BaseFieldDefinition::create('datetime')
      ->setLabel(t('Date'))
      ->setSettings([
        'datetime_type' => 'date',
        'datetime_format' => 'd/m/Y',
      ])
      ->setRevisionable(FALSE)
      ->setTranslatable(FALSE)
      ->setDefaultValue('')
      ->setDisplayOptions('form', [
        'date_date_element' => 'date',
        'date_date_format' => 'd/m/Y',
        'date_time_element' => 'none',
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['ref_code'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Type of reference'))
      ->setRevisionable(FALSE)
      ->setTranslatable(FALSE)
      ->setSettings([
        'allowed_values' => [
          '01' => t('Cancel Reference Document'),
          '02' => t('Corrects text reference document'),
          '03' => t('Corrects amount'),
          '04' => t('Reference to another document'),
          '05' => t('Replaces provisional voucher for contingency'),
          '99' => t('Other'),
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['ref_reason'] = BaseFieldDefinition::create('string_long')
      ->setLabel('Reason')
      ->setRevisionable(FALSE)
      ->setTranslatable(FALSE)
      ->setSettings([
        'max_length' => 180,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Publishing status'))
      ->setDescription(t('A boolean indicating whether the Invoice is published.'))
      ->setRevisionable(TRUE)
      ->setDefaultValue(TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    $fields['revision_translation_affected'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Revision translation affected'))
      ->setDescription(t('Indicates if the last edit of a translation belongs to current revision.'))
      ->setReadOnly(TRUE)
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE);

    return $fields;
  }

  /**
   * Gets an array of placeholders for invoice entity.
   *
   * Individual entity classes may override this method to add additional
   * placeholders if desired. If so, they should be sure to replicate the
   * property caching logic.
   *
   * @param string $rel
   *   The link relationship type, for example: canonical or edit-form.
   *
   * @return array
   *   An array of URI placeholders.
   */
  protected function urlRouteParameters($rel) {
    $uri_route_parameters = parent::urlRouteParameters($rel);
    if ($rel === 'revision_revert' && $this instanceof RevisionableContentEntityBase) {
      $uri_route_parameters[$this->getEntityTypeId() . '_revision'] = $this->getRevisionId();
    }
    elseif ($rel === 'revision_delete' && $this instanceof RevisionableContentEntityBase) {
      $uri_route_parameters[$this->getEntityTypeId() . '_revision'] = $this->getRevisionId();
    }
    return $uri_route_parameters;
  }

}
