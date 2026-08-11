<?php

namespace Drupal\drupoll;

use Drupal\Core\Entity\Sql\SqlContentEntityStorageSchema;
use Drupal\Core\Entity\ContentEntityTypeInterface;

/**
 * Defines the storage schema handler for Vote entities.
 */
class DrupollVoteStorageSchema extends SqlContentEntityStorageSchema {

  /**
   * {@inheritdoc}
   */
  protected function getEntitySchema(ContentEntityTypeInterface $entity_type, $reset = FALSE) {
    $schema = parent::getEntitySchema($entity_type, $reset);

    $schema['drupoll_votes']['unique keys'] += [
      'drupoll_vote__uid_quid' => ['uid', 'quid'],
    ];
    $schema['drupoll_votes']['indexes'] += [
      'drupoll_vote__quid' => ['quid'],
      'drupoll_vote__anid' => ['anid'],
      'drupoll_vote__uid' => ['uid'],
    ];

    return $schema;
  }

}