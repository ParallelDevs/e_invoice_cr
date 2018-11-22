<?php

namespace Drupal\provider_entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Provider entities.
 *
 * @ingroup provider_entity
 */
class ProviderEntityListBuilder extends EntityListBuilder {

  /**
   * Builds the header row for the provider entities.
   *
   * @return array
   *   A render array structure of header strings.
   */
  public function buildHeader() {
    $header['id'] = $this->t('Provider ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * Builds a row for an entity in the provider entities.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The provider entity for this row of the list.
   *
   * @return array
   *   A render array structure of header strings.
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\provider_entity\Entity\ProviderEntity */
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.provider_entity.edit_form',
      ['provider_entity' => $entity->id()]
    );
    return $row + parent::buildRow($entity);
  }

}
