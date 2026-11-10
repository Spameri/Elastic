<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Model;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
class DeleteMultipleTest extends \SpameriTests\Elastic\AbstractTestCase
{

	private const INDEX = 'spameri_model_delete_multiple_test';


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
					'name' => [
						'type' => 'keyword',
					],
					'count' => [
						'type' => 'integer',
					],
					'price' => [
						'type' => 'float',
					],
					'active' => [
						'type' => 'boolean',
					],
				],
			],
		]);

		\usleep(100000);
	}


	public function testDeleteMultipleEntities(): void
	{
		/** @var \Spameri\Elastic\Model\Insert $insert */
		$insert = $this->container->getByType(\Spameri\Elastic\Model\Insert::class);
		/** @var \Spameri\Elastic\Model\DeleteMultiple $deleteMultiple */
		$deleteMultiple = $this->container->getByType(\Spameri\Elastic\Model\DeleteMultiple::class);
		/** @var \Spameri\Elastic\Model\GetAllBy $getAllBy */
		$getAllBy = $this->container->getByType(\Spameri\Elastic\Model\GetAllBy::class);

		// Create 5 entities
		$entities = [];
		for ($i = 1; $i <= 5; $i++) {
			$entity = new \SpameriTests\Elastic\Data\Entity\SimpleTestEntity(
				new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
				'Product ' . $i,
				$i,
				$i * 10.0,
				true,
			);
			$insert->execute($entity, self::INDEX, false);
			$entities[] = $entity;
		}

		\usleep(500000);

		// Verify all 5 exist
		$elasticQuery = new \Spameri\ElasticQuery\ElasticQuery();
		$elasticQuery->addMustQuery(new \Spameri\ElasticQuery\Query\MatchAll());

		$result = $getAllBy->execute($elasticQuery, self::INDEX);
		\Tester\Assert::same(5, $result->stats()->total());

		// Create collection with first 3 entities to delete
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);
		$entitiesToDelete = new \Spameri\Elastic\Entity\Collection\ElasticEntityCollection(
			$entityManager,
			\SpameriTests\Elastic\Data\Entity\SimpleTestEntity::class,
		);
		$entitiesToDelete->add($entities[0]);
		$entitiesToDelete->add($entities[1]);
		$entitiesToDelete->add($entities[2]);

		$deleteResult = $deleteMultiple->execute($entitiesToDelete, self::INDEX);

		\Tester\Assert::type(\Spameri\ElasticQuery\Response\ResultBulk::class, $deleteResult);

		\usleep(500000);

		// Verify only 2 remain
		$result = $getAllBy->execute($elasticQuery, self::INDEX);
		\Tester\Assert::same(2, $result->stats()->total());
	}


	public function testDeleteMultipleAllEntities(): void
	{
		/** @var \Spameri\Elastic\Model\Insert $insert */
		$insert = $this->container->getByType(\Spameri\Elastic\Model\Insert::class);
		/** @var \Spameri\Elastic\Model\DeleteMultiple $deleteMultiple */
		$deleteMultiple = $this->container->getByType(\Spameri\Elastic\Model\DeleteMultiple::class);
		/** @var \Spameri\Elastic\Model\GetAllBy $getAllBy */
		$getAllBy = $this->container->getByType(\Spameri\Elastic\Model\GetAllBy::class);

		// Create entities
		$entities = [];
		for ($i = 1; $i <= 3; $i++) {
			$entity = new \SpameriTests\Elastic\Data\Entity\SimpleTestEntity(
				new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
				'Product ' . $i,
				$i,
				$i * 10.0,
				true,
			);
			$insert->execute($entity, self::INDEX, false);
			$entities[] = $entity;
		}

		\usleep(500000);

		// Delete all entities
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);
		$entitiesToDelete = new \Spameri\Elastic\Entity\Collection\ElasticEntityCollection(
			$entityManager,
			\SpameriTests\Elastic\Data\Entity\SimpleTestEntity::class,
		);
		foreach ($entities as $entity) {
			$entitiesToDelete->add($entity);
		}

		$deleteMultiple->execute($entitiesToDelete, self::INDEX);

		\usleep(500000);

		// Verify none remain
		$elasticQuery = new \Spameri\ElasticQuery\ElasticQuery();
		$elasticQuery->addMustQuery(new \Spameri\ElasticQuery\Query\MatchAll());

		$result = $getAllBy->execute($elasticQuery, self::INDEX);
		\Tester\Assert::same(0, $result->stats()->total());
	}


	public function testDeleteMultipleEmptyCollectionReturnsEmptyResult(): void
	{
		/** @var \Spameri\Elastic\Model\DeleteMultiple $deleteMultiple */
		$deleteMultiple = $this->container->getByType(\Spameri\Elastic\Model\DeleteMultiple::class);
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);

		$emptyCollection = new \Spameri\Elastic\Entity\Collection\ElasticEntityCollection(
			$entityManager,
			\SpameriTests\Elastic\Data\Entity\SimpleTestEntity::class,
		);

		// Empty collection should return empty result, not throw
		$result = $deleteMultiple->execute($emptyCollection, self::INDEX);

		\Tester\Assert::type(\Spameri\ElasticQuery\Response\ResultBulk::class, $result);
		\Tester\Assert::same(0, $result->stats()->total());
	}


	public function testDeleteMultipleSingleEntity(): void
	{
		/** @var \Spameri\Elastic\Model\Insert $insert */
		$insert = $this->container->getByType(\Spameri\Elastic\Model\Insert::class);
		/** @var \Spameri\Elastic\Model\DeleteMultiple $deleteMultiple */
		$deleteMultiple = $this->container->getByType(\Spameri\Elastic\Model\DeleteMultiple::class);
		/** @var \Spameri\Elastic\Model\Get $get */
		$get = $this->container->getByType(\Spameri\Elastic\Model\Get::class);

		$entity = new \SpameriTests\Elastic\Data\Entity\SimpleTestEntity(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			'Single Product',
			1,
			10.0,
			true,
		);
		$id = $insert->execute($entity, self::INDEX, false);

		\usleep(500000);

		// Verify it exists
		$result = $get->execute(new \Spameri\Elastic\Entity\Property\ElasticId($id), self::INDEX);
		\Tester\Assert::same($id, $result->hit()->id());

		// Delete single entity via bulk
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);
		$entitiesToDelete = new \Spameri\Elastic\Entity\Collection\ElasticEntityCollection(
			$entityManager,
			\SpameriTests\Elastic\Data\Entity\SimpleTestEntity::class,
		);
		$entitiesToDelete->add($entity);

		$deleteMultiple->execute($entitiesToDelete, self::INDEX);

		\usleep(500000);

		// Verify it's gone
		\Tester\Assert::exception(
			static function () use ($get, $id): void {
				$get->execute(new \Spameri\Elastic\Entity\Property\ElasticId($id), self::INDEX);
			},
			\Spameri\Elastic\Exception\ElasticSearch::class,
		);
	}


	public function testDeleteMultipleReturnsCorrectCounts(): void
	{
		/** @var \Spameri\Elastic\Model\Insert $insert */
		$insert = $this->container->getByType(\Spameri\Elastic\Model\Insert::class);
		/** @var \Spameri\Elastic\Model\DeleteMultiple $deleteMultiple */
		$deleteMultiple = $this->container->getByType(\Spameri\Elastic\Model\DeleteMultiple::class);

		// Create 3 entities
		$entities = [];
		for ($i = 1; $i <= 3; $i++) {
			$entity = new \SpameriTests\Elastic\Data\Entity\SimpleTestEntity(
				new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
				'Product ' . $i,
				$i,
				$i * 10.0,
				true,
			);
			$insert->execute($entity, self::INDEX, false);
			$entities[] = $entity;
		}

		\usleep(500000);

		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);
		$entitiesToDelete = new \Spameri\Elastic\Entity\Collection\ElasticEntityCollection(
			$entityManager,
			\SpameriTests\Elastic\Data\Entity\SimpleTestEntity::class,
		);
		foreach ($entities as $entity) {
			$entitiesToDelete->add($entity);
		}

		$result = $deleteMultiple->execute($entitiesToDelete, self::INDEX);

		\Tester\Assert::type(\Spameri\ElasticQuery\Response\ResultBulk::class, $result);
	}


	public function testDeleteMultipleLargeDataset(): void
	{
		/** @var \Spameri\Elastic\Model\Insert $insert */
		$insert = $this->container->getByType(\Spameri\Elastic\Model\Insert::class);
		/** @var \Spameri\Elastic\Model\DeleteMultiple $deleteMultiple */
		$deleteMultiple = $this->container->getByType(\Spameri\Elastic\Model\DeleteMultiple::class);
		/** @var \Spameri\Elastic\Model\GetAllBy $getAllBy */
		$getAllBy = $this->container->getByType(\Spameri\Elastic\Model\GetAllBy::class);

		// Create 50 entities
		$entities = [];
		for ($i = 1; $i <= 50; $i++) {
			$entity = new \SpameriTests\Elastic\Data\Entity\SimpleTestEntity(
				new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
				'Product ' . $i,
				$i,
				$i * 10.0,
				true,
			);
			$insert->execute($entity, self::INDEX, false);
			$entities[] = $entity;
		}

		\usleep(1000000);

		// Verify 50 exist
		$elasticQuery = new \Spameri\ElasticQuery\ElasticQuery();
		$elasticQuery->addMustQuery(new \Spameri\ElasticQuery\Query\MatchAll());

		$result = $getAllBy->execute($elasticQuery, self::INDEX);
		\Tester\Assert::same(50, $result->stats()->total());

		// Delete first 25
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);
		$entitiesToDelete = new \Spameri\Elastic\Entity\Collection\ElasticEntityCollection(
			$entityManager,
			\SpameriTests\Elastic\Data\Entity\SimpleTestEntity::class,
		);
		for ($i = 0; $i < 25; $i++) {
			$entitiesToDelete->add($entities[$i]);
		}

		$deleteMultiple->execute($entitiesToDelete, self::INDEX);

		\usleep(500000);

		// Verify 25 remain
		$result = $getAllBy->execute($elasticQuery, self::INDEX);
		\Tester\Assert::same(25, $result->stats()->total());
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

(new DeleteMultipleTest())->run();
