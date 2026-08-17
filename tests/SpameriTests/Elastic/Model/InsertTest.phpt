<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Model;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
class InsertTest extends \SpameriTests\Elastic\AbstractTestCase
{

	private const INDEX = 'spameri_model_insert_test';


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
					'description' => [
						'type' => 'text',
					],
				],
			],
		]);

		\usleep(100000);
	}


	public function testInsertSimpleEntity(): void
	{
		/** @var \Spameri\Elastic\Model\Insert $insert */
		$insert = $this->container->getByType(\Spameri\Elastic\Model\Insert::class);

		$entity = new \SpameriTests\Elastic\Data\Entity\SimpleTestEntity(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			'Test Product',
			10,
			99.99,
			true,
			'A test product description',
		);

		$id = $insert->execute($entity, self::INDEX, false);

		\Tester\Assert::type('string', $id);
		\Tester\Assert::same(20, \strlen($id)); // ES generates 20-char IDs
		\Tester\Assert::same($id, $entity->id()->value());
		\Tester\Assert::false($entity->id() instanceof \Spameri\Elastic\Entity\Property\EmptyElasticId);
	}


	public function testInsertEntityWithNullableProperty(): void
	{
		/** @var \Spameri\Elastic\Model\Insert $insert */
		$insert = $this->container->getByType(\Spameri\Elastic\Model\Insert::class);

		$entity = new \SpameriTests\Elastic\Data\Entity\SimpleTestEntity(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			'Product without description',
			5,
			49.99,
			false,
			null, // Nullable description
		);

		$id = $insert->execute($entity, self::INDEX, false);

		\Tester\Assert::type('string', $id);
		\Tester\Assert::same(20, \strlen($id));
	}


	public function testInsertExistingEntityUpdates(): void
	{
		/** @var \Spameri\Elastic\Model\Insert $insert */
		$insert = $this->container->getByType(\Spameri\Elastic\Model\Insert::class);
		/** @var \Spameri\Elastic\Model\Get $get */
		$get = $this->container->getByType(\Spameri\Elastic\Model\Get::class);

		// First insert
		$entity = new \SpameriTests\Elastic\Data\Entity\SimpleTestEntity(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			'Original Name',
			1,
			10.0,
			true,
		);

		$id = $insert->execute($entity, self::INDEX, false);

		\usleep(500000);

		// Verify first insert
		$result = $get->execute(new \Spameri\Elastic\Entity\Property\ElasticId($id), self::INDEX);
		\Tester\Assert::same('Original Name', $result->hit()->source()['name']);

		// Second insert with same entity (should update)
		$updatedEntity = new \SpameriTests\Elastic\Data\Entity\SimpleTestEntity(
			new \Spameri\Elastic\Entity\Property\ElasticId($id),
			'Updated Name',
			2,
			20.0,
			false,
		);

		// No need to mark as changed - new object with existing ID will be considered changed
		// since it's not in the persisted map

		$updatedId = $insert->execute($updatedEntity, self::INDEX, false);

		\usleep(500000);

		\Tester\Assert::same($id, $updatedId);

		// Verify update
		$result = $get->execute(new \Spameri\Elastic\Entity\Property\ElasticId($id), self::INDEX);
		\Tester\Assert::same('Updated Name', $result->hit()->source()['name']);
	}


	public function testInsertReturnsNewId(): void
	{
		/** @var \Spameri\Elastic\Model\Insert $insert */
		$insert = $this->container->getByType(\Spameri\Elastic\Model\Insert::class);

		$entity1 = new \SpameriTests\Elastic\Data\Entity\SimpleTestEntity(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			'Product 1',
			1,
			10.0,
			true,
		);

		$entity2 = new \SpameriTests\Elastic\Data\Entity\SimpleTestEntity(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			'Product 2',
			2,
			20.0,
			true,
		);

		$id1 = $insert->execute($entity1, self::INDEX, false);
		$id2 = $insert->execute($entity2, self::INDEX, false);

		\Tester\Assert::notSame($id1, $id2);
		\Tester\Assert::same($id1, $entity1->id()->value());
		\Tester\Assert::same($id2, $entity2->id()->value());
	}


	public function testInsertMarksEntityInIdentityMap(): void
	{
		/** @var \Spameri\Elastic\Model\Insert $insert */
		$insert = $this->container->getByType(\Spameri\Elastic\Model\Insert::class);
		/** @var \Spameri\Elastic\Model\IdentityMap $identityMap */
		$identityMap = $this->container->getByType(\Spameri\Elastic\Model\IdentityMap::class);

		$entity = new \SpameriTests\Elastic\Data\Entity\SimpleTestEntity(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			'Test Product',
			10,
			99.99,
			true,
		);

		// Before insert, entity should be marked as changed
		\Tester\Assert::true($identityMap->isChanged($entity));

		$insert->execute($entity, self::INDEX, false);

		// After insert, entity should be marked as not changed
		\Tester\Assert::false($identityMap->isChanged($entity));
	}


	public function testInsertSkipsUnchangedEntity(): void
	{
		/** @var \Spameri\Elastic\Model\Insert $insert */
		$insert = $this->container->getByType(\Spameri\Elastic\Model\Insert::class);
		/** @var \Spameri\Elastic\Model\IdentityMap $identityMap */
		$identityMap = $this->container->getByType(\Spameri\Elastic\Model\IdentityMap::class);

		$entity = new \SpameriTests\Elastic\Data\Entity\SimpleTestEntity(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			'Test Product',
			10,
			99.99,
			true,
		);

		// First insert
		$id1 = $insert->execute($entity, self::INDEX, false);

		// Second insert should skip (entity unchanged)
		$id2 = $insert->execute($entity, self::INDEX, false);

		\Tester\Assert::same($id1, $id2);

		// Third insert should also skip
		$id3 = $insert->execute($entity, self::INDEX, false);

		\Tester\Assert::same($id1, $id3);
	}


	public function testInsertWithValueObjects(): void
	{
		/** @var \Spameri\Elastic\Model\Insert $insert */
		$insert = $this->container->getByType(\Spameri\Elastic\Model\Insert::class);

		$entity = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			null,
		);

		$id = $insert->execute($entity, self::INDEX, false);

		\Tester\Assert::type('string', $id);
		\Tester\Assert::same(20, \strlen($id));
	}


	public function testInsertRefreshesIndex(): void
	{
		/** @var \Spameri\Elastic\Model\Insert $insert */
		$insert = $this->container->getByType(\Spameri\Elastic\Model\Insert::class);
		/** @var \Spameri\Elastic\Model\Get $get */
		$get = $this->container->getByType(\Spameri\Elastic\Model\Get::class);

		$entity = new \SpameriTests\Elastic\Data\Entity\SimpleTestEntity(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			'Test Product',
			10,
			99.99,
			true,
		);

		$id = $insert->execute($entity, self::INDEX, false);

		// Document should be immediately available (no need to wait)
		$result = $get->execute(new \Spameri\Elastic\Entity\Property\ElasticId($id), self::INDEX);

		\Tester\Assert::same($id, $result->hit()->id());
		\Tester\Assert::same('Test Product', $result->hit()->source()['name']);
	}


	public function testInsertMultipleEntitiesSequentially(): void
	{
		/** @var \Spameri\Elastic\Model\Insert $insert */
		$insert = $this->container->getByType(\Spameri\Elastic\Model\Insert::class);
		/** @var \Spameri\Elastic\Model\GetAllBy $getAllBy */
		$getAllBy = $this->container->getByType(\Spameri\Elastic\Model\GetAllBy::class);

		$ids = [];
		for ($i = 1; $i <= 10; $i++) {
			$entity = new \SpameriTests\Elastic\Data\Entity\SimpleTestEntity(
				new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
				'Product ' . $i,
				$i,
				$i * 10.0,
				$i % 2 === 0,
			);

			$ids[] = $insert->execute($entity, self::INDEX, false);
		}

		\Tester\Assert::count(10, $ids);
		\Tester\Assert::same(10, \count(\array_unique($ids))); // All IDs should be unique

		$elasticQuery = new \Spameri\ElasticQuery\ElasticQuery();
		$elasticQuery->addMustQuery(new \Spameri\ElasticQuery\Query\MatchAll());

		$result = $getAllBy->execute($elasticQuery, self::INDEX);

		\Tester\Assert::same(10, $result->stats()->total());
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

(new InsertTest())->run();
