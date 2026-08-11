<?php

namespace Drupal\drupoll\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\drupoll\Entity\DrupollQuestionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a confirmation form for reopening voting on a question.
 */
class DrupollQuestionOpenForm extends ConfirmFormBase {

  /**
   * The question being reopened.
   *
   * @var \Drupal\drupoll\Entity\DrupollQuestionInterface
   */
  protected DrupollQuestionInterface $question;

  /**
   * Constructs the form.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'drupoll_question_open_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, ?DrupollQuestionInterface $drupoll_question = NULL) {
    $this->question = $drupoll_question;
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Reopen voting for %title?', ['%title' => $this->question->label()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('Once reopened, registered users who have not already voted may cast a vote on this question again.');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Reopen voting');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return Url::fromRoute('entity.drupoll_question.canonical', [
      'drupoll_question' => $this->question->id(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->question->setVotingOpen(TRUE)->save();
    $this->messenger()->addStatus($this->t('Voting has been reopened for %title.', [
      '%title' => $this->question->label(),
    ]));
    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}