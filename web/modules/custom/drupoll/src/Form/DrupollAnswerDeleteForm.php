<?php

namespace Drupal\drupoll\Form;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\ContentEntityDeleteForm;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\drupoll\AnswerReferenceChecker;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a delete confirmation form for Answer entities.
 *
 * Blocks deletion, with an explanatory table of referencing questions,
 * when the answer is still referenced by at least one question. Each row's
 * "View" and "Delete" links are only shown to users who actually have
 * access to that specific action on that specific question; otherwise the
 * corresponding cell is left blank rather than showing a link that would
 * result in access denied.
 */
class DrupollAnswerDeleteForm extends ContentEntityDeleteForm {

  /**
   * The questions currently referencing this answer, keyed by quid.
   *
   * @var \Drupal\drupoll\Entity\DrupollQuestionInterface[]
   */
  protected array $referencingQuestions = [];

  /**
   * Constructs the form.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\drupoll\AnswerReferenceChecker $referenceChecker
   *   The answer reference checker service.
   */
  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
    protected AnswerReferenceChecker $referenceChecker,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->entityTypeManager = $container->get('entity_type.manager');
    $instance->referenceChecker = $container->get('drupoll.answer_reference_checker');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $quids = $this->referenceChecker->getReferencingQuestionIds((int) $this->entity->id());

    if (empty($quids)) {
      return $form;
    }

    /** @var \Drupal\drupoll\Entity\DrupollQuestionInterface[] $questions */
    $questions = $this->entityTypeManager->getStorage('drupoll_question')->loadMultiple($quids);
    $this->referencingQuestions = $questions;
    $account = $this->currentUser();

    $rows = [];
    foreach ($questions as $question) {
      $view_access = $question->access('view', $account, TRUE);
      $delete_access = $question->access('delete', $account, TRUE);

      $view_cell = $view_access->isAllowed()
        ? Link::createFromRoute(
            $this->t('View'),
            'entity.drupoll_question.canonical',
            ['drupoll_question' => $question->id()]
          )->toRenderable()
        : ['#markup' => ''];
      CacheableMetadata::createFromObject($view_access)->applyTo($view_cell);

      $delete_cell = $delete_access->isAllowed()
        ? Link::createFromRoute(
            $this->t('Delete'),
            'entity.drupoll_question.delete_form',
            ['drupoll_question' => $question->id()]
          )->toRenderable()
        : ['#markup' => ''];
      CacheableMetadata::createFromObject($delete_access)->applyTo($delete_cell);

      $rows[] = [
        'title' => ['#markup' => $question->label()],
        'view' => $view_cell,
        'delete' => $delete_cell,
      ];
    }

    $form['drupoll_blocked_message'] = [
      '#type' => 'markup',
      '#markup' => '<p>' . $this->formatPlural(
        count($questions),
        'This answer is used by @count question and cannot be deleted.',
        'This answer is used by @count questions and cannot be deleted.'
      ) . '</p>',
      '#weight' => -10,
    ];

    $form['drupoll_referencing_questions'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Title'),
        $this->t('View'),
        $this->t('Delete'),
      ],
      '#rows' => $rows,
      '#weight' => -5,
    ];

    // Remove the standard confirm/cancel actions entirely; there is nothing
    // to confirm, since deletion cannot proceed while references exist.
    unset($form['actions']['submit']);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if (!empty($this->referencingQuestions)) {
      // Defense in depth: submission should be unreachable since the
      // submit button is removed above, but block it explicitly in case
      // the form is ever submitted programmatically or the button removal
      // is bypassed.
      $this->messenger()->addError($this->t('This answer cannot be deleted while questions still reference it.'));
      return;
    }

    parent::submitForm($form, $form_state);
  }

}