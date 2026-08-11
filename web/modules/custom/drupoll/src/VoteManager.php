<?php

namespace Drupal\drupoll;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\drupoll\Entity\DrupollAnswerInterface;
use Drupal\drupoll\Entity\DrupollQuestionInterface;
use Drupal\drupoll\Entity\DrupollVoteInterface;
use Drupal\drupoll\Exception\DrupollVoteException;
use Psr\Log\LoggerInterface;

/**
 * Manages vote casting and cascading vote deletion for Drupoll.
 */
class VoteManager implements VoteManagerInterface {

  use StringTranslationTrait;

  /**
   * Constructs the service.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\drupoll\VoteCheckerInterface $voteChecker
   *   The vote checker service.
   * @param \Psr\Log\LoggerInterface $logger
   *   The Drupoll logger channel.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $stringTranslation
   *   The string translation service.
   */
  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
    protected VoteCheckerInterface $voteChecker,
    protected LoggerInterface $logger,
    TranslationInterface $stringTranslation,
  ) {
    $this->stringTranslation = $stringTranslation;
  }

  /**
   * {@inheritdoc}
   */
  public function castVote(DrupollQuestionInterface $question, DrupollAnswerInterface $answer, AccountInterface $account): DrupollVoteInterface {
    if (!$account->isAuthenticated()) {
      throw new DrupollVoteException((string) $this->t('Only registered users may vote.'));
    }

    if (!$question->isVotingOpen()) {
      throw new DrupollVoteException((string) $this->t('Voting is closed for %question.', [
        '%question' => $question->label(),
      ]));
    }

    $valid_answer_ids = array_map(
      static fn (DrupollAnswerInterface $a) => $a->id(),
      $question->getAnswers()
    );
    if (!in_array($answer->id(), $valid_answer_ids, TRUE)) {
      throw new DrupollVoteException((string) $this->t('The selected answer does not belong to %question.', [
        '%question' => $question->label(),
      ]));
    }

    if ($this->voteChecker->hasVoted((int) $question->id(), (int) $account->id())) {
      throw new DrupollVoteException((string) $this->t('You have already voted on %question.', [
        '%question' => $question->label(),
      ]));
    }

    $vote_storage = $this->entityTypeManager->getStorage('drupoll_vote');

    /** @var \Drupal\drupoll\Entity\DrupollVoteInterface $vote */
    $vote = $vote_storage->create([
      'uid' => $account->id(),
      'quid' => $question->id(),
      'anid' => $answer->id(),
    ]);

    try {
      $vote->save();
    }
    catch (\Exception $e) {
      // Covers the case where two concurrent requests both pass the
      // hasVoted() check above before either has saved; the unique key on
      // (uid, quid) defined in DrupollVoteStorageSchema will cause the
      // second save() to fail at the database level.
      $this->logger->warning('Vote save failed for uid @uid on question @quid, likely a duplicate vote race: @message', [
        '@uid' => $account->id(),
        '@quid' => $question->id(),
        '@message' => $e->getMessage(),
      ]);

      throw new DrupollVoteException((string) $this->t('You have already voted on %question.', [
        '%question' => $question->label(),
      ]));
    }

    $this->logger->notice('User @uid voted on question @quid (answer @anid).', [
      '@uid' => $account->id(),
      '@quid' => $question->id(),
      '@anid' => $answer->id(),
    ]);

    return $vote;
  }

  /**
   * {@inheritdoc}
   */
  public function deleteVotesForQuestion(int $quid): void {
    $vote_storage = $this->entityTypeManager->getStorage('drupoll_vote');

    $vtids = $vote_storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('quid', $quid)
      ->execute();

    if (empty($vtids)) {
      return;
    }

    $votes = $vote_storage->loadMultiple($vtids);
    $vote_storage->delete($votes);

    $this->logger->notice('Deleted @count vote(s) for deleted question @quid.', [
      '@count' => count($votes),
      '@quid' => $quid,
    ]);
  }

}