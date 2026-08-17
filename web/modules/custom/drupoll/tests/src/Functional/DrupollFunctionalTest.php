<?php

namespace Drupal\Tests\drupoll\Functional;

use Drupal\Core\Url;
use Drupal\Tests\BrowserTestBase;
use Drupal\user\Entity\Role;
use Drupal\user\RoleInterface;

/**
 * Tests the Drupoll module's core functionality.
 *
 * @group drupoll
 */
class DrupollFunctionalTest extends BrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'drupoll',
    'field_ui',
    'file',
    'image',
    'media',
    'media_library',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'olivero'; // Or 'claro' for admin theme.

  /**
   * Test users.
   *
   * @var \Drupal\user\UserInterface[]
   */
  protected $users = [];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Create roles with specific permissions.
    $this->createRoles();

    // Create test users.
    $this->createTestUsers();
  }

  /**
   * Creates test roles with specific permissions.
   */
  protected function createRoles(): void {
    // Administrator role (already exists, but we can add more permissions if needed).
    $admin_role = Role::load(RoleInterface::ADMINISTRATOR_ROLE);
    $admin_role->grantPermission('administer drupoll');
    $admin_role->grantPermission('create drupoll question');
    $admin_role->grantPermission('view vote count for any drupoll question');
    $admin_role->grantPermission('close voting for any drupoll question');
    $admin_role->grantPermission('open voting for any drupoll question');
    $admin_role->grantPermission('delete any drupoll question');
    $admin_role->grantPermission('delete any drupoll answer');
    $admin_role->grantPermission('delete any drupoll vote');
    $admin_role->save();

    // Authenticated user role (default, but add specific Drupoll permissions).
    $authenticated_role = Role::load(RoleInterface::AUTHENTICATED_ID);
    // Basic access.
    $authenticated_role->grantPermission('access content');
    $authenticated_role->grantPermission('create drupoll question');
    $authenticated_role->grantPermission('view vote count for own drupoll question');
    $authenticated_role->grantPermission('close voting for own drupoll question');
    $authenticated_role->grantPermission('open voting for own drupoll question');
    $authenticated_role->grantPermission('delete own drupoll question');
    // If they can create answers, they should be able to delete their own.
    $authenticated_role->grantPermission('delete own drupoll answer');
    $authenticated_role->grantPermission('delete own drupoll vote');
    $authenticated_role->save();

    // A "voter" role - can only vote, not create questions.
    $voter_role = $this->createRole([
      'access content',
      // Can see results for any questions they voted on.
      'view vote count for any drupoll question',
      // Can delete their own vote.
      'delete own drupoll vote',
    ], 'voter');
    $this->users['voter_role'] = $voter_role; // Store role for later use if needed.
  }

  /**
   * Creates test users.
   */
  protected function createTestUsers(): void {
    // Admin user.
    $this->users['admin'] = $this->drupalCreateUser([
      'administer drupoll',
      'create drupoll question',
      'view vote count for any drupoll question',
      'close voting for any drupoll question',
      'open voting for any drupoll question',
      'delete any drupoll question',
      'delete any drupoll answer',
      'delete any drupoll vote',
    ]);

    // Regular authenticated user (can create questions, vote, etc.).
    $this->users['owner'] = $this->drupalCreateUser([
      'access content',
      'create drupoll question',
      'view vote count for own drupoll question',
      'close voting for own drupoll question',
      'open voting for own drupoll question',
      'delete own drupoll question',
      'delete own drupoll answer',
      'delete own drupoll vote',
    ]);

    // User with only voting permissions.
    $this->users['voter'] = $this->drupalCreateUser([
      'access content',
      'view vote count for any drupoll question',
      'delete own drupoll vote',
    ]);
  }

  /**
   * Tests question creation with new and existing answers.
   */
  public function testQuestionCreation(): void {
    // Log in as a user who can create questions.
    $this->drupalLogin($this->users['owner']);

    // Test creating a question with new answers.
    $this->drupalGet(Url::fromRoute('entity.drupoll_question.add_form'));
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Create Drupoll question');

    $question_title_new = $this->randomMachineName(10);
    $answer1_title_new = $this->randomMachineName(10);
    $answer2_title_new = $this->randomMachineName(10);

    $edit = [
      'title[0][value]' => $question_title_new,
      'answers[form][inline_entity_form][entities][0][title][0][value]' => $answer1_title_new,
      'answers[form][inline_entity_form][entities][1][title][0][value]' => $answer2_title_new,
      'show_vote_count[value]' => 1, // Enable vote count display.
    ];
    $this->submitForm($edit, 'Save');

    $this->assertSession()->pageTextContains('Created the ' . $question_title_new . ' Drupoll question.');
    $this->assertSession()->pageTextContains($answer1_title_new);
    $this->assertSession()->pageTextContains($answer2_title_new);

    // Verify the question and answers exist in the database.
    $question_storage = $this->container->get('entity_type.manager')->getStorage('drupoll_question');
    $question_entities = $question_storage->loadByProperties(['title' => $question_title_new]);
    $this->assertCount(1, $question_entities, 'New question found in database.');
    /** @var \Drupal\drupoll\Entity\DrupollQuestionInterface $question */
    $question = reset($question_entities);
    $this->assertNotNull($question, 'Question entity is not null.');

    $answers = $question->getAnswers();
    $this->assertCount(2, $answers, 'Question has 2 answers.');
    $this->assertEquals($answer1_title_new, $answers[0]->label(), 'First answer title matches.');
    $this->assertEquals($answer2_title_new, $answers[1]->label(), 'Second answer title matches.');

    // Test creating a question with existing answers.
    // First, create some standalone answers.
    $answer_storage = $this->container->get('entity_type.manager')->getStorage('drupoll_answer');
    $existing_answer1_title = $this->randomMachineName(10);
    $existing_answer2_title = $this->randomMachineName(10);

    $answer1 = $answer_storage->create(['title' => $existing_answer1_title]);
    $answer1->save();
    $answer2 = $answer_storage->create(['title' => $existing_answer2_title]);
    $answer2->save();

    $this->drupalGet(Url::fromRoute('entity.drupoll_question.add_form'));
    $this->assertSession()->statusCodeEquals(200);

    $question_title_existing = $this->randomMachineName(10);
    $edit = [
      'title[0][value]' => $question_title_existing,
      // Use the autocomplete field for existing answers.
      // The format for autocomplete is "LABEL (ID)".
      'answers[form][inline_entity_form][entities][0][target_id]' => $existing_answer1_title . ' (' . $answer1->id() . ')',
      'answers[form][inline_entity_form][entities][1][target_id]' => $existing_answer2_title . ' (' . $answer2->id() . ')',
      'show_vote_count[value]' => 0, // Disable vote count display.
    ];
    $this->submitForm($edit, 'Save');

    $this->assertSession()->pageTextContains('Created the ' . $question_title_existing . ' Drupoll question.');
    $this->assertSession()->pageTextContains($existing_answer1_title);
    $this->assertSession()->pageTextContains($existing_answer2_title);

    // Verify the question and answers exist in the database.
    $question_entities_existing = $question_storage->loadByProperties(['title' => $question_title_existing]);
    $this->assertCount(1, $question_entities_existing, 'Existing question found in database.');
    /** @var \Drupal\drupoll\Entity\DrupollQuestionInterface $question_existing */
    $question_existing = reset($question_entities_existing);
    $this->assertNotNull($question_existing, 'Existing question entity is not null.');

    $answers_existing = $question_existing->getAnswers();
    $this->assertCount(2, $answers_existing, 'Existing question has 2 answers.');
    $this->assertEquals($existing_answer1_title, $answers_existing[0]->label(), 'First existing answer title matches.');
    $this->assertEquals($existing_answer2_title, $answers_existing[1]->label(), 'Second existing answer title matches.');
  }

}