<?php

namespace Drupal\drupoll\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Question entities.
 */
interface DrupollQuestionInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface, EntityPublishedInterface {

  /**
   * Checks whether voting is currently open.
   *
   * @return bool
   *   TRUE if voting is open.
   */
  public function isVotingOpen();

  /**
   * Sets the voting status.
   *
   * @param bool $open
   *   TRUE to open voting, FALSE to close it.
   *
   * @return $this
   */
  public function setVotingOpen($open);

  /**
   * Checks whether vote counts should be shown.
   *
   * @return bool
   *   TRUE if vote counts should be shown.
   */
  public function showVoteCount();

  /**
   * Gets the referenced answer entities.
   *
   * @return \Drupal\drupoll\Entity\DrupollAnswerInterface[]
   *   The answers.
   */
  public function getAnswers();

}