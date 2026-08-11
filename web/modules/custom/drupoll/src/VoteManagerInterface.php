<?php

namespace Drupal\drupoll;

use Drupal\Core\Session\AccountInterface;
use Drupal\drupoll\Entity\DrupollAnswerInterface;
use Drupal\drupoll\Entity\DrupollQuestionInterface;
use Drupal\drupoll\Entity\DrupollVoteInterface;

/**
 * Provides an interface for casting votes and cascading vote deletions.
 */
interface VoteManagerInterface {

  /**
   * Casts a vote on behalf of a user.
   *
   * Performs the following validation before creating the vote:
   * - the account is authenticated;
   * - voting is currently open on the question;
   * - the answer belongs to the question being voted on;
   * - the account has not already voted on the question.
   *
   * @param \Drupal\drupoll\Entity\DrupollQuestionInterface $question
   *   The question being voted on.
   * @param \Drupal\drupoll\Entity\DrupollAnswerInterface $answer
   *   The chosen answer.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The voting user.
   *
   * @return \Drupal\drupoll\Entity\DrupollVoteInterface
   *   The newly created and saved vote entity.
   *
   * @throws \Drupal\drupoll\Exception\DrupollVoteException
   *   If any of the validation checks fail.
   */
  public function castVote(DrupollQuestionInterface $question, DrupollAnswerInterface $answer, AccountInterface $account): DrupollVoteInterface;

  /**
   * Deletes all votes cast on a given question.
   *
   * Intended to be called when a question is deleted, so that no orphaned
   * vote records referencing a nonexistent question remain. Answers
   * referenced by those votes are not affected.
   *
   * @param int $quid
   *   The question ID whose votes should be deleted.
   */
  public function deleteVotesForQuestion(int $quid): void;

}