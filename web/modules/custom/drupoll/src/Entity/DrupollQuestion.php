<?php

namespace Drupal\drupoll\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\user\UserInterface;
use Drupal\Core\Entity\Attribute\ContentEntityType; // Add this use statement
use Drupal\Core\StringTranslation\TranslatableMarkup; // Add this use statement
use Drupal\Core\StringTranslation\PluralTranslatableMarkup; // Add this use statement

/**
 * Defines the Question entity.
 */
#[ContentEntityType(
  id: "drupoll_question",
  label: new TranslatableMarkup("Question"),
  label_collection: new TranslatableMarkup("Questions"),
  label_singular: new TranslatableMarkup("question"),
  label_plural: new TranslatableMarkup("questions"),
  label_count: [
    "singular" => "@count question",
    "plural" => "@count questions",
  ],
  bundle_label: new TranslatableMarkup("Question type"),
  handlers: [
    "storage_schema" => "Drupal\drupoll\DrupollQuestionStorageSchema",
    "view_builder" => "Drupal\Core\Entity\EntityViewBuilder",
    "list_builder" => "Drupal\drupoll\DrupollQuestionListBuilder",
    "access" => "Drupal\drupoll\DrupollQuestionAccessControlHandler",
    "form" => [
      "add" => "Drupal\drupoll\Form\DrupollQuestionForm",
      "delete" => "Drupal\Core\Entity\ContentEntityDeleteForm",
    ],
    "route_provider" => [
      "html" => "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
    ],
    "views_data" => "Drupal\views\EntityViewsData",
  ],
  base_table: "drupoll_question",
  admin_permission: "administer drupoll question fields",
  entity_keys: [
    "id" => "quid",
    "bundle" => "type",
    "label" => "title",
    "uuid" => "uuid",
    "published" => "status",
  ],
  bundle_entity_type: "drupoll_question_type",
  field_ui_base_route: "entity.drupoll_question_type.edit_form",
  links: [
    "canonical" => "/question/{drupoll_question}",
    "add-page" => "/drupoll/question/add",
    "add-form" => "/question/add",
    "delete-form" => "/question/{drupoll_question}/delete",
    "collection" => "/admin/content/questions",
  ],
)]
class DrupollQuestion extends ContentEntityBase implements DrupollQuestionInterface {
  use EntityChangedTrait;
  use EntityPublishedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage, array &$values) {
    parent::preCreate($storage, $values);
    if (!isset($values['uid'])) {
      $values['uid'] = \Drupal::currentUser()->id();
    }
    // Voting open by default.
    if (!isset($values['voting_status'])) {
      $values['voting_status'] = 1;
    }
    if (!isset($values['show_vote_count'])) {
      $values['show_vote_count'] = 1;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('uid')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('uid')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('uid', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('uid', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isVotingOpen() {
    return (bool) $this->get('voting_status')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setVotingOpen($open) {
    $this->set('voting_status', $open ? 1 : 0);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function showVoteCount() {
    return (bool) $this->get('show_vote_count')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getAnswers() {
    $answers = [];
    foreach ($this->get('answers')->referencedEntities() as $answer) {
      $answers[] = $answer;
    }
    return $answers;
  }

  /**
   * Default value callback for 'uid' base field.
   */
  public static function getDefaultEntityOwner() {
    return \Drupal::currentUser()->id();
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['quid'] = BaseFieldDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Question ID'))
      ->setDescription(new TranslatableMarkup('The question ID.'))
      ->setReadOnly(TRUE);

    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Title'))
      ->setRequired(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -10,
      ])
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => -10,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Author'))
      ->setSetting('target_type', 'user')
      ->setDefaultValueCallback(static::class . '::getDefaultEntityOwner')
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['answers'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Answers'))
      ->setDescription(new TranslatableMarkup('The answers available for this question.'))
      ->setSetting('target_type', 'drupoll_answer')
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setDisplayOptions('form', [
        'type' => 'inline_entity_form_complex',
        'weight' => 0,
        'settings' => [
          'override_labels' => TRUE,
          'label_singular' => 'answer',
          'label_plural' => 'answers',
          'allow_new' => TRUE,
          'allow_existing' => TRUE,
          'match_operator' => 'CONTAINS',
          'allow_duplicate' => FALSE,
          'form_mode' => 'default',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['voting_status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(new TranslatableMarkup('Voting open'))
      ->setDescription(new TranslatableMarkup('Whether voting is currently open for this question.'))
      ->setDefaultValue(TRUE)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['show_vote_count'] = BaseFieldDefinition::create('boolean')
      ->setLabel(new TranslatableMarkup('Show vote count'))
      ->setDescription(new TranslatableMarkup('Whether to show the vote count/results to voters after they vote.'))
      ->setDefaultValue(TRUE)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(new TranslatableMarkup('Created'))
      ->setDisplayConfigurable('view', TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(new TranslatableMarkup('Changed'));

    $fields += static::publishedBaseFieldDefinitions($entity_type);

    return $fields;
  }

}