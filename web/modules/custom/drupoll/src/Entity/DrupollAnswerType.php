<?php

namespace Drupal\drupoll\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\Core\Entity\Attribute\ConfigEntityType;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Defines the Answer type config entity.
 */
#[ConfigEntityType(
  id: "drupoll_answer_type",
  label: new TranslatableMarkup("Answer type"),
  label_collection: new TranslatableMarkup("Answer types"),
  label_singular: new TranslatableMarkup("answer type"),
  label_plural: new TranslatableMarkup("answer types"),
  label_count: [
    "singular" => "@count answer type",
    "plural" => "@count answer types",
  ],
  handlers: [
    "list_builder" => "Drupal\drupoll\DrupollAnswerTypeListBuilder",
    "form" => [
      "add" => "Drupal\drupoll\Form\DrupollAnswerTypeForm",
      "edit" => "Drupal\drupoll\Form\DrupollAnswerTypeForm",
      "delete" => "Drupal\drupoll\Form\DrupollAnswerTypeDeleteForm",
    ],
    "route_provider" => [
      "html" => "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
    ],
  ],
  config_prefix: "answer_type",
  admin_permission: "administer drupoll answer fields",
  bundle_of: "drupoll_answer",
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
    "add-form" => "/admin/structure/drupoll-answer-types/add",
    "edit-form" => "/admin/structure/drupoll-answer-types/manage/{drupoll_answer_type}",
    "delete-form" => "/admin/structure/drupoll-answer-types/manage/{drupoll_answer_type}/delete",
    "collection" => "/admin/structure/drupoll-answer-types",
  ],
)]
class DrupollAnswerType extends ConfigEntityBundleBase {

  /**
   * The machine name of this answer type.
   *
   * @var string
   */
  protected $id;

  /**
   * The human-readable label of this answer type.
   *
   * @var string
   */
  protected $label;

  /**
   * A brief description of this answer type.
   *
   * @var string
   */
  protected $description;

  /**
   * Gets the description.
   *
   * @return string
   *   The description of this answer type.
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