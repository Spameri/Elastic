<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\EdgeCases;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for documents with large content and deep nesting.
 * @testCase
 */
class LargeDocumentTest extends \SpameriTests\Elastic\AbstractTestCase
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


	public function testPersistLargeTextField(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);

		// Generate a large text content (1 MB)
		$largeContent = \str_repeat('Lorem ipsum dolor sit amet, consectetur adipiscing elit. ', 20000);

		$entity = new \SpameriTests\Elastic\Data\Entity\EdgeCaseEntity(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			'large-document',
			$largeContent,
		);

		$id = $entityManager->persist($entity);

		\Tester\Assert::notSame('', $id);

		// Wait for ES to index
		\usleep(200000);

		// Retrieve and verify
		$retrieved = $entityManager->find(
			$id,
			\SpameriTests\Elastic\Data\Entity\EdgeCaseEntity::class,
		);

		\Tester\Assert::notNull($retrieved);
		\Tester\Assert::same($largeContent, $retrieved->content);
	}


	public function testPersistMultipleLargeDocuments(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);

		$ids = [];
		$largeContent = \str_repeat('Test content data. ', 5000);

		for ($i = 0; $i < 5; $i++) {
			$entity = new \SpameriTests\Elastic\Data\Entity\EdgeCaseEntity(
				new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
				'large-document-' . $i,
				$largeContent . ' Number: ' . $i,
			);

			$ids[] = $entityManager->persist($entity);
		}

		// Wait for ES to index
		\usleep(300000);

		// Verify all documents
		foreach ($ids as $index => $id) {
			$retrieved = $entityManager->find(
				$id,
				\SpameriTests\Elastic\Data\Entity\EdgeCaseEntity::class,
			);

			\Tester\Assert::notNull($retrieved);
			\Tester\Assert::same('large-document-' . $index, $retrieved->name);
		}
	}


	public function testSearchInLargeDocument(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);

		$uniqueMarker = 'UNIQUE_SEARCH_MARKER_' . \uniqid();
		$largeContent = \str_repeat('Random text content here. ', 10000) . $uniqueMarker . \str_repeat(' More text follows. ', 10000);

		$entity = new \SpameriTests\Elastic\Data\Entity\EdgeCaseEntity(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			'searchable-large-doc',
			$largeContent,
		);

		$entityManager->persist($entity);

		// Wait for ES to index
		\usleep(300000);

		// Search for the unique marker
		$query = new \Spameri\ElasticQuery\ElasticQuery();
		$query->addMustQuery(new \Spameri\ElasticQuery\Query\ElasticMatch('content', $uniqueMarker));

		$result = $entityManager->findBy(
			$query,
			\SpameriTests\Elastic\Data\Entity\EdgeCaseEntity::class,
		);

		\Tester\Assert::same(1, $result->count());
		\Tester\Assert::contains($uniqueMarker, $result->first()->content);
	}


	public function testLargeDocumentWithMaxDescriptionLength(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);

		// Test with description field containing long text
		$longDescription = \str_repeat('Description text. ', 10000);

		$entity = new \SpameriTests\Elastic\Data\Entity\EdgeCaseEntity(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			'entity-with-long-description',
			'Main content',
			$longDescription,
		);

		$id = $entityManager->persist($entity);

		// Wait for ES to index
		\usleep(200000);

		$retrieved = $entityManager->find(
			$id,
			\SpameriTests\Elastic\Data\Entity\EdgeCaseEntity::class,
		);

		\Tester\Assert::notNull($retrieved);
		\Tester\Assert::same($longDescription, $retrieved->description);
	}


	public function testBulkPersistManyDocuments(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);

		// Persist 100 documents
		$count = 100;
		$ids = [];

		for ($i = 0; $i < $count; $i++) {
			$entity = new \SpameriTests\Elastic\Data\Entity\EdgeCaseEntity(
				new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
				'bulk-doc-' . $i,
				'Content for document ' . $i,
			);

			$ids[] = $entityManager->persist($entity);
		}

		// Wait for ES to index all
		\usleep(500000);

		// Verify count
		$result = $entityManager->findAll(
			\SpameriTests\Elastic\Data\Entity\EdgeCaseEntity::class,
			$count + 10, // Limit higher than count
		);

		\Tester\Assert::true($result->count() >= $count);
	}


	public function testVeryLongFieldName(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);

		// Test with very long name field
		$longName = \str_repeat('a', 500);

		$entity = new \SpameriTests\Elastic\Data\Entity\EdgeCaseEntity(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			$longName,
			'content',
		);

		$id = $entityManager->persist($entity);

		// Wait for ES to index
		\usleep(200000);

		$retrieved = $entityManager->find(
			$id,
			\SpameriTests\Elastic\Data\Entity\EdgeCaseEntity::class,
		);

		\Tester\Assert::notNull($retrieved);
		\Tester\Assert::same($longName, $retrieved->name);
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

(new LargeDocumentTest())->run();
