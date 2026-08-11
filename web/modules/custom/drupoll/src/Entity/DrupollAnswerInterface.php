<?php

namespace Drupal\drupoll\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Answer entities.
 */
interface DrupollAnswerInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

}