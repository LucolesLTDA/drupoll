<?php

namespace Drupal\drupoll\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityOwnerInterface;
use Drupal\Core\Entity\RevisionLogInterface;

/**
 * Provides an interface for defining Question entities.
 */
interface QuestionInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface, RevisionLogInterface {

  /**
   * Gets the question title.
   *
   * @return string
   *   The title of the question.
   */
  public function getTitle();

  /**
   * Sets the question title.
   *
   * @param string $title
   *   The title to set.
   *
   * @return $this
   */
  public function setTitle($title);

  /**
   * Checks whether voting is closed for this question.
   *
   * @return bool
   *   TRUE if voting is closed, FALSE otherwise.
   */
  public function isVotingClosed();

  /**
   * Sets the voting closed state.
   *
   * @param bool $closed
   *   TRUE to close voting, FALSE to open it.
   *
   * @return $this
   */
  public function setVotingClosed($closed);

  /**
   * Checks whether the total vote count should be shown after voting.
   *
   * @return bool
   *   TRUE if the vote count should be shown, FALSE otherwise.
   */
  public function showVoteCount();

  /**
   * Sets whether the total vote count should be shown after voting.
   *
   * @param bool $show
   *   TRUE to show the vote count, FALSE to never show it.
   *
   * @return $this
   */
  public function setShowVoteCount($show);

}