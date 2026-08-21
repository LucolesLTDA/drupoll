<?php

namespace Drupal\drupoll\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\drupoll\Entity\DrupollAnswerInterface;
use Drupal\Core\Action\Attribute\Action;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Deletes an answer.
 *
 * Before deleting each answer, it's checked for any questions it might have
 * been assigned to. Answers belonging to any questions will note be deleted.
 */
#[Action(
  id: "drupoll_delete_answer_action",
  label: new TranslatableMarkup("Delete answers"),
  type: "drupoll_answer",
)]
class DrupollDeleteAnswerAction extends ActionBase {

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL, object|null $object = NULL) {
    if ($entity instanceof DrupollAnswerInterface) {
      $entity->delete();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, ?AccountInterface $account = NULL, $return_as_object = FALSE) {
    /** @var \Drupal\drupoll\Entity\DrupollAnswerInterface $object */
    $result = $object->access('delete', $account, TRUE);
    return $return_as_object ? $result : $result->isAllowed();
  }

}