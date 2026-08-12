<?php

namespace Drupal\drupoll;

use Drupal\Core\Entity\Sql\SqlContentEntityStorageSchema;
use Drupal\Core\Entity\ContentEntityTypeInterface;

/**
 * Defines the storage schema handler for Answer entities.
 */
class DrupollAnswerStorageSchema extends SqlContentEntityStorageSchema {

  /**
   * {@inheritdoc}
   */
  protected function getEntitySchema(ContentEntityTypeInterface $entity_type, $reset = FALSE) {
    $schema = parent::getEntitySchema($entity_type, $reset);

    $schema['drupoll_answer']['indexes'] += [
      'drupoll_answer__uid' => ['uid'],
    ];

    return $schema;
  }

}