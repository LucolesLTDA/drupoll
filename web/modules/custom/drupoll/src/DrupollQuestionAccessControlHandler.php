<?php

namespace Drupal\drupoll;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Access controller for the Question entity.
 */
class DrupollQuestionAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\drupoll\Entity\DrupollQuestionInterface $entity */
    switch ($operation) {
      case 'view':
        return AccessResult::allowed();

      case 'update':
        // Editing is not implemented; only open/close toggling, which is
        // handled by dedicated forms/routes with their own access checks.
        return AccessResult::forbidden('Questions cannot be edited after creation.');

      case 'delete':
        if ($account->hasPermission('delete any drupoll question')) {
          return AccessResult::allowed()->cachePerPermissions();
        }
        if ($account->hasPermission('delete own drupoll question') && $account->id() == $entity->getOwnerId()) {
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
    return AccessResult::allowedIfHasPermission($account, 'create drupoll question');
  }

}