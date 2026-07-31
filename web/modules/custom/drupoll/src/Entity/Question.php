<?php

namespace Drupal\drupoll\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\RevisionLogEntityTrait;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\user\EntityOwnerTrait;

/**
 * Defines the Question entity.
 *
 * @ContentEntityType(
 *   id = "question",
 *   label = @Translation("Question"),
 *   label_collection = @Translation("Questions"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\drupoll\QuestionListBuilder",
 *     "access" = "Drupal\drupoll\Access\QuestionAccessControlHandler",
 *     "form" = {
 *       "default" = "Drupal\drupoll\Form\QuestionForm",
 *       "add" = "Drupal\drupoll\Form\QuestionForm",
 *       "edit" = "Drupal\drupoll\Form\QuestionForm",
 *       "delete" = "Drupal\drupoll\Form\QuestionDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "drupoll_question",
 *   data_table = "drupoll_question_field_data",
 *   revision_table = "drupoll_question_revision",
 *   revision_data_table = "drupoll_question_field_revision",
 *   show_revision_ui = TRUE,
 *   translatable = TRUE,
 *   admin_permission = "administer question fields",
 *   entity_keys = {
 *     "id" = "quid",
 *     "label" = "title",
 *     "revision" = "vid",
 *     "langcode" = "langcode",
 *     "uuid" = "uuid",
 *     "uid" = "uid",
 *     "published" = "status",
 *   },
 *   revision_metadata_keys = {
 *     "revision_user" = "revision_user",
 *     "revision_created" = "revision_created",
 *     "revision_log_message" = "revision_log_message",
 *   },
 *   links = {
 *     "canonical" = "/question/{question}",
 *     "add-form" = "/admin/content/questions/add",
 *     "edit-form" = "/question/{question}/edit",
 *     "delete-form" = "/question/{question}/delete",
 *     "collection" = "/admin/content/questions",
 *   },
 *   field_ui_base_route = "entity.question.field_ui_base",
 * )
 */
class Question extends ContentEntityBase implements QuestionInterface {

  use EntityChangedTrait;
  use EntityOwnerTrait;
  use RevisionLogEntityTrait;

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->get('title')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setTitle($title) {
    $this->set('title', $title);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isVotingClosed() {
    return (bool) $this->get('voting_closed')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setVotingClosed($closed) {
    $this->set('voting_closed', (bool) $closed);
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
  public function setShowVoteCount($show) {
    $this->set('show_vote_count', (bool) $show);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields += static::ownerBaseFieldDefinitions($entity_type);
    $fields += static::revisionLogBaseFieldDefinitions($entity_type);

    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Title'))
      ->setDescription(t('The title of the question.'))
      ->setRequired(TRUE)
      ->setTranslatable(TRUE)
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

    $fields['uid']
      ->setLabel(t('Author'))
      ->setDescription(t('The user ID of the question author.'))
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'match_limit' => 10,
        ],
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['status']
      ->setLabel(t('Published'))
      ->setDefaultValue(TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['voting_closed'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Voting closed'))
      ->setDescription(t('Whether voting is currently closed for this question.'))
      ->setDefaultValue(FALSE)
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => 10,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['show_vote_count'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Show vote count after voting'))
      ->setDescription(t('Whether to display the total number of votes once a user has voted, or never show it.'))
      ->setDefaultValue(FALSE)
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => 11,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['created']
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the question was created.'))
      ->setDisplayConfigurable('view', TRUE);

    $fields['changed']
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the question was last edited.'));

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);

    if ($this->isNew() && $this->getOwnerId() === NULL) {
      $this->setOwnerId(\Drupal::currentUser()->id());
    }
  }

}