<?php

namespace Drupal\drupoll\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Vote entities.
 */
interface DrupollVoteInterface extends ContentEntityInterface, EntityOwnerInterface {

  /**
   * Gets the referenced question.
   *
   * @return \Drupal\drupoll\Entity\DrupollQuestionInterface|null
   *   The question entity, or NULL.
   */
  public function getQuestion();

  /**
   * Gets the referenced answer.
   *
   * @return \Drupal\drupoll\Entity\DrupollAnswerInterface|null
   *   The answer entity, or NULL.
   */
  public function getAnswer();

}