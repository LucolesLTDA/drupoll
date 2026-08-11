<?php

namespace Drupal\drupoll\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\drupoll\Entity\DrupollAnswerInterface;

/**
 * Provides route controllers for the Answer entity.
 */
class DrupollAnswerController extends ControllerBase {

  /**
   * Provides the page title for an answer's canonical route.
   *
   * @param \Drupal\drupoll\Entity\DrupollAnswerInterface $drupoll_answer
   *   The answer entity.
   *
   * @return string
   *   The page title.
   */
  public function title(DrupollAnswerInterface $drupoll_answer) {
    return $drupoll_answer->label();
  }

}