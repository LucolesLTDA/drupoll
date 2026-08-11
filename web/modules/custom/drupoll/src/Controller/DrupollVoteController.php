<?php

namespace Drupal\drupoll\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\drupoll\Entity\DrupollVoteInterface;

/**
 * Provides route controllers for the Vote entity.
 */
class DrupollVoteController extends ControllerBase {

  /**
   * Provides the page title for a vote's canonical route.
   *
   * @param \Drupal\drupoll\Entity\DrupollVoteInterface $drupoll_vote
   *   The vote entity.
   *
   * @return string
   *   The page title.
   */
  public function title(DrupollVoteInterface $drupoll_vote) {
    $question = $drupoll_vote->getQuestion();
    $owner = $drupoll_vote->getOwner();

    if ($question && $owner) {
      return $this->t("@username's (@uid) vote on @question (@quid)", [
        '@username' => $owner->getDisplayName(),
        '@uid' => $owner->id(),
        '@question' => $question->label(),
        '@quid' => $question->id(),
      ]);
    }

    return $this->t('Vote #@id', ['@id' => $drupoll_vote->id()]);
  }

}