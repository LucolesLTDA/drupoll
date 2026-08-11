<?php

namespace Drupal\drupoll\Form;

use Drupal\Core\Entity\EntityDeleteForm;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a confirmation form for deleting an Answer type.
 *
 * Deletion is blocked while any Answer entity still uses this bundle, for
 * the same reason Question type deletion is blocked: existing content
 * cannot be left referencing a bundle that no longer exists.
 */
class DrupollAnswerTypeDeleteForm extends EntityDeleteForm {

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

    $count = $this->getAnswerCount();

    if ($count > 0) {
      $form['#title'] = $this->getQuestion();

      $form['description'] = [
        '#markup' => $this->formatPlural(
          $count,
          'This type is used by @count answer. You may not delete this answer type until you have removed all answers of this type.',
          'This type is used by @count answers. You may not delete this answer type until you have removed all answers of this type.'
        ),
      ];

      unset($form['actions']['submit']);
    }

    return $form;
  }

  /**
   * Counts the answers currently using this bundle.
   *
   * @return int
   *   The number of answers of this type.
   */
  protected function getAnswerCount(): int {
    return (int) $this->entityTypeManager->getStorage('drupoll_answer')
      ->getQuery()
      ->condition('type', $this->entity->id())
      ->accessCheck(FALSE)
      ->count()
      ->execute();
  }

}