<?php

namespace Drupal\drupoll\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\user\UserInterface;

/**
 * Access checker for the user "Votes" tab.
 */
class DrupollUserVotesAccess implements AccessInterface {

  /**
   * Checks access to a user's votes page.
   *
   * @param \Drupal\user\UserInterface $user
   *   The user whose votes page is being accessed.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The account making the request.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function access(UserInterface $user, AccountInterface $account) {
    if ($account->hasPermission('view any drupoll vote')) {
      return AccessResult::allowed()->cachePerPermissions();
    }

    if ($account->hasPermission('view own drupoll votes') && $account->id() == $user->id()) {
      return AccessResult::allowed()->cachePerPermissions()->cachePerUser();
    }

    return AccessResult::forbidden()->cachePerPermissions()->cachePerUser();
  }

}