<?php

namespace Drupal\drupoll\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\drupoll\Entity\DrupollQuestionInterface;
use Drupal\drupoll\VoteCheckerInterface;

/**
 * Access checker for the vote submission form.
 */
class DrupollVoteAccess implements AccessInterface {

  /**
   * Constructs the access checker.
   *
   * @param \Drupal\drupoll\VoteCheckerInterface $voteChecker
   *   The vote checker service.
   */
  public function __construct(
    protected VoteCheckerInterface $voteChecker,
  ) {}

  /**
   * Checks access to the vote form for a given question.
   *
   * @param \Drupal\drupoll\Entity\DrupollQuestionInterface $drupoll_question
   *   The question entity, from the route.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The account making the request.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function access(DrupollQuestionInterface $drupoll_question, AccountInterface $account) {
    if (!$account->isAuthenticated()) {
      return AccessResult::forbidden('Only registered users may vote.')->cachePerPermissions();
    }

    if (!$drupoll_question->isVotingOpen()) {
      return AccessResult::forbidden('Voting is closed for this question.')->addCacheableDependency($drupoll_question);
    }

    if ($this->voteChecker->hasVoted($drupoll_question->id(), (int) $account->id())) {
      return AccessResult::forbidden('You have already voted on this question.')
        ->addCacheableDependency($drupoll_question)
        ->cachePerUser();
    }

    return AccessResult::allowed()
      ->addCacheableDependency($drupoll_question)
      ->cachePerUser();
  }

}