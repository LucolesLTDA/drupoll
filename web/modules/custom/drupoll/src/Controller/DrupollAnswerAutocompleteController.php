<?php

namespace Drupal\drupoll\Controller;

use Drupal\Component\Utility\Html;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides autocomplete results for existing Answer entities.
 */
class DrupollAnswerAutocompleteController extends ControllerBase {

  /**
   * The maximum number of suggestions to return.
   */
  protected const LIMIT = 10;

  /**
   * Constructs the controller.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   */
  public function __construct(
    protected Connection $database,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
    );
  }

  /**
   * Handles autocomplete requests for answer titles.
   *
   * Matches on CONTAINS against the title of existing answers (i.e. rows
   * currently present in drupoll_answers; answers have no soft-delete
   * state, so "existing" simply means not yet deleted) and returns up to
   * self::LIMIT results, formatted as "Title (anid)" — the format expected
   * by Drupal's entity autocomplete parsing (EntityAutocomplete::extract
   * EntityIdFromAutocompleteInput), which IEF's autocomplete widget relies
   * on as well.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The autocomplete suggestions.
   */
  public function handleAutocomplete(Request $request) {
    $results = [];
    $input = $request->query->get('q');

    if ($input !== NULL && $input !== '') {
      $result = $this->database->select('drupoll_answers', 'a')
        ->fields('a', ['anid', 'title'])
        ->condition('a.title', '%' . $this->database->escapeLike($input) . '%', 'LIKE')
        ->range(0, self::LIMIT)
        ->orderBy('a.title', 'ASC')
        ->execute();

      foreach ($result as $record) {
        $label = Html::escape($record->title) . ' (' . $record->anid . ')';
        $results[] = [
          'value' => $label,
          'label' => $record->title,
        ];
      }
    }

    return new JsonResponse($results);
  }

}