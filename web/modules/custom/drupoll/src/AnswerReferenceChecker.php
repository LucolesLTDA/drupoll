<?php

namespace Drupal\drupoll;

use Drupal\Core\Database\Connection;

/**
 * Checks whether an answer is referenced by any question.
 */
class AnswerReferenceChecker {

  /**
   * Constructs the service.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   */
  public function __construct(
    protected Connection $database,
  ) {}

  /**
   * Checks whether the given answer is referenced by any question.
   *
   * @param int $anid
   *   The answer ID.
   *
   * @return bool
   *   TRUE if the answer is referenced by at least one question.
   */
  public function isReferenced(int $anid): bool {
    return !empty($this->getReferencingQuestionIds($anid));
  }

  /**
   * Gets the IDs of all questions referencing the given answer.
   *
   * @param int $anid
   *   The answer ID.
   *
   * @return int[]
   *   An array of question IDs (quid), or an empty array if none reference
   *   the answer.
   */
  public function getReferencingQuestionIds(int $anid): array {
    $quids = $this->database->select('drupoll_questions__answers', 'qa')
      ->fields('qa', ['entity_id'])
      ->condition('qa.answers_target_id', $anid)
      ->execute()
      ->fetchCol();

    return array_map('intval', $quids);
  }

}