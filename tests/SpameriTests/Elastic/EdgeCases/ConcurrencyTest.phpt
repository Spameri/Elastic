<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\EdgeCases;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for concurrent operations and conflict handling.
 * Note: Full optimistic locking tests would require version support in Insert.
 * @testCase
 */
class ConcurrencyTest extends \SpameriTests\Elastic\AbstractTestCase
{

	protected function setUp(): void
	{
		parent::setUp();

		// Delete any existing index or alias with wildcard
		/** @var \Spameri\Elastic\ClientProvider $clientProvider */
		$clientProvider = $this->container->getByType(\Spameri\Elastic\ClientProvider::class);
		try {
			$clientProvider->client()->indices()->delete(['index' => \SpameriTests\Elastic\Config::INDEX_EDGE_CASE . '*']);
		} catch (\Throwable $e) {
			// Ignore if index doesn't exist
		}

		\usleep(100000);

		/** @var \Spameri\Elastic\Model\Indices\Create $create */
		$create = $this->container->getByType(\Spameri\Elastic\Model\Indices\Create::class);
		$create->execute(\SpameriTests\Elastic\Config::INDEX_EDGE_CASE, []);

		// Wait for index to be ready
		\usleep(100000);
	}


	public function testSequentialUpdatesToSameDocument(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);

		// Create initial entity
		$entity = new \SpameriTests\Elastic\Data\Entity\EdgeCaseEntity(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			'initial-name',
			'initial-content',
		);

		$id = $entityManager->persist($entity);
		\usleep(200000);

		// Retrieve and update multiple times
		for ($i = 1; $i <= 5; $i++) {
			$retrieved = $entityManager->find(
				$id,
				\SpameriTests\Elastic\Data\Entity\EdgeCaseEntity::class,
			);

			\Tester\Assert::notNull($retrieved);

			// Create new entity with same ID for update
			$updated = new \SpameriTests\Elastic\Data\Entity\EdgeCaseEntity(
				$retrieved->id(),
				'updated-name-' . $i,
				'updated-content-' . $i,
			);

			$entityManager->persist($updated);
			\usleep(100000);
		}

		// Verify final state
		$final = $entityManager->find(
			$id,
			\SpameriTests\Elastic\Data\Entity\EdgeCaseEntity::class,
		);

