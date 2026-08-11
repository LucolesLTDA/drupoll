<?php

namespace Drupal\drupoll;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Access controller for the Vote entity.
 */
class DrupollVoteAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\drupoll\Entity\DrupollVoteInterface $entity */
    switch ($operation) {
      case 'view':
        if ($account->hasPermission('view any drupoll vote')) {
          return AccessResult::allowed()->cachePerPermissions();
        }
        if ($account->hasPermission('view own drupoll votes') && $account->id() == $entity->getOwnerId()) {
          return AccessResult::allowed()->cachePerPermissions()->cachePerUser();
        }
        return AccessResult::forbidden()->cachePerPermissions()->cachePerUser();

      case 'update':
        return AccessResult::forbidden('Votes cannot be edited.');

      case 'delete':
        if ($account->hasPermission('delete any drupoll vote')) {
          return AccessResult::allowed()->cachePerPermissions();
        }
        if ($account->hasPermission('delete own drupoll vote') && $account->id() == $entity->getOwnerId()) {
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
    // Voting is handled via the dedicated DrupollVoteAccess custom access
    // checker on the vote form route, not through entity create access.
    return AccessResult::allowedIf($account->isAuthenticated());
  }

}