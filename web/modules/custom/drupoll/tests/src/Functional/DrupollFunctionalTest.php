<?php

namespace Drupal\Tests\drupoll\Functional;

use Drupal\Core\Url;
use Drupal\Core\Config\ConfigInstallerInterface;
use Drupal\Core\Config\FileStorage;
use Drupal\Core\Extension\Attribute\LegacyRequirementsHook;
use Drupal\Tests\BrowserTestBase;
use Drupal\user\Entity\Role;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Tests the Drupoll module's core functionality.
 */
#[Group('drupoll')]
#[RunTestsInSeparateProcesses]
class DrupollFunctionalTest extends BrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'drupoll',
    'field_ui',
    'media',
    'media_library',
    'image',
    'file',
    'node',
    'user',
    'datetime',
    'system',
    'inline_entity_form',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'olivero';

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

    // Explicitly install the media.type.image configuration.
    // This is a workaround if BrowserTestBase isn't picking it up correctly
    // when the 'media' module is installed.
    // Remove this block if you've permanently removed the dependency on media.type.image
    // from your drupoll module's configuration.
    $config_installer = $this->container->get('config.installer');
    assert($config_installer instanceof ConfigInstallerInterface);
    $source_storage = new FileStorage(DRUPAL_ROOT . '/core/modules/media/config/install');
    $config_installer->installOptionalConfig($source_storage, ['media.type.image']);

    // Create roles with specific permissions.
    $defined_roles = $this->createRoles();

    // Create test users.
    $this->createTestUsers($defined_roles);
  }

  /**
   * Creates test roles with specific permissions.
   *
   * This method ensures that the 'administrator' and 'authenticated' roles
   * exist and have the necessary permissions, and creates a custom 'voter' role.
   *
   * @return \Drupal\user\RoleInterface[]
   *   An array of created/loaded role objects, keyed by role ID.
   */
  protected function createRoles(): array {
    // Administrator role.
    // The 'administrator' role is a default role. We load it and grant permissions.
    $admin_role_id = 'administrator';
    $admin_role = Role::load($admin_role_id);
    if (!$admin_role) {
      // Fallback: if for some reason it doesn't exist, create it.
      // $this->createRole() returns the role ID, so we load it again to get the object.
      $this->createRole([], $admin_role_id);
      $admin_role = Role::load($admin_role_id);
    }
    // Grant permissions to the administrator role.
    $admin_role->grantPermission('create drupoll question');
    $admin_role->grantPermission('view vote count for any drupoll question');
    $admin_role->grantPermission('close voting for any drupoll question');
    $admin_role->grantPermission('open voting for any drupoll question');
    $admin_role->grantPermission('delete any drupoll question');
    $admin_role->grantPermission('delete any drupoll answer');
    $admin_role->grantPermission('delete any drupoll vote');
    $admin_role->save();

    // Authenticated user role.
    // The 'authenticated' role is a default role. We load it and grant permissions.
    $authenticated_role_id = 'authenticated';
    $authenticated_role = Role::load($authenticated_role_id);
    if (!$authenticated_role) {
      // Fallback: if for some reason it doesn't exist, create it.
      $this->createRole([], $authenticated_role_id);
      $authenticated_role = Role::load($authenticated_role_id);
    }
    // Grant permissions to the authenticated user role.
    $authenticated_role->grantPermission('access content');
    $authenticated_role->grantPermission('create drupoll question');
    $authenticated_role->grantPermission('view vote count for own drupoll question');
    $authenticated_role->grantPermission('close voting for own drupoll question');
    $authenticated_role->grantPermission('open voting for own drupoll question');
    $authenticated_role->grantPermission('delete own drupoll question');
    $authenticated_role->grantPermission('delete own drupoll answer');
    $authenticated_role->grantPermission('delete own drupoll vote');
    $authenticated_role->save();

    // A "voter" role - this is a custom role, so we create it.
    // $this->createRole() creates the role and grants permissions.
    // We then load the role object to return it.
    $voter_role_id = 'voter';
    $this->createRole([
      'access content',
      'view vote count for any drupoll question',
      'delete own drupoll vote',
    ], $voter_role_id);
    // Load the object after creation.
    $voter_role = Role::load($voter_role_id);

    return [
      'administrator' => $admin_role,
      'authenticated' => $authenticated_role,
      'voter' => $voter_role,
    ];
  }

  /**
   * Creates test users and assigns them to predefined roles.
   *
   * @param \Drupal\user\RoleInterface[] $roles
   *   An array of role objects, keyed by role ID, as returned by createRoles().
   */
  protected function createTestUsers(array $roles): void {
    // Admin user.
    // Assign the 'administrator' role.
    $this->users['admin'] = $this->drupalCreateUser([], 'administrator');

    // Regular authenticated user (can create questions, vote, etc.).
    // Assign the 'authenticated' role.
    $this->users['owner'] = $this->drupalCreateUser([], 'authenticated');

    // User with only voting permissions.
    // Assign the 'voter' role.
    $this->users['voter'] = $this->drupalCreateUser([], 'voter');
  }

  /**
   * Tests question creation with new and existing answers.
   */
  #[LegacyRequirementsHook]
  public function testQuestionCreation(): void {
    // Log in as a user who can create questions.
    $this->drupalLogin($this->users['owner']);

    // Test creating a question with new answers.
    $this->drupalGet(Url::fromRoute('entity.drupoll_question.add_form'));
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Add question');

    $question_title_new = $this->randomMachineName(10);
    $answer1_title_new = $this->randomMachineName(10);
    $answer2_title_new = $this->randomMachineName(10);

    // --- Handling Inline Entity Form for NEW answers ---
    // The "Add new answer" button needs to be clicked for each new answer.
    // The exact text of the button might vary based on your IEF configuration.
    // You might need to inspect the form on your local site to get the exact button text.
    // Common button texts: "Add new [entity type]", "Add new Answer", "Add new item".
    // If your IEF widget automatically provides empty rows, you might not need these clicks.

    // Click "Add new answer" button for the first answer.
    $this->submitForm([], 'Add new answer');
    // Click "Add new answer" button for the second answer.
    $this->submitForm([], 'Add new answer');

    // Now the fields for the new answers should be present.
    $edit = [
      'title[0][value]' => $question_title_new,
      'answers[form][inline_entity_form][entities][0][title][0][value]' => $answer1_title_new,
      'answers[form][inline_entity_form][entities][1][title][0][value]' => $answer2_title_new,
      // Enable vote count display.
      'show_vote_count[value]' => 1,
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

    // --- Handling Inline Entity Form for EXISTING answers (autocomplete) ---
    // Similar to new answers, you might need to click an "Add existing" button
    // to make the autocomplete fields appear.
    // Inspect your form to find the exact text of this button.
    // Common texts: "Add existing [entity type]", "Add existing Answer", "Add existing item".
    $this->submitForm([], 'Add existing answer');
    $this->submitForm([], 'Add existing answer');

    $question_title_existing = $this->randomMachineName(10);
    $edit = [
      'title[0][value]' => $question_title_existing,
      // Use the autocomplete field for existing answers.
      // The format for autocomplete is "LABEL (ID)".
      'answers[form][inline_entity_form][entities][0][target_id]' => $existing_answer1_title . ' (' . $answer1->id() . ')',
      'answers[form][inline_entity_form][entities][1][target_id]' => $existing_answer2_title . ' (' . $answer2->id() . ')',
      // Disable vote count display.
      'show_vote_count[value]' => 0,
    ];
    $this->submitForm($edit, 'Save');

    $this->assertSession()->pageTextContains('Created the ' . $question_title_existing . ' Drupoll question.');
    $this->assertSession()->pageTextContains($existing_answer1_title);
    $this->assertSession()->pageTextContains($existing_answer2_title);

    // Verify the question and answers exist in the database.
    $question_storage = $this->container->get('entity_type.manager')->getStorage('drupoll_question');
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