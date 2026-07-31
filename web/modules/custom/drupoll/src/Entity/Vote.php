<?php

namespace Drupal\drupoll\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\user\EntityOwnerTrait;

/**
 * Defines the Vote entity.
 *
 * Votes are immutable once cast: no edit form or route exists.
 *
 * @ContentEntityType(
 *   id = "vote",
 *   label = @Translation("Vote"),
 *   label_collection = @Translation("Votes"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\drupoll\VoteListBuilder",
 *     "access" = "Drupal\drupoll\Access\VoteAccessControlHandler",
 *     "form" = {
 *       "delete" = "Drupal\drupoll\Form\VoteDeleteForm",
 *     },
 *   },
 *   base_table = "drupoll_vote",
 *   admin_permission = "delete any vote",
 *   entity_keys = {
 *     "id" = "vtid",
 *     "uuid" = "uuid",
 *     "uid" = "uid",
 *   },
 *   links = {
 *     "canonical" = "/vote/{vote}",
 *     "delete-form" = "/vote/{vote}/delete",
 *     "collection" = "/admin/content/votes",
 *   },
 * )
 */
class Vote extends ContentEntityBase implements VoteInterface {

  use EntityOwnerTrait;

  /**
   * {@inheritdoc}
   */
  public function getQuestionId() {
    return $this->get('question')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function getAnswerId() {
    return $this->get('answer')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields += static::ownerBaseFieldDefinitions($entity_type);

    $fields['uid']
      ->setLabel(t('Voter'))
      ->setDescription(t('The user who cast this vote.'))
      ->setRequired(TRUE);

    $fields['question'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Question'))
      ->setDescription(t('The question this vote applies to.'))
      ->setSetting('target_type', 'question')
      ->setRequired(TRUE);

    $fields['answer'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Answer'))
      ->setDescription(t('The answer chosen by the voter. Cannot be changed after creation.'))
      ->setSetting('target_type', 'answer')
      ->setRequired(TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Voted on'))
      ->setDescription(t('The time the vote was cast.'));

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

    // Enforce immutability: once a vote exists, its answer reference
    // can never change, regardless of how preSave is invoked.
    if (!$this->isNew()) {
      $original = $storage->loadUnchanged($this->id());
      if ($original && $original->getAnswerId() != $this->getAnswerId()) {
        $this->set('answer', $original->getAnswerId());
      }
    }
  }

}