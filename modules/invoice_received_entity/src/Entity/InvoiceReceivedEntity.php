<?php

namespace Drupal\invoice_received_entity\Entity;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Invoice received entity entity.
 *
 * @ingroup invoice_received_entity
 *
 * @ContentEntityType(
 *   id = "invoice_received_entity",
 *   label = @Translation("Invoice received entity"),
 *   handlers = {
 *     "storage" = "Drupal\invoice_received_entity\InvoiceReceivedEntityStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\invoice_received_entity\InvoiceReceivedEntityListBuilder",
 *     "views_data" = "Drupal\invoice_received_entity\Entity\InvoiceReceivedEntityViewsData",
 *     "translation" = "Drupal\invoice_received_entity\InvoiceReceivedEntityTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\invoice_received_entity\Form\InvoiceReceivedEntityForm",
 *       "add" = "Drupal\invoice_received_entity\Form\InvoiceReceivedEntityForm",
 *       "edit" = "Drupal\invoice_received_entity\Form\InvoiceReceivedEntityForm",
 *       "delete" = "Drupal\invoice_received_entity\Form\InvoiceReceivedEntityDeleteForm",
 *     },
 *     "access" = "Drupal\invoice_received_entity\InvoiceReceivedEntityAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\invoice_received_entity\InvoiceReceivedEntityHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "invoice_received_entity",
 *   data_table = "invoice_received_entity_field_data",
 *   revision_table = "invoice_received_entity_revision",
 *   revision_data_table = "invoice_received_entity_field_revision",
 *   translatable = TRUE,
 *   admin_permission = "administer invoice received entity entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "vid",
 *     "label" = "document_key",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/invoice_received_entity/{invoice_received_entity}",
 *     "add-form" = "/admin/structure/invoice_received_entity/add",
 *     "edit-form" = "/admin/structure/invoice_received_entity/{invoice_received_entity}/edit",
 *     "delete-form" = "/admin/structure/invoice_received_entity/{invoice_received_entity}/delete",
 *     "version-history" = "/admin/structure/invoice_received_entity/{invoice_received_entity}/revisions",
 *     "revision" = "/admin/structure/invoice_received_entity/{invoice_received_entity}/revisions/{invoice_received_entity_revision}/view",
 *     "revision_revert" = "/admin/structure/invoice_received_entity/{invoice_received_entity}/revisions/{invoice_received_entity_revision}/revert",
 *     "revision_delete" = "/admin/structure/invoice_received_entity/{invoice_received_entity}/revisions/{invoice_received_entity_revision}/delete",
 *     "translation_revert" = "/admin/structure/invoice_received_entity/{invoice_received_entity}/revisions/{invoice_received_entity_revision}/revert/{langcode}",
 *     "collection" = "/admin/structure/invoice_received_entity",
 *   },
 *   field_ui_base_route = "invoice_received_entity.settings"
 * )
 */
class InvoiceReceivedEntity extends RevisionableContentEntityBase implements InvoiceReceivedEntityInterface {

  use EntityChangedTrait;

  /**
   * Changes the values of an invoice received entity before it is created.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage_controller
   *   The entity storage object.
   * @param array $values
   *   An array of values to set, keyed by property name. If the invoice
   *   received entity has bundles the bundle key has to be specified.
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'user_id' => \Drupal::currentUser()->id(),
    ];
  }

  /**
   * Gets an array of placeholders for customer entity.
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

    if ($rel === 'revision_revert' && $this instanceof RevisionableInterface) {
      $uri_route_parameters[$this->getEntityTypeId() . '_revision'] = $this->getRevisionId();
    }
    elseif ($rel === 'revision_delete' && $this instanceof RevisionableInterface) {
      $uri_route_parameters[$this->getEntityTypeId() . '_revision'] = $this->getRevisionId();
    }

    return $uri_route_parameters;
  }

  /**
   * Acts on an invoice received entity before the presave hook is invoked.
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

    // If no revision author has been set explicitly, make the
    // invoice_received_entity owner the revision author.
    if (!$this->getRevisionUser()) {
      $this->setRevisionUserId($this->getOwnerId());
    }
  }

  /**
   * Gets the Invoice received entity name.
   *
   * @return string
   *   Name of the Invoice received entity.
   */
  public function getName() {
    return $this->get('document_key')->value;
  }

  /**
   * Sets the Invoice received entity name.
   *
   * @param string $name
   *   The Invoice received entity name.
   *
   * @return \Drupal\invoice_received_entity\Entity\InvoiceReceivedEntityInterface
   *   The called Invoice received entity entity.
   */
  public function setName($name) {
    $this->set('document_key', $name);
    return $this;
  }

  /**
   * Gets the Invoice received entity creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Invoice received entity.
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * Sets the Invoice received entity creation timestamp.
   *
   * @param int $timestamp
   *   The Invoice received entity creation timestamp.
   *
   * @return \Drupal\invoice_received_entity\Entity\InvoiceReceivedEntityInterface
   *   The called Invoice received entity entity.
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
   * Returns the Invoice received entity published status indicator.
   *
   * Unpublished Invoice received entity are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Invoice received entity is published.
   */
  public function isPublished() {
    return (bool) $this->getEntityKey('status');
  }

  /**
   * Sets the published status of a Invoice received entity.
   *
   * @param bool $published
   *   TRUE to set this Invoice received entity to published, FALSE to set it
   *   to unpublished.
   *
   * @return \Drupal\invoice_received_entity\Entity\InvoiceReceivedEntityInterface
   *   The called Invoice received entity entity.
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
      ->setDescription(t('The user ID of author of the Invoice received entity entity.'))
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

    $fields['document_key'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Document Key'))
      ->setDescription(t('The document numeric key.'))
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
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Publishing status'))
      ->setDescription(t('A boolean indicating whether the Invoice received entity is published.'))
      ->setRevisionable(TRUE)
      ->setDefaultValue(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => -3,
      ]);

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
   * {@inheritdoc}
   */
  public function access($operation, AccountInterface $account = NULL, $return_as_object = FALSE) {
    $checked = FALSE;
    if ($operation === 'update') {
      $checked = !is_null($this->get('field_ir_message')->value) && $this->get('field_ir_status')->value > 1;
    }
    return $checked ? AccessResult::allowedIf(FALSE) : parent::access($operation, $account, $return_as_object);
  }

}
