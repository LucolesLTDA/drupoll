<?php

namespace Drupal\drupoll\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\drupoll\Entity\DrupollVoteInterface;

/**
 * Deletes a vote.
 *
 * Deleting a vote reopens the corresponding question for the affected
 * voter (a natural consequence of DrupollVoteAccess re-evaluating on the
 * next request, not an explicit action taken here). This action defers
 * entirely to the vote entity's own access control handler.
 *
 * @Action(
 *   id = "drupoll_delete_vote_action",
 *   label = @Translation("Delete vote"),
 *   type = "drupoll_vote",
 *   confirm = TRUE
 * )
 */
class DrupollDeleteVoteAction extends ActionBase {

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    if ($entity instanceof DrupollVoteInterface) {
      $entity->delete();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, ?AccountInterface $account = NULL, $return_as_object = FALSE) {
    /** @var \Drupal\drupoll\Entity\DrupollVoteInterface $object */
    $result = $object->access('delete', $account, TRUE);
    return $return_as_object ? $result : $result->isAllowed();
  }

}