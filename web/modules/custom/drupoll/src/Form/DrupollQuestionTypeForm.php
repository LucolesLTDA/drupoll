<?php

namespace Drupal\drupoll\Form;

use Drupal\Core\Entity\BundleEntityFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form handler for adding/editing Question type config entities.
 */
class DrupollQuestionTypeForm extends BundleEntityFormBase {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->entityTypeManager = $container->get('entity_type.manager');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    /** @var \Drupal\drupoll\Entity\DrupollQuestionType $type */
    $type = $this->entity;

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#default_value' => $type->label(),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $type->id(),
      '#machine_name' => [
        'exists' => [$this->entityTypeManager->getStorage('drupoll_question_type'), 'load'],
      ],
      '#disabled' => !$type->isNew(),
    ];

    $form['description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Description'),
      '#default_value' => $type->getDescription(),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $status = parent::save($form, $form_state);

    $message_args = ['%label' => $this->entity->label()];
    $message = $status == SAVED_NEW
      ? $this->t('Created new question type %label.', $message_args)
      : $this->t('Updated question type %label.', $message_args);
    $this->messenger()->addStatus($message);

    $form_state->setRedirectUrl($this->entity->toUrl('collection'));

    return $status;
  }

}