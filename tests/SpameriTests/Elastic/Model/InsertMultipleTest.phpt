<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Model;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
class InsertMultipleTest extends \SpameriTests\Elastic\AbstractTestCase
{

	private const INDEX = 'spameri_model_insertmultiple_test';


	protected function setUp(): void
	{
		parent::setUp();

		// Delete any existing index or alias with wildcard
		/** @var \Spameri\Elastic\ClientProvider $clientProvider */
		$clientProvider = $this->container->getByType(\Spameri\Elastic\ClientProvider::class);
		try {
			$clientProvider->client()->indices()->delete(['index' => self::INDEX . '*']);
		} catch (\Throwable $e) {
			// Ignore if index doesn't exist
		}

		\usleep(100000);

		/** @var \Spameri\Elastic\Model\Indices\Create $create */
		$create = $this->container->getByType(\Spameri\Elastic\Model\Indices\Create::class);
		$create->execute(self::INDEX, [
			'mappings' => [
				'properties' => [
					'name' => ['type' => 'keyword'],
					'count' => ['type' => 'integer'],
					'price' => ['type' => 'float'],
					'active' => ['type' => 'boolean'],
				],
			],
		]);

		\usleep(100000);
	}


	public function testInsertMultipleEntities(): void
	{
		/** @var \Spameri\Elastic\Model\InsertMultiple $insertMultiple */
		$insertMultiple = $this->container->getByType(\Spameri\Elastic\Model\InsertMultiple::class);
		/** @var \Spameri\Elastic\Model\GetAllBy $getAllBy */
		$getAllBy = $this->container->getByType(\Spameri\Elastic\Model\GetAllBy::class);
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);

		$collection = new \Spameri\Elastic\Entity\Collection\ElasticEntityCollection(
			$entityManager,
			\SpameriTests\Elastic\Data\Entity\SimpleTestEntity::class,
		);

		for ($i = 1; $i <= 5; $i++) {
			$entity = new \SpameriTests\Elastic\Data\Entity\SimpleTestEntity(
				new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
				'Product ' . $i,
				$i,
				$i * 10.0,
				$i % 2 === 0,
			);
			$collection->add($entity);
		}

		$result = $insertMultiple->execute($collection, self::INDEX);

		\Tester\Assert::type(\Spameri\ElasticQuery\Response\ResultBulk::class, $result);

		// Verify all documents were inserted
		$elasticQuery = new \Spameri\ElasticQuery\ElasticQuery();
		$elasticQuery->addMustQuery(new \Spameri\ElasticQuery\Query\MatchAll());

		$searchResult = $getAllBy->execute($elasticQuery, self::INDEX);
		\Tester\Assert::same(5, $searchResult->stats()->total());
	}


	public function testInsertMultipleSingleEntity(): void
	{
		/** @var \Spameri\Elastic\Model\InsertMultiple $insertMultiple */
		$insertMultiple = $this->container->getByType(\Spameri\Elastic\Model\InsertMultiple::class);
		/** @var \Spameri\Elastic\Model\GetAllBy $getAllBy */
		$getAllBy = $this->container->getByType(\Spameri\Elastic\Model\GetAllBy::class);
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);

		$collection = new \Spameri\Elastic\Entity\Collection\ElasticEntityCollection(
			$entityManager,
			\SpameriTests\Elastic\Data\Entity\SimpleTestEntity::class,
		);

		$entity = new \SpameriTests\Elastic\Data\Entity\SimpleTestEntity(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			'Single Product',
			1,
			10.0,
			true,
		);
		$collection->add($entity);

		$result = $insertMultiple->execute($collection, self::INDEX);

		\Tester\Assert::type(\Spameri\ElasticQuery\Response\ResultBulk::class, $result);

		// Verify document was inserted
		$elasticQuery = new \Spameri\ElasticQuery\ElasticQuery();
		$elasticQuery->addMustQuery(new \Spameri\ElasticQuery\Query\MatchAll());

		$searchResult = $getAllBy->execute($elasticQuery, self::INDEX);
		\Tester\Assert::same(1, $searchResult->stats()->total());
	}


	public function testInsertMultipleLargeDataset(): void
	{
		/** @var \Spameri\Elastic\Model\InsertMultiple $insertMultiple */
		$insertMultiple = $this->container->getByType(\Spameri\Elastic\Model\InsertMultiple::class);
		/** @var \Spameri\Elastic\Model\GetAllBy $getAllBy */
		$getAllBy = $this->container->getByType(\Spameri\Elastic\Model\GetAllBy::class);
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);

		$collection = new \Spameri\Elastic\Entity\Collection\ElasticEntityCollection(
			$entityManager,
			\SpameriTests\Elastic\Data\Entity\SimpleTestEntity::class,
		);

		for ($i = 1; $i <= 100; $i++) {
			$entity = new \SpameriTests\Elastic\Data\Entity\SimpleTestEntity(
				new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
				'Product ' . $i,
				$i,
				$i * 10.0,
				$i % 2 === 0,
			);
			$collection->add($entity);
		}

		$result = $insertMultiple->execute($collection, self::INDEX);

		\Tester\Assert::type(\Spameri\ElasticQuery\Response\ResultBulk::class, $result);

		// Verify all documents were inserted
		$elasticQuery = new \Spameri\ElasticQuery\ElasticQuery();
		$elasticQuery->addMustQuery(new \Spameri\ElasticQuery\Query\MatchAll());

		$searchResult = $getAllBy->execute($elasticQuery, self::INDEX);
		\Tester\Assert::same(100, $searchResult->stats()->total());
	}


	public function testInsertMultipleRefreshesIndex(): void
	{
		/** @var \Spameri\Elastic\Model\InsertMultiple $insertMultiple */
		$insertMultiple = $this->container->getByType(\Spameri\Elastic\Model\InsertMultiple::class);
		/** @var \Spameri\Elastic\Model\GetAllBy $getAllBy */
		$getAllBy = $this->container->getByType(\Spameri\Elastic\Model\GetAllBy::class);
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);

		$collection = new \Spameri\Elastic\Entity\Collection\ElasticEntityCollection(
			$entityManager,
			\SpameriTests\Elastic\Data\Entity\SimpleTestEntity::class,
		);

		for ($i = 1; $i <= 3; $i++) {
			$entity = new \SpameriTests\Elastic\Data\Entity\SimpleTestEntity(
				new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
				'Product ' . $i,
				$i,
				$i * 10.0,
				true,
			);
			$collection->add($entity);
		}

		$insertMultiple->execute($collection, self::INDEX);

		// Documents should be immediately available without waiting
		$elasticQuery = new \Spameri\ElasticQuery\ElasticQuery();
		$elasticQuery->addMustQuery(new \Spameri\ElasticQuery\Query\MatchAll());

		$searchResult = $getAllBy->execute($elasticQuery, self::INDEX);
		\Tester\Assert::same(3, $searchResult->stats()->total());
	}


	public function testInsertMultipleWithVariousDataTypes(): void
	{
		/** @var \Spameri\Elastic\Model\InsertMultiple $insertMultiple */
		$insertMultiple = $this->container->getByType(\Spameri\Elastic\Model\InsertMultiple::class);
		/** @var \Spameri\Elastic\Model\GetAllBy $getAllBy */
		$getAllBy = $this->container->getByType(\Spameri\Elastic\Model\GetAllBy::class);
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);

		$collection = new \Spameri\Elastic\Entity\Collection\ElasticEntityCollection(
			$entityManager,
			\SpameriTests\Elastic\Data\Entity\SimpleTestEntity::class,
		);

		// Entity with true
		$entity1 = new \SpameriTests\Elastic\Data\Entity\SimpleTestEntity(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			'Active Product',
			100,
			99.99,
			true,
		);
		$collection->add($entity1);

		// Entity with false
		$entity2 = new \SpameriTests\Elastic\Data\Entity\SimpleTestEntity(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			'Inactive Product',
			0,
			0.01,
			false,
		);
		$collection->add($entity2);

		// Entity with null description
		$entity3 = new \SpameriTests\Elastic\Data\Entity\SimpleTestEntity(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			'No Description',
			50,
			25.0,
			true,
			null,
		);
		$collection->add($entity3);

		$result = $insertMultiple->execute($collection, self::INDEX);

		\Tester\Assert::type(\Spameri\ElasticQuery\Response\ResultBulk::class, $result);

		$elasticQuery = new \Spameri\ElasticQuery\ElasticQuery();
		$elasticQuery->addMustQuery(new \Spameri\ElasticQuery\Query\MatchAll());

		$searchResult = $getAllBy->execute($elasticQuery, self::INDEX);
		\Tester\Assert::same(3, $searchResult->stats()->total());
	}


	public function testInsertMultipleEmptyCollectionDoesNothing(): void
	{
		/** @var \Spameri\Elastic\Model\InsertMultiple $insertMultiple */
		$insertMultiple = $this->container->getByType(\Spameri\Elastic\Model\InsertMultiple::class);
		/** @var \Spameri\Elastic\Model\GetAllBy $getAllBy */
		$getAllBy = $this->container->getByType(\Spameri\Elastic\Model\GetAllBy::class);
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);

		$collection = new \Spameri\Elastic\Entity\Collection\ElasticEntityCollection(
			$entityManager,
			\SpameriTests\Elastic\Data\Entity\SimpleTestEntity::class,
		);

		// This should execute without error
		$result = $insertMultiple->execute($collection, self::INDEX);

		\Tester\Assert::type(\Spameri\ElasticQuery\Response\ResultBulk::class, $result);

		// Verify no documents were inserted
		$elasticQuery = new \Spameri\ElasticQuery\ElasticQuery();
		$elasticQuery->addMustQuery(new \Spameri\ElasticQuery\Query\MatchAll());

		$searchResult = $getAllBy->execute($elasticQuery, self::INDEX);
		\Tester\Assert::same(0, $searchResult->stats()->total());
	}


	public function testInsertMultipleTwice(): void
	{
		/** @var \Spameri\Elastic\Model\InsertMultiple $insertMultiple */
		$insertMultiple = $this->container->getByType(\Spameri\Elastic\Model\InsertMultiple::class);
		/** @var \Spameri\Elastic\Model\GetAllBy $getAllBy */
		$getAllBy = $this->container->getByType(\Spameri\Elastic\Model\GetAllBy::class);
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);

		// First batch
		$collection1 = new \Spameri\Elastic\Entity\Collection\ElasticEntityCollection(
			$entityManager,
			\SpameriTests\Elastic\Data\Entity\SimpleTestEntity::class,
		);
		for ($i = 1; $i <= 5; $i++) {
			$entity = new \SpameriTests\Elastic\Data\Entity\SimpleTestEntity(
				new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
				'Batch1 Product ' . $i,
				$i,
				$i * 10.0,
				true,
			);
			$collection1->add($entity);
		}
		$insertMultiple->execute($collection1, self::INDEX);

		// Second batch
		$collection2 = new \Spameri\Elastic\Entity\Collection\ElasticEntityCollection(
			$entityManager,
			\SpameriTests\Elastic\Data\Entity\SimpleTestEntity::class,
		);
		for ($i = 1; $i <= 5; $i++) {
			$entity = new \SpameriTests\Elastic\Data\Entity\SimpleTestEntity(
				new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
				'Batch2 Product ' . $i,
				$i + 10,
				$i * 20.0,
				false,
			);
			$collection2->add($entity);
		}
		$insertMultiple->execute($collection2, self::INDEX);

		// Verify all 10 documents exist
		$elasticQuery = new \Spameri\ElasticQuery\ElasticQuery();
		$elasticQuery->addMustQuery(new \Spameri\ElasticQuery\Query\MatchAll());

		$searchResult = $getAllBy->execute($elasticQuery, self::INDEX);
		\Tester\Assert::same(10, $searchResult->stats()->total());
	}


	protected function tearDown(): void
	{
		/** @var \Spameri\Elastic\Model\Indices\Delete $delete */
		$delete = $this->container->getByType(\Spameri\Elastic\Model\Indices\Delete::class);

		try {
			$delete->execute(self::INDEX);
		} catch (\Throwable $e) {
			// Ignore
		}
	}

}

(new InsertMultipleTest())->run();
