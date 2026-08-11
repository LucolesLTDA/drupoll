<?php

namespace Drupal\drupoll;

use Drupal\Core\Entity\EntityListBuilder;

/**
 * Minimal list builder for Answer entities.
 */
class DrupollAnswerListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('ID');
    $header['title'] = $this->t('Title');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(\Drupal\Core\Entity\EntityInterface $entity) {
    $row['id'] = $entity->id();
    $row['title'] = $entity->toLink();
    return $row + parent::buildRow($entity);
  }

}