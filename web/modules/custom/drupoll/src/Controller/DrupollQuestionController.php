<?php

namespace Drupal\drupoll\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\drupoll\Entity\DrupollQuestionInterface;

/**
 * Provides route controllers for the Question entity.
 */
class DrupollQuestionController extends ControllerBase {

  /**
   * Provides the page title for a question's canonical route.
   *
   * @param \Drupal\drupoll\Entity\DrupollQuestionInterface $drupoll_question
   *   The question entity.
   *
   * @return string
   *   The page title.
   */
  public function title(DrupollQuestionInterface $drupoll_question) {
    return $drupoll_question->label();
  }

}