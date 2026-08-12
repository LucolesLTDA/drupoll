<?php

namespace Drupal\drupoll\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the Drupoll Answer entity.
 *
 * @ingroup drupoll
 */
class DrupollAnswerForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var \Drupal\drupoll\Entity\DrupollAnswer $entity */
    $entity = $this->entity;
    $form = parent::buildForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;
    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addStatus($this->t('Created the %label Drupoll Answer.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        $this->messenger()->addStatus($this->t('Saved the %label Drupoll Answer.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.drupoll_answer.canonical', ['drupoll_answer' => $entity->id()]);
  }

}