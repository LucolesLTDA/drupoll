<?php

namespace Drupal\drupoll\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\Core\Entity\Attribute\ConfigEntityType;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Defines the Question type config entity.
 */
#[ConfigEntityType(
  id: "drupoll_question_type",
  label: new TranslatableMarkup("Question type"),
  label_collection: new TranslatableMarkup("Question types"),
  label_singular: new TranslatableMarkup("question type"),
  label_plural: new TranslatableMarkup("question types"),
  label_count: [
    "singular" => "@count question type",
    "plural" => "@count question types",
  ],
  handlers: [
    "list_builder" => "Drupal\drupoll\DrupollQuestionTypeListBuilder",
    "form" => [
      "add" => "Drupal\drupoll\Form\DrupollQuestionTypeForm",
      "edit" => "Drupal\drupoll\Form\DrupollQuestionTypeForm",
      "delete" => "Drupal\drupoll\Form\DrupollQuestionTypeDeleteForm",
    ],
    "route_provider" => [
      "html" => "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
    ],
  ],
  config_prefix: "question_type",
  admin_permission: "administer drupoll question fields",
  bundle_of: "drupoll_question",
  entity_keys: [
    "id" => "id",
    "label" => "label",
  ],
  config_export: [
    "id",
    "label",
    "description",
  ],
  links: [
    "add-form" => "/admin/structure/drupoll-question-types/add",
    "edit-form" => "/admin/structure/drupoll-question-types/manage/{drupoll_question_type}",
    "delete-form" => "/admin/structure/drupoll-question-types/manage/{drupoll_question_type}/delete",
    "collection" => "/admin/structure/drupoll-question-types",
  ],
)]
class DrupollQuestionType extends ConfigEntityBundleBase {
  /**
   * The machine name of this question type.
   *
   * @var string
   */
  protected $id;

  /**
   * The human-readable label of this question type.
   *
   * @var string
   */
  protected $label;

  /**
   * A brief description of this question type.
   *
   * @var string
   */
  protected $description;

  /**
   * Gets the description.
   *
   * @return string
   *   The description of this question type.
   */
  public function getDescription() {
    return $this->description ?? '';
  }

  /**
   * Sets the description.
   *
   * @param string $description
   *   The new description.
   *
   * @return $this
   */
  public function setDescription($description) {
    $this->description = $description;
    return $this;
  }

}