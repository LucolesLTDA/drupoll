<?php

namespace Drupal\drupoll\Plugin\Action;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Action\ActionBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\drupoll\Entity\DrupollQuestionInterface;

/**
 * Opens (reopens) voting on a question.
 *
 * @Action(
 *   id = "drupoll_open_question_action",
 *   label = @Translation("Open voting"),
 *   type = "drupoll_question"
 * )
 */
class DrupollOpenQuestionAction extends ActionBase {

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    if ($entity instanceof DrupollQuestionInterface && !$entity->isVotingOpen()) {
      $entity->setVotingOpen(TRUE)->save();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, ?AccountInterface $account = NULL, $return_as_object = FALSE) {
    /** @var \Drupal\drupoll\Entity\DrupollQuestionInterface $object */
    $account = $account ?: \Drupal::currentUser();

    if ($object->isVotingOpen()) {
      $result = AccessResult::forbidden('Voting is already open for this question.')->addCacheableDependency($object);
      return $return_as_object ? $result : $result->isAllowed();
    }

    $result = drupoll_question_own_or_any_access($object, $account, 'open voting');
    return $return_as_object ? $result : $result->isAllowed();
  }

}