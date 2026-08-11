<?php

namespace Drupal\drupoll\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\user\UserInterface;

/**
 * Defines the Vote entity.
 *
 * @ContentEntityType(
 *   id = "drupoll_vote",
 *   label = @Translation("Vote"),
 *   label_collection = @Translation("Votes"),
 *   label_singular = @Translation("vote"),
 *   label_plural = @Translation("votes"),
 *   label_count = @PluralTranslation(
 *     singular = "@count vote",
 *     plural = "@count votes",
 *   ),
 *   handlers = {
 *     "storage_schema" = "Drupal\drupoll\DrupollVoteStorageSchema",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\drupoll\DrupollVoteListBuilder",
 *     "access" = "Drupal\drupoll\DrupollVoteAccessControlHandler",
 *     "form" = {
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *     "views_data" = "Drupal\views\EntityViewsData",
 *   },
 *   base_table = "drupoll_votes",
 *   admin_permission = "delete any drupoll vote",
 *   entity_keys = {
 *     "id" = "vtid",
 *     "uuid" = "uuid",
 *   },
 *   links = {
 *     "canonical" = "/vote/{drupoll_vote}",
 *     "delete-form" = "/vote/{drupoll_vote}/delete",
 *     "collection" = "/admin/content/votes",
 *   },
 * )
 */
class DrupollVote extends ContentEntityBase implements DrupollVoteInterface {

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage, array &$values) {
    parent::preCreate($storage, $values);
    if (!isset($values['uid'])) {
      $values['uid'] = \Drupal::currentUser()->id();
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
  public function getQuestion() {
    return $this->get('quid')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getAnswer() {
    return $this->get('anid')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['vtid']->setLabel(t('Vote ID'));
    $fields['uuid']->setLabel(t('UUID'));

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Voter'))
      ->setSetting('target_type', 'user')
      ->setRequired(TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['quid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Question'))
      ->setSetting('target_type', 'drupoll_question')
      ->setRequired(TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['anid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Answer'))
      ->setSetting('target_type', 'drupoll_answer')
      ->setRequired(TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Cast on'))
      ->setDisplayConfigurable('view', TRUE);

    return $fields;
  }

}