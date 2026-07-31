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
 * Defines the Answer entity.
 *
 * @ContentEntityType(
 *   id = "answer",
 *   label = @Translation("Answer"),
 *   label_collection = @Translation("Answers"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\drupoll\AnswerListBuilder",
 *     "access" = "Drupal\drupoll\Access\AnswerAccessControlHandler",
 *     "form" = {
 *       "default" = "Drupal\drupoll\Form\AnswerForm",
 *       "add" = "Drupal\drupoll\Form\AnswerForm",
 *       "edit" = "Drupal\drupoll\Form\AnswerForm",
 *       "delete" = "Drupal\drupoll\Form\AnswerDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "drupoll_answer",
 *   data_table = "drupoll_answer_field_data",
 *   revision_table = "drupoll_answer_revision",
 *   revision_data_table = "drupoll_answer_field_revision",
 *   show_revision_ui = TRUE,
 *   translatable = TRUE,
 *   admin_permission = "administer answer fields",
 *   entity_keys = {
 *     "id" = "anid",
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
 *     "canonical" = "/answer/{answer}",
 *     "add-form" = "/admin/content/answers/add",
 *     "edit-form" = "/answer/{answer}/edit",
 *     "delete-form" = "/answer/{answer}/delete",
 *     "collection" = "/admin/content/answers",
 *   },
 *   field_ui_base_route = "entity.answer.field_ui_base",
 * )
 */
class Answer extends ContentEntityBase implements AnswerInterface {

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
  public function hasVotes() {
    $count = \Drupal::entityQuery('vote')
      ->condition('answer', $this->id())
      ->accessCheck(FALSE)
      ->count()
      ->execute();
    return $count > 0;
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
      ->setDescription(t('The title of the answer.'))
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
      ->setDescription(t('The user ID of the answer author.'))
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

    $fields['created']
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the answer was created.'))
      ->setDisplayConfigurable('view', TRUE);

    $fields['changed']
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the answer was last edited.'));

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