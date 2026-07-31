<?php

namespace Drupal\drupoll\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityOwnerInterface;

/**
 * Provides an interface for defining Vote entities.
 */
interface VoteInterface extends ContentEntityInterface, EntityOwnerInterface {

  /**
   * Gets the referenced question ID.
   *
   * @return int
   *   The question entity ID.
   */
  public function getQuestionId();

  /**
   * Gets the referenced answer ID.
   *
   * @return int
   *   The answer entity ID.
   */
  public function getAnswerId();

  /**
   * Gets the vote creation timestamp.
   *
   * @return int
   *   The timestamp.
   */
  public function getCreatedTime();

}