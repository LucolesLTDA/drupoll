<?php

namespace Drupal\drupoll;

use Drupal\Core\Entity\EntityListBuilder;

/**
 * Minimal list builder for Question entities.
 *
 * The actual admin listing at /admin/content/questions is provided by a
 * Views display; this class exists to satisfy entity handler requirements.
 */
class DrupollQuestionListBuilder extends EntityListBuilder {

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