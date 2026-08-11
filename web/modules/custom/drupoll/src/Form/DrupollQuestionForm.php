<?php

namespace Drupal\drupoll\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the Question add form.
 *
 * Questions cannot be edited after creation, so this form is only ever used
 * in "add" mode; no edit route or form operation is registered for it.
 */
class DrupollQuestionForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    $answers = $form_state->getValue('answers');
    $answer_count = is_array($answers) ? count(array_filter($answers, static fn ($item) => !empty($item['target_id']) || !empty($item['entity']))) : 0;

    if ($answer_count < 2) {
      $form_state->setErrorByName('answers', $this->t('A question must have at least 2 answers.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $result = parent::save($form, $form_state);

    $this->messenger()->addStatus($this->t('Question %title has been created.', [
      '%title' => $this->entity->label(),
    ]));

    $form_state->setRedirect('entity.drupoll_question.canonical', [
      'drupoll_question' => $this->entity->id(),
    ]);

    return $result;
  }

}