<?php

namespace Drupal\drupoll\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityOwnerInterface;
use Drupal\Core\Entity\RevisionLogInterface;

/**
 * Provides an interface for defining Answer entities.
 */
interface AnswerInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface, RevisionLogInterface {

  /**
   * Gets the answer title.
   *
   * @return string
   *   The title of the answer.
   */
  public function getTitle();

  /**
   * Sets the answer title.
   *
   * @param string $title
   *   The title to set.
   *
   * @return $this
   */
  public function setTitle($title);

  /**
   * Checks whether this answer has been selected in at least one vote.
   *
   * @return bool
   *   TRUE if the answer has at least one vote, FALSE otherwise.
   */
  public function hasVotes();

}