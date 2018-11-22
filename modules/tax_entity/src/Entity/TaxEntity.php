<?php

namespace Drupal\tax_entity\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Tax entity.
 *
 * @ingroup tax_entity
 *
 * @ContentEntityType(
 *   id = "tax_entity",
 *   label = @Translation("Tax entity"),
 *   handlers = {
 *     "storage" = "Drupal\tax_entity\TaxEntityStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\tax_entity\TaxEntityListBuilder",
 *     "views_data" = "Drupal\tax_entity\Entity\TaxEntityViewsData",
 *     "translation" = "Drupal\tax_entity\TaxEntityTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\tax_entity\Form\TaxEntityForm",
 *       "add" = "Drupal\tax_entity\Form\TaxEntityForm",
 *       "edit" = "Drupal\tax_entity\Form\TaxEntityForm",
 *       "delete" = "Drupal\tax_entity\Form\TaxEntityDeleteForm",
 *     },
 *     "access" = "Drupal\tax_entity\TaxEntityAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\tax_entity\TaxEntityHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "tax_entity",
 *   data_table = "tax_entity_field_data",
 *   revision_table = "tax_entity_revision",
 *   revision_data_table = "tax_entity_field_revision",
 *   translatable = TRUE,
 *   admin_permission = "administer tax entities",
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
 *     "canonical" = "/admin/structure/tax_entity/{tax_entity}",
 *     "add-form" = "/admin/structure/tax_entity/add",
 *     "edit-form" = "/admin/structure/tax_entity/{tax_entity}/edit",
 *     "delete-form" = "/admin/structure/tax_entity/{tax_entity}/delete",
 *     "version-history" = "/admin/structure/tax_entity/{tax_entity}/revisions",
 *     "revision" = "/admin/structure/tax_entity/{tax_entity}/revisions/{tax_entity_revision}/view",
 *     "revision_revert" = "/admin/structure/tax_entity/{tax_entity}/revisions/{tax_entity_revision}/revert",
 *     "revision_delete" = "/admin/structure/tax_entity/{tax_entity}/revisions/{tax_entity_revision}/delete",
 *     "translation_revert" = "/admin/structure/tax_entity/{tax_entity}/revisions/{tax_entity_revision}/revert/{langcode}",
 *     "collection" = "/admin/structure/tax_entity",
 *   },
 *   field_ui_base_route = "tax_entity.settings"
 * )
 */
class TaxEntity extends RevisionableContentEntityBase implements TaxEntityInterface {

  use EntityChangedTrait;

  /**
   * Changes the values of an tax entity before it is created.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage_controller
   *   The entity storage object.
   * @param array $values
   *   An array of values to set, keyed by property name. If the customer
   *   entity has bundles the bundle key has to be specified.
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'user_id' => \Drupal::currentUser()->id(),
    ];
  }

  /**
   * Acts on an customer entity before the presave hook is invoked.
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
   * Gets the Tax entity name.
   *
   * @return string
   *   Name of the Tax entity.
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * Sets the Tax entity name.
   *
   * @param string $name
   *   The Tax entity name.
   *
   * @return \Drupal\tax_entity\Entity\TaxEntityInterface
   *   The called Tax entity.
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * Gets the Tax entity creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Tax entity.
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * Sets the Tax entity creation timestamp.
   *
   * @param int $timestamp
   *   The Tax entity creation timestamp.
   *
   * @return \Drupal\tax_entity\Entity\TaxEntityInterface
   *   The called Tax entity.
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
   * Returns the Tax entity published status indicator.
   *
   * Unpublished Tax entity are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Tax entity is published.
   */
  public function isPublished() {
    return (bool) $this->getEntityKey('status');
  }

  /**
   * Sets the published status of a Tax entity.
   *
   * @param bool $published
   *   TRUE to set this Tax entity to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\tax_entity\Entity\TaxEntityInterface
   *   The called Tax entity.
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

    $settings = \Drupal::config('e_invoice_cr.settings');

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Tax entity.'))
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
        'weight' => 20,
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
      ->setDescription(t('The name of the Tax entity.'))
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

    $fields['exoneration'] = BaseFieldDefinition::create('boolean')
      ->setLabel('Add exoneration')
      ->setDescription(t('Check if this tax has a exoneration.'))
      ->setRevisionable(TRUE)
      ->setDefaultValue(FALSE)
      ->setDisplayOptions('form', [
        'weight' => 10,
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['ex_document_type'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Document type'))
      ->setDescription(t('Type of document exoneration or authorization'))
      ->setRevisionable(TRUE)
      ->setSettings([
        'allowed_values' => [
          '01' => t('Compras autorizadas'),
          '02' => t('Ventas exentas a diplomáticos'),
          '03' => t('Orden de compra (Instituciones Públicas y otros organismos)'),
          '04' => t('Exenciones Dirección General de Hacienda'),
          '05' => t('Zonas Francas'),
          '99' => t('Otros'),
        ],
      ])
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'options_select',
        'weight' => 11,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['ex_document_number'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Document number'))
      ->setRevisionable(TRUE)
      ->setSettings([
        'max_length' => 17,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -6,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 12,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['ex_institution'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Institution'))
      ->setDescription(t('Institution that emit the exoneration.'))
      ->setRevisionable(TRUE)
      ->setSettings([
        'max_length' => 100,
        'text_processing' => 0,
      ])
      ->setDefaultValue($settings->get('name'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -6,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 13,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['ex_date'] = BaseFieldDefinition::create('datetime')
      ->setLabel(t('Date'))
      ->setRevisionable(TRUE)
      /*
      ->setSettings([
        'datetime_type' => 'date',
        'datetime_format' => 'd/m/Y',
      ])
      */
      ->setDisplayOptions('form', [
        'type' => 'datetime_default',
        'weight' => 14,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['ex_percentage'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Exoneration percentage (%)'))
      ->setSettings([
        'max_value' => 100,
      ])
      ->setRevisionable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -8,
      ])
      ->setDisplayOptions('form', [
        'type' => 'number',
        'weight' => 15,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Publishing status'))
      ->setDescription(t('A boolean indicating whether the Tax entity is published.'))
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
   * Gets an array of placeholders for this entity.
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
