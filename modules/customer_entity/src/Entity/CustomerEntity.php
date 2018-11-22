<?php

namespace Drupal\customer_entity\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Customer entity.
 *
 * @ingroup customer_entity
 *
 * @ContentEntityType(
 *   id = "customer_entity",
 *   label = @Translation("Customer"),
 *   handlers = {
 *     "storage" = "Drupal\customer_entity\CustomerEntityStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\customer_entity\CustomerEntityListBuilder",
 *     "views_data" = "Drupal\customer_entity\Entity\CustomerEntityViewsData",
 *     "translation" = "Drupal\customer_entity\CustomerEntityTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\customer_entity\Form\CustomerEntityForm",
 *       "add" = "Drupal\customer_entity\Form\CustomerEntityForm",
 *       "edit" = "Drupal\customer_entity\Form\CustomerEntityForm",
 *       "delete" = "Drupal\customer_entity\Form\CustomerEntityDeleteForm",
 *     },
 *     "access" = "Drupal\customer_entity\CustomerEntityAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\customer_entity\CustomerEntityHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "customer_entity",
 *   data_table = "customer_entity_field_data",
 *   revision_table = "customer_entity_revision",
 *   revision_data_table = "customer_entity_field_revision",
 *   translatable = TRUE,
 *   admin_permission = "administer customer entities",
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
 *     "canonical" = "/admin/structure/e-invoice-cr/customer_entity/{customer_entity}",
 *     "add-form" = "/admin/structure/e-invoice-cr/customer_entity/add",
 *     "edit-form" = "/admin/structure/e-invoice-cr/customer_entity/{customer_entity}/edit",
 *     "delete-form" = "/admin/structure/e-invoice-cr/customer_entity/{customer_entity}/delete",
 *     "version-history" = "/admin/structure/e-invoice-cr/customer_entity/{customer_entity}/revisions",
 *     "revision" = "/admin/structure/e-invoice-cr/customer_entity/{customer_entity}/revisions/{customer_entity_revision}/view",
 *     "revision_revert" = "/admin/structure/e-invoice-cr/customer_entity/{customer_entity}/revisions/{customer_entity_revision}/revert",
 *     "revision_delete" = "/admin/structure/e-invoice-cr/customer_entity/{customer_entity}/revisions/{customer_entity_revision}/delete",
 *     "translation_revert" = "/admin/structure/e-invoice-cr/customer_entity/{customer_entity}/revisions/{customer_entity_revision}/revert/{langcode}",
 *     "collection" = "/admin/structure/e-invoice-cr/customer_entity",
 *   },
 *   field_ui_base_route = "customer_entity.settings"
 * )
 */
class CustomerEntity extends RevisionableContentEntityBase implements CustomerEntityInterface {

  use EntityChangedTrait;

  /**
   * Changes the values of an customer entity before it is created.
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
   * Gets the Customer name.
   *
   * @return string
   *   Name of the Customer.
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * Sets the Customer name.
   *
   * @param string $name
   *   The Customer name.
   *
   * @return \Drupal\customer_entity\Entity\CustomerEntityInterface
   *   The called Customer entity.
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * Gets the Customer creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Customer.
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * Sets the Customer creation timestamp.
   *
   * @param int $timestamp
   *   The Customer creation timestamp.
   *
   * @return \Drupal\customer_entity\Entity\CustomerEntityInterface
   *   The called Customer entity.
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
   * Returns the Customer published status indicator.
   *
   * Unpublished Customer are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Customer is published.
   */
  public function isPublished() {
    return (bool) $this->getEntityKey('status');
  }

  /**
   * Sets the published status of a Customer.
   *
   * @param bool $published
   *   TRUE to set this Customer to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\customer_entity\Entity\CustomerEntityInterface
   *   The called Customer entity.
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
      ->setDescription(t('The user ID of author of the Customer entity.'))
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
      ->setDescription(t('The name of the Customer entity.'))
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

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Publishing status'))
      ->setDescription(t('A boolean indicating whether the Customer is published.'))
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
    if ($rel === 'revision_revert' && $this instanceof RevisionableContentEntityBase) {
      $uri_route_parameters[$this->getEntityTypeId() . '_revision'] = $this->getRevisionId();
    }
    elseif ($rel === 'revision_delete' && $this instanceof RevisionableContentEntityBase) {
      $uri_route_parameters[$this->getEntityTypeId() . '_revision'] = $this->getRevisionId();
    }
    return $uri_route_parameters;
  }

}
