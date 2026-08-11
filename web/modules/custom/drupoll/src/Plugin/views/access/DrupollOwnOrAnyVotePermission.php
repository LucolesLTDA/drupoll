<?php

namespace Drupal\drupoll\Plugin\views\access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;
use Drupal\views\Plugin\views\access\AccessPluginBase;
use Symfony\Component\Routing\Route;

/**
 * Access plugin granting access if the user holds any vote-related
 * permission that implies some visibility into the votes listing: viewing
 * any vote, viewing their own votes, deleting any vote, or deleting their
 * own votes.
 *
 * @ViewsAccess(
 *   id = "drupoll_own_or_any_vote_permission",
 *   title = @Translation("Drupoll: any vote-related permission"),
 *   help = @Translation("Access will be granted to users with any permission that grants some visibility into votes (viewing or deleting, own or any).")
 * )
 */
class DrupollOwnOrAnyVotePermission extends AccessPluginBase {

  /**
   * The permissions that grant access, in order of decreasing scope.
   */
  protected const PERMISSIONS = [
    'view any drupoll vote',
    'delete any drupoll vote',
    'view own drupoll votes',
    'delete own drupoll vote',
  ];

  /**
   * {@inheritdoc}
   */
  public function access(AccountInterface $account) {
    foreach (self::PERMISSIONS as $permission) {
      if ($account->hasPermission($permission)) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function alterRouteDefinition(Route $route) {
    $route->setRequirement('_custom_access', '\Drupal\drupoll\Plugin\views\access\DrupollOwnOrAnyVotePermission::routeAccess');
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return ['user.permissions', 'user'];
  }

  /**
   * Route-level access callback mirroring ::access(), for the route system.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The account making the request.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public static function routeAccess(AccountInterface $account) {
    $instance = new static([], 'drupoll_own_or_any_vote_permission', []);
    return AccessResult::allowedIf($instance->access($account))
      ->cachePerPermissions();
  }

}