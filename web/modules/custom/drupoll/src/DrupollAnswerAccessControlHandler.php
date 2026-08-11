<?php

namespace Drupal\drupoll;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Access controller for the Answer entity.
 */
class DrupollAnswerAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\drupoll\Entity\DrupollAnswerInterface $entity */
    switch ($operation) {
      case 'view':
        return AccessResult::allowed();

      case 'update':
        return AccessResult::forbidden('Answers cannot be edited after creation.');

      case 'delete':
        // Answers referenced by any existing question cannot be deleted.
        $referenced = \Drupal::service('drupoll.answer_reference_checker')->isReferenced($entity->id());
        if ($referenced) {
          return AccessResult::forbidden('This answer is still referenced by one or more questions.')->addCacheableDependency($entity);
        }
        if ($account->hasPermission('delete any drupoll answer')) {
          return AccessResult::allowed()->cachePerPermissions();
        }
        if ($account->hasPermission('delete own drupoll answer') && $account->id() == $entity->getOwnerId()) {
          return AccessResult::allowed()->cachePerPermissions()->cachePerUser();
        }
        return AccessResult::forbidden()->cachePerPermissions()->cachePerUser();
    }

    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'create drupoll answer');
  }

}