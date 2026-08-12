<?php

namespace Drupal\drupoll\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\user\UserInterface;

/**
 * Defines the Answer entity.
 *
 * @ContentEntityType(
 *   id = "drupoll_answer",
 *   label = @Translation("Answer"),
 *   label_collection = @Translation("Answers"),
 *   label_singular = @Translation("answer"),
 *   label_plural = @Translation("answers"),
 *   label_count = @PluralTranslation(
 *     singular = "@count answer",
 *     plural = "@count answers",
 *   ),
 *   bundle_label = @Translation("Answer type"),
 *   handlers = {
 *     "storage_schema" = "Drupal\drupoll\DrupollAnswerStorageSchema",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\drupoll\DrupollAnswerListBuilder",
 *     "access" = "Drupal\drupoll\DrupollAnswerAccessControlHandler",
 *     "form" = {
 *       "add" = "Drupal\drupoll\Form\DrupollAnswerForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *       "inline" = "Drupal\drupoll\Form\DrupollAnswerInlineForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *     "views_data" = "Drupal\views\EntityViewsData",
 *   },
 *   base_table = "drupoll_answers",
 *   admin_permission = "administer drupoll answer fields",
 *   entity_keys = {
 *     "id" = "anid",
 *     "bundle" = "type",
 *     "label" = "title",
 *     "uuid" = "uuid",
 *   },
 *   bundle_entity_type = "drupoll_answer_type",
 *   field_ui_base_route = "entity.drupoll_answer_type.edit_form",
 *   links = {
 *     "canonical" = "/answer/{drupoll_answer}",
 *     "add-form" = "/answer/add",
 *     "delete-form" = "/answer/{drupoll_answer}/delete",
 *     "collection" = "/admin/content/answers",
 *   },
 * )
 */
class DrupollAnswer extends ContentEntityBase implements DrupollAnswerInterface {

  use EntityChangedTrait;

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
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['anid']->setLabel(t('Answer ID'));
    $fields['uuid']->setLabel(t('UUID'));

    $fields['type'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Type'))
      ->setSetting('target_type', 'drupoll_answer_type')
      ->setRequired(TRUE)
      ->setReadOnly(TRUE);

    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Title'))
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
      ->setLabel(t('Author'))
      ->setSetting('target_type', 'user')
      ->setDefaultValueCallback(static::class . '::getDefaultEntityOwner')
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDisplayConfigurable('view', TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'));

    return $fields;
  }

  /**
   * Default value callback for 'uid' base field.
   */
  public static function getDefaultEntityOwner() {
    return \Drupal::currentUser()->id();
  }

}