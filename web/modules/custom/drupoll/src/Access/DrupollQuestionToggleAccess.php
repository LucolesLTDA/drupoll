<?php

namespace Drupal\drupoll\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\drupoll\Entity\DrupollQuestionInterface;

/**
 * Access checker for opening/closing voting on a question.
 */
class DrupollQuestionToggleAccess implements AccessInterface {

  /**
   * Checks access to close voting for a question.
   *
   * @param \Drupal\drupoll\Entity\DrupollQuestionInterface $drupoll_question
   *   The question entity, from the route.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The account making the request.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function closeAccess(DrupollQuestionInterface $drupoll_question, AccountInterface $account) {
    if (!$drupoll_question->isVotingOpen()) {
      return AccessResult::forbidden('Voting is already closed for this question.')->addCacheableDependency($drupoll_question);
    }

    return $this->checkOwnerOrAnyPermission($drupoll_question, $account, 'close voting for any drupoll question', 'close voting for own drupoll question');
  }

  /**
   * Checks access to open voting for a question.
   *
   * @param \Drupal\drupoll\Entity\DrupollQuestionInterface $drupoll_question
   *   The question entity, from the route.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The account making the request.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function openAccess(DrupollQuestionInterface $drupoll_question, AccountInterface $account) {
    if ($drupoll_question->isVotingOpen()) {
      return AccessResult::forbidden('Voting is already open for this question.')->addCacheableDependency($drupoll_question);
    }

    return $this->checkOwnerOrAnyPermission($drupoll_question, $account, 'open voting for any drupoll question', 'open voting for own drupoll question');
  }

  /**
   * Shared owner-or-any permission check.
   *
   * @param \Drupal\drupoll\Entity\DrupollQuestionInterface $drupoll_question
   *   The question entity.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The account making the request.
   * @param string $any_permission
   *   The "any" permission machine name.
   * @param string $own_permission
   *   The "own" permission machine name.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  protected function checkOwnerOrAnyPermission(DrupollQuestionInterface $drupoll_question, AccountInterface $account, string $any_permission, string $own_permission) {
    $any = AccessResult::allowedIfHasPermission($account, $any_permission);
    $own = AccessResult::allowedIf($account->hasPermission($own_permission) && $account->id() == $drupoll_question->getOwnerId())
      ->cachePerPermissions()
      ->cachePerUser()
      ->addCacheableDependency($drupoll_question);

    return $any->orIf($own);
  }

}