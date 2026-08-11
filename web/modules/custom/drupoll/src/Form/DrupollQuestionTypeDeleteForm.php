<?php

namespace Drupal\drupoll\Form;

use Drupal\Core\Entity\EntityDeleteForm;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a confirmation form for deleting a Question type.
 *
 * Deletion is blocked while any Question entity still uses this bundle,
 * since removing the bundle out from under existing content would leave
 * those entities referencing a nonexistent type.
 */
class DrupollQuestionTypeDeleteForm extends EntityDeleteForm {

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
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $count = $this->getQuestionCount();

    if ($count > 0) {
      $form['#title'] = $this->getQuestion();

      $form['description'] = [
        '#markup' => $this->formatPlural(
          $count,
          'This type is used by @count question. You may not delete this question type until you have removed all questions of this type.',
          'This type is used by @count questions. You may not delete this question type until you have removed all questions of this type.'
        ),
      ];

      unset($form['actions']['submit']);
    }

    return $form;
  }

  /**
   * Counts the questions currently using this bundle.
   *
   * @return int
   *   The number of questions of this type.
   */
  protected function getQuestionCount(): int {
    return (int) $this->entityTypeManager->getStorage('drupoll_question')
      ->getQuery()
      ->condition('type', $this->entity->id())
      ->accessCheck(FALSE)
      ->count()
      ->execute();
  }

}