		\Tester\Assert::notNull($final);
		\Tester\Assert::same('updated-name-5', $final->name);
		\Tester\Assert::same('updated-content-5', $final->content);
	}


	public function testConcurrentCreationOfMultipleDocuments(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);

		$ids = [];
		$count = 20;

		// Create many documents rapidly
		for ($i = 0; $i < $count; $i++) {
			$entity = new \SpameriTests\Elastic\Data\Entity\EdgeCaseEntity(
				new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
				'concurrent-' . $i,
				'content-' . $i,
			);

			$ids[] = $entityManager->persist($entity);
		}

		// Wait for indexing
		\usleep(500000);

		// Verify all documents exist
		$allFound = 0;
		foreach ($ids as $index => $id) {
			$retrieved = $entityManager->find(
				$id,
				\SpameriTests\Elastic\Data\Entity\EdgeCaseEntity::class,
			);

			if ($retrieved !== null) {
				$allFound++;
				\Tester\Assert::same('concurrent-' . $index, $retrieved->name);
			}
		}

		\Tester\Assert::same($count, $allFound);
	}


	public function testRapidCreateDeleteCycle(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);

		// Create, then delete, then create again with same logical name
		for ($cycle = 0; $cycle < 3; $cycle++) {
			$entity = new \SpameriTests\Elastic\Data\Entity\EdgeCaseEntity(
				new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
				'cycle-test',
				'content-cycle-' . $cycle,
			);

			$id = $entityManager->persist($entity);
			\usleep(200000);

			// Retrieve to get entity with proper ID
			$retrieved = $entityManager->find(
				$id,
				\SpameriTests\Elastic\Data\Entity\EdgeCaseEntity::class,
			);

			if ($retrieved !== null) {
				$entityManager->remove($retrieved);
				\usleep(200000);
			}
		}

		// Final creation
		$finalEntity = new \SpameriTests\Elastic\Data\Entity\EdgeCaseEntity(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			'cycle-test-final',
			'final-content',
		);

		$finalId = $entityManager->persist($finalEntity);
		\usleep(200000);

		$final = $entityManager->find(
			$finalId,
			\SpameriTests\Elastic\Data\Entity\EdgeCaseEntity::class,
		);

		\Tester\Assert::notNull($final);
		\Tester\Assert::same('cycle-test-final', $final->name);
	}


	public function testMultipleOperationsOnDifferentDocuments(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);

		// Create batch of documents
		$entities = [];
		for ($i = 0; $i < 10; $i++) {
			$entity = new \SpameriTests\Elastic\Data\Entity\EdgeCaseEntity(
				new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
				'multi-op-' . $i,
				'content-' . $i,
			);
			$entities[$entityManager->persist($entity)] = $entity;
		}

		\usleep(300000);

		// Update odd-indexed documents, delete even-indexed
		$updatedIds = [];
		$deletedIds = [];
		$counter = 0;

		foreach ($entities as $id => $entity) {
			try {
				$retrieved = $entityManager->find(
					$id,
					\SpameriTests\Elastic\Data\Entity\EdgeCaseEntity::class,
				);
			} catch (\Spameri\Elastic\Exception\DocumentNotFound $e) {
				$counter++;
				continue;
			}

			if ($counter % 2 === 0) {
				$entityManager->remove($retrieved);
				$deletedIds[] = $id;
			} else {
				$updated = new \SpameriTests\Elastic\Data\Entity\EdgeCaseEntity(
					$retrieved->id(),
					'multi-op-updated-' . $counter,
					'updated-content-' . $counter,
				);
				$entityManager->persist($updated);
				$updatedIds[] = $id;
			}
			$counter++;
		}

		\usleep(300000);

		// Verify deletions - use a fresh Get model to bypass identity map cache
		// Get model reports a document that is not there as DocumentNotFound
		/** @var \Spameri\Elastic\Model\Get $get */
		$get = $this->container->getByType(\Spameri\Elastic\Model\Get::class);

		foreach ($deletedIds as $id) {
			\Tester\Assert::exception(
				static fn () => $get->execute(
					new \Spameri\Elastic\Entity\Property\ElasticId($id),
					\SpameriTests\Elastic\Config::INDEX_EDGE_CASE,
				),
				\Spameri\Elastic\Exception\DocumentNotFound::class,
			);
		}

		// Verify updates
		foreach ($updatedIds as $index => $id) {
			$shouldExist = $entityManager->find(
				$id,
				\SpameriTests\Elastic\Data\Entity\EdgeCaseEntity::class,
			);
			\Tester\Assert::notNull($shouldExist);
			\Tester\Assert::contains('updated', $shouldExist->name);
		}
	}


	public function testIdentityMapPreventsDuplicateInstances(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);

		$entity = new \SpameriTests\Elastic\Data\Entity\EdgeCaseEntity(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			'identity-test',
			'content',
		);

		$id = $entityManager->persist($entity);
		\usleep(200000);

		// Retrieve the same entity multiple times
		$retrieved1 = $entityManager->find(
			$id,
			\SpameriTests\Elastic\Data\Entity\EdgeCaseEntity::class,
		);

		$retrieved2 = $entityManager->find(
			$id,
			\SpameriTests\Elastic\Data\Entity\EdgeCaseEntity::class,
		);

		// Should be the same instance from Identity Map
		\Tester\Assert::same($retrieved1, $retrieved2);
	}


	public function testUpdateSameEntityTwiceWithoutRefetch(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);

		$entity = new \SpameriTests\Elastic\Data\Entity\EdgeCaseEntity(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			'double-update',
			'initial',
		);

		$id = $entityManager->persist($entity);
		\usleep(200000);

		// First update
		$updated1 = new \SpameriTests\Elastic\Data\Entity\EdgeCaseEntity(
			new \Spameri\Elastic\Entity\Property\ElasticId($id),
			'first-update',
			'first-content',
		);
		$entityManager->persist($updated1);
		\usleep(100000);

		// Second update without fetching
		$updated2 = new \SpameriTests\Elastic\Data\Entity\EdgeCaseEntity(
			new \Spameri\Elastic\Entity\Property\ElasticId($id),
			'second-update',
			'second-content',
		);
		$entityManager->persist($updated2);
		\usleep(200000);

		// Verify final state
		$final = $entityManager->find(
			$id,
			\SpameriTests\Elastic\Data\Entity\EdgeCaseEntity::class,
		);

		\Tester\Assert::notNull($final);
		\Tester\Assert::same('second-update', $final->name);
		\Tester\Assert::same('second-content', $final->content);
	}


	public function testDeleteAndRecreateSameId(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);

		// Create with specific ID
		$specificId = 'specific-id-' . \uniqid();
		$entity = new \SpameriTests\Elastic\Data\Entity\EdgeCaseEntity(
			new \Spameri\Elastic\Entity\Property\ElasticId($specificId),
			'original-name',
			'original-content',
		);

		$entityManager->persist($entity);
		\usleep(200000);

		// Delete it
		$toDelete = $entityManager->find(
			$specificId,
			\SpameriTests\Elastic\Data\Entity\EdgeCaseEntity::class,
		);
		\Tester\Assert::notNull($toDelete);
		$entityManager->remove($toDelete);
		\usleep(200000);

		// Recreate with same ID
		$recreated = new \SpameriTests\Elastic\Data\Entity\EdgeCaseEntity(
			new \Spameri\Elastic\Entity\Property\ElasticId($specificId),
			'recreated-name',
			'recreated-content',
		);

		$entityManager->persist($recreated);
		\usleep(200000);

		// Verify recreation
		$final = $entityManager->find(
			$specificId,
			\SpameriTests\Elastic\Data\Entity\EdgeCaseEntity::class,
		);

		\Tester\Assert::notNull($final);
		\Tester\Assert::same('recreated-name', $final->name);
	}


	public function testFindByDuringUpdates(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);

		// Create initial documents
		$prefix = 'findby-test-' . \uniqid() . '-';
		for ($i = 0; $i < 5; $i++) {
			$entity = new \SpameriTests\Elastic\Data\Entity\EdgeCaseEntity(
				new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
				$prefix . $i,
				'searchable content ' . $i,
			);
			$entityManager->persist($entity);
		}

		\usleep(300000);

		// Query while data is being indexed
		$query = new \Spameri\ElasticQuery\ElasticQuery();
		$query->addMustQuery(new \Spameri\ElasticQuery\Query\ElasticMatch('content', 'searchable'));

		$result = $entityManager->findBy(
			$query,
			\SpameriTests\Elastic\Data\Entity\EdgeCaseEntity::class,
		);

		// Should find at least some of the documents
		\Tester\Assert::true($result->count() > 0);
	}


	protected function tearDown(): void
	{
		/** @var \Spameri\Elastic\Model\Indices\Delete $delete */
		$delete = $this->container->getByType(\Spameri\Elastic\Model\Indices\Delete::class);

		try {
			$delete->execute(\SpameriTests\Elastic\Config::INDEX_EDGE_CASE);
		} catch (\Throwable $e) {
			// Ignore if index doesn't exist
		}
	}

}

(new ConcurrencyTest())->run();
