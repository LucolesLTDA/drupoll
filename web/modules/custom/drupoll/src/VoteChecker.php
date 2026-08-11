<?php

namespace Drupal\drupoll;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\drupoll\Entity\DrupollVoteInterface;

/**
 * Checks vote status and tallies results for Drupoll questions.
 */
class VoteChecker implements VoteCheckerInterface {

  /**
   * Constructs the service.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(
    protected Connection $database,
    protected EntityTypeManagerInterface $entityTypeManager,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function hasVoted(int $quid, int $uid): bool {
    $count = $this->database->select('drupoll_votes', 'v')
      ->condition('v.quid', $quid)
      ->condition('v.uid', $uid)
      ->countQuery()
      ->execute()
      ->fetchField();

    return $count > 0;
  }

  /**
   * {@inheritdoc}
   */
  public function getUserVote(int $quid, int $uid): ?DrupollVoteInterface {
    $vtid = $this->database->select('drupoll_votes', 'v')
      ->fields('v', ['vtid'])
      ->condition('v.quid', $quid)
      ->condition('v.uid', $uid)
      ->execute()
      ->fetchField();

    if (!$vtid) {
      return NULL;
    }

    /** @var \Drupal\drupoll\Entity\DrupollVoteInterface|null $vote */
    $vote = $this->entityTypeManager->getStorage('drupoll_vote')->load($vtid);

    return $vote;
  }

  /**
   * {@inheritdoc}
   */
  public function getTotalVotes(int $quid): int {
    return (int) $this->database->select('drupoll_votes', 'v')
      ->condition('v.quid', $quid)
      ->countQuery()
      ->execute()
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function getResults(int $quid): array {
    $results = [];

    /** @var \Drupal\drupoll\Entity\DrupollQuestionInterface|null $question */
    $question = $this->entityTypeManager->getStorage('drupoll_question')->load($quid);

    if (!$question) {
      return $results;
    }

    // Seed every answer belonging to the question with a zero count, so
    // answers with no votes still appear in the results.
    foreach ($question->getAnswers() as $answer) {
      $results[$answer->id()] = [
        'count' => 0,
        'percentage' => 0.0,
      ];
    }

    $query = $this->database->select('drupoll_votes', 'v')
      ->condition('v.quid', $quid);
    $query->addField('v', 'anid');
    $query->addExpression('COUNT(v.vtid)', 'vote_count');
    $query->groupBy('v.anid');
    $counts = $query->execute()->fetchAllKeyed(0, 1);

    $total = array_sum($counts);

    foreach ($counts as $anid => $count) {
      $results[$anid]['count'] = (int) $count;
      $results[$anid]['percentage'] = $total > 0
        ? round(($count / $total) * 100, 1)
        : 0.0;
    }

    return $results;
  }

}