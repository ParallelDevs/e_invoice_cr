<?php

namespace Drupal\tax_entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Tax entity entities.
 *
 * @ingroup tax_entity
 */
class TaxEntityListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Tax entity ID');
    $header['name'] = $this->t('Name');
    $header['percentage'] = $this->t('Percentage (%)');
    $header['ex_percentage'] = $this->t('Exoneration (%)');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\tax_entity\Entity\TaxEntity */
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.tax_entity.edit_form',
      ['tax_entity' => $entity->id()]
    );
    $row['percentage'] = $entity->get('field_tax_percentage')->value . '%';
    $row['ex_exoneration'] = !empty($entity->get('ex_percentage')->value) ?
      $entity->get('ex_percentage')->value . '%' : 'N/A';

    return $row + parent::buildRow($entity);
  }

}
