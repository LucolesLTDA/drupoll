<?php

namespace Drupal\drupoll;

/**
 * Provides an interface for checking vote status and tallying results.
 */
interface VoteCheckerInterface {

  /**
   * Checks whether a given user has already voted on a given question.
   *
   * @param int $quid
   *   The question ID.
   * @param int $uid
   *   The user ID.
   *
   * @return bool
   *   TRUE if the user has already cast a vote on this question.
   */
  public function hasVoted(int $quid, int $uid): bool;

  /**
   * Gets the vote cast by a given user on a given question, if any.
   *
   * @param int $quid
   *   The question ID.
   * @param int $uid
   *   The user ID.
   *
   * @return \Drupal\drupoll\Entity\DrupollVoteInterface|null
   *   The vote entity, or NULL if the user has not voted.
   */
  public function getUserVote(int $quid, int $uid): ?\Drupal\drupoll\Entity\DrupollVoteInterface;

  /**
   * Gets the total number of votes cast on a given question.
   *
   * @param int $quid
   *   The question ID.
   *
   * @return int
   *   The total vote count.
   */
  public function getTotalVotes(int $quid): int;

  /**
   * Gets per-answer vote tallies and percentages for a given question.
   *
   * @param int $quid
   *   The question ID.
   *
   * @return array
   *   An associative array keyed by answer ID (anid), each value an array
   *   with the keys:
   *   - count: (int) the number of votes cast for that answer.
   *   - percentage: (float) the percentage of total votes, 0-100, rounded
   *     to one decimal place. 0 if there are no votes at all.
   *   Answers belonging to the question but with zero votes are included
   *   with a count of 0, so the results always reflect every answer.
   */
  public function getResults(int $quid): array;

}