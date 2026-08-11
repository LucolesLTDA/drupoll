<?php

namespace Drupal\drupoll\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\user\UserInterface;
use Drupal\views\Views;

/**
 * Provides the "Votes" tab on a user's account page.
 */
class DrupollUserVotesController extends ControllerBase {

  /**
   * Builds the votes listing page for a given user.
   *
   * @param \Drupal\user\UserInterface $user
   *   The user whose votes are being listed.
   *
   * @return array
   *   A render array embedding the user votes View.
   */
  public function page(UserInterface $user) {
    $view = Views::getView('drupoll_user_votes');

    if (!$view) {
      return [
        '#markup' => $this->t('The votes view could not be found.'),
      ];
    }

    $view->setDisplay('embed_1');
    $view->setArguments([$user->id()]);
    $view->execute();

    return $view->buildRenderable('embed_1', [$user->id()], FALSE);
  }

}