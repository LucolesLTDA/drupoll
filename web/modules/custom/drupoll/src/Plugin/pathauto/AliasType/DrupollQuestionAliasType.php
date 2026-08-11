<?php

namespace Drupal\drupoll\Plugin\pathauto\AliasType;

use Drupal\pathauto\Plugin\pathauto\AliasType\EntityAliasTypeBase;

/**
 * A pathauto alias type plugin for Question entities.
 *
 * @AliasType(
 *   id = "drupoll_question",
 *   label = @Translation("Question"),
 *   types = {"drupoll_question"},
 *   provider = "drupoll",
 * )
 */
class DrupollQuestionAliasType extends EntityAliasTypeBase {

}