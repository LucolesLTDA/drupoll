<?php

namespace Drupal\drupoll\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\drupoll\Entity\DrupollQuestionInterface;
use Drupal\drupoll\Exception\DrupollVoteException;
use Drupal\drupoll\VoteManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the vote-casting form embedded on a question's canonical page.
 *
 * Submission happens entirely via AJAX; there is no page redirect or full
 * page reload. On success, the vote area of the canonical page (radio
 * buttons and vote button, or results, depending on the outcome) is
 * replaced in place.
 */
class DrupollVoteForm extends FormBase {

  /**
   * Constructs the form.
   *
   * @param \Drupal\drupoll\VoteManagerInterface $voteManager
   *   The vote manager service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(
    protected VoteManagerInterface $voteManager,
    protected EntityTypeManagerInterface $entityTypeManager,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('drupoll.vote_manager'),
      $container->get('entity_type.manager'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'drupoll_vote_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, ?DrupollQuestionInterface $drupoll_question = NULL) {
    if (!$drupoll_question) {
      return $form;
    }

    $form_state->set('drupoll_question', $drupoll_question);

    $wrapper_id = 'drupoll-vote-wrapper-' . $drupoll_question->id();
    $form['#prefix'] = '<div id="' . $wrapper_id . '">';
    $form['#suffix'] = '</div>';
    $form_state->set('drupoll_wrapper_id', $wrapper_id);

    $options = [];
    foreach ($drupoll_question->getAnswers() as $answer) {
      $options[$answer->id()] = $answer->label();
    }

    $options = [];
    $answers_to_render = [];
    foreach ($drupoll_question->getAnswers() as $answer) {
      $options[$answer->id()] = $answer->label();
      $answers_to_render[$answer->id()] = $answer;
    }

    $form['answer'] = [
      '#type' => 'radios',
      '#title' => $this->t('Choose an answer'),
      '#title_display' => 'invisible',
      '#options' => $options,
      '#required' => TRUE,
      '#theme' => 'form_element_radios__drupoll_answer_options',
      '#answers_to_render' => $answers_to_render,
    ];

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Vote'),
      '#ajax' => [
        'callback' => '::ajaxSubmit',
        'wrapper' => $wrapper_id,
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Intentionally left to core's required-field validation on the
    // 'answer' radios element; no additional validation is needed here,
    // since VoteManager::castVote() re-validates everything server-side
    // regardless (open status, answer ownership, duplicate vote).
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\drupoll\Entity\DrupollQuestionInterface $question */
    $question = $form_state->get('drupoll_question');
    $anid = $form_state->getValue('answer');

    /** @var \Drupal\drupoll\Entity\DrupollAnswerInterface|null $answer */
    $answer = $this->entityTypeManager->getStorage('drupoll_answer')->load($anid);

    if (!$answer) {
      $this->messenger()->addError($this->t('The selected answer could not be found.'));
      return;
    }

    try {
      $this->voteManager->castVote($question, $answer, $this->currentUser());
      $this->messenger()->addStatus($this->t('Your vote has been recorded.'));
    }
    catch (DrupollVoteException $e) {
      $this->messenger()->addError($e->getMessage());
    }

    // No $form_state->setRedirect() call: this form is only ever submitted
    // via AJAX (see ::ajaxSubmit), so a redirect target is never used.
  }

  /**
   * AJAX submit callback.
   *
   * Rebuilds the vote area of the canonical question page in place: after a
   * successful vote (or a failed attempt, such as a race-condition double
   * vote caught server-side), the wrapper is replaced with the question's
   * freshly rendered "default" view mode, which will now reflect the
   * user's updated ability to vote and the current results, per the
   * question view builder / preprocess logic.
   *
   * @param array $form
   *   The form render array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   The AJAX response replacing the vote wrapper.
   */
  public function ajaxSubmit(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();

    /** @var \Drupal\drupoll\Entity\DrupollQuestionInterface $question */
    $question = $form_state->get('drupoll_question');
    $wrapper_id = $form_state->get('drupoll_wrapper_id');

    // Re-load the question to ensure fully fresh field values (vote counts,
    // etc.) are reflected, rather than relying on the in-memory entity from
    // before the vote was cast.
    $question = $this->entityTypeManager->getStorage('drupoll_question')->load($question->id());

    $view_builder = $this->entityTypeManager->getViewBuilder('drupoll_question');
    $rebuilt = $view_builder->view($question, 'default');

    $rebuilt['#prefix'] = '<div id="' . $wrapper_id . '">';
    $rebuilt['#suffix'] = '</div>';

    $messages = ['#type' => 'status_messages'];

    $response->addCommand(new ReplaceCommand('#' . $wrapper_id, $rebuilt));
    $response->addCommand(new \Drupal\Core\Ajax\PrependCommand('#' . $wrapper_id, $messages));

    return $response;
  }

}