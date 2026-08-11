<?php

namespace Drupal\drupoll\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\drupoll\Entity\DrupollQuestionInterface;

/**
 * Deletes a question.
 *
 * Deleting a question also cascades to delete its votes (handled by
 * hook_entity_predelete in drupoll.module), while leaving its answers
 * intact for reuse. This action defers entirely to the question entity's
 * own access control handler, rather than duplicating its own/any
 * permission logic here.
 *
 * @Action(
 *   id = "drupoll_delete_question_action",
 *   label = @Translation("Delete question"),
 *   type = "drupoll_question",
 *   confirm = TRUE
 * )
 */
class DrupollDeleteQuestionAction extends ActionBase {

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    if ($entity instanceof DrupollQuestionInterface) {
      $entity->delete();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, ?AccountInterface $account = NULL, $return_as_object = FALSE) {
    /** @var \Drupal\drupoll\Entity\DrupollQuestionInterface $object */
    $result = $object->access('delete', $account, TRUE);
    return $return_as_object ? $result : $result->isAllowed();
  }

}