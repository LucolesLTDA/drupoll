<?php

namespace Drupal\drupoll;

use Drupal\Core\Entity\Sql\SqlContentEntityStorageSchema;
use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 * Defines the storage schema handler for Question entities.
 */
class DrupollQuestionStorageSchema extends SqlContentEntityStorageSchema {

  /**
   * {@inheritdoc}
   */
  protected function getEntitySchema(ContentEntityTypeInterface $entity_type, $reset = FALSE) {
    $schema = parent::getEntitySchema($entity_type, $reset);

    $schema['drupoll_questions']['indexes'] += [
      'drupoll_question__uid' => ['uid'],
      'drupoll_question__voting_status' => ['voting_status'],
    ];

    return $schema;
  }

}