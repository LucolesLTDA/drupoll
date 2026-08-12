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

    $answer_count = 0;

    // Get the raw values for the 'answers' field from form_state.
    $answers_field_values = $form_state->getValue('answers');

    // Based on the dump, the active IEF items are found under the 'entities' key.
    if (isset($answers_field_values['entities']) && is_array($answers_field_values['entities'])) {
      // The count of elements in this 'entities' array directly corresponds
      // to the number of answers IEF is currently managing (new or existing).
      $answer_count = count($answers_field_values['entities']);
    }

    \Drupal::logger('drupoll')->notice('Final answer count from form_state entities: @count', ['@count' => $answer_count]);

    if ($answer_count === 0) {
      $form_state->setErrorByName('answers', $this->t('Your question is lacking answers.'));
    }
    elseif ($answer_count < 2) {
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