<?php

namespace Drupal\customer_entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Customer entities.
 *
 * @ingroup customer_entity
 */
class CustomerEntityListBuilder extends EntityListBuilder {

  /**
   * Builds the header row for the customer entities.
   *
   * @return array
   *   A render array structure of header strings.
   */
  public function buildHeader() {
    $header['id'] = $this->t('Customer ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * Builds a row for an entity in the customer entities.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The customer entity for this row of the list.
   *
   * @return array
   *   A render array structure of header strings.
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\customer_entity\Entity\CustomerEntity */
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.customer_entity.edit_form',
      ['customer_entity' => $entity->id()]
    );
    return $row + parent::buildRow($entity);
  }

}
