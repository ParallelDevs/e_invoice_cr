<?php

namespace Drupal\exoneration_entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Exoneration entity entities.
 *
 * @ingroup exoneration_entity
 */
class ExonerationEntityListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Exoneration entity ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\exoneration_entity\Entity\ExonerationEntity */
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.exoneration_entity.edit_form',
      ['exoneration_entity' => $entity->id()]
    );
    return $row + parent::buildRow($entity);
  }

}
