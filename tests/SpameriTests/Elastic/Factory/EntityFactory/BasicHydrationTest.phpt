<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Factory\EntityFactory;

require_once __DIR__ . '/../../../../bootstrap.php';

/**
 * @testCase
 */
class BasicHydrationTest extends \SpameriTests\Elastic\AbstractTestCase
{

	public function testHydrateSimpleEntityFromHit(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);
		/** @var \Spameri\Elastic\Factory\EntityFactory $entityFactory */
		$entityFactory = $this->container->getByType(\Spameri\Elastic\Factory\EntityFactory::class);

		$hit = new \Spameri\ElasticQuery\Response\Result\Hit(
			source: [
				'name' => 'Test Entity',
				'count' => 42,
				'price' => 19.99,
				'active' => true,
				'description' => 'A test description',
			],
			position: 0,
			index: 'test_index',
			type: '_doc',
			id: 'test-id-123',
			score: 1.0,
			version: 1,
		);

		$entity = $entityFactory->create(
			$hit,
			\SpameriTests\Elastic\Data\Entity\SimpleTestEntity::class,
			$entityManager,
		);

		\Tester\Assert::type(\SpameriTests\Elastic\Data\Entity\SimpleTestEntity::class, $entity);
		\Tester\Assert::same('test-id-123', $entity->id()->value());
		\Tester\Assert::same('Test Entity', $entity->name);
		\Tester\Assert::same(42, $entity->count);
		\Tester\Assert::same(19.99, $entity->price);
		\Tester\Assert::true($entity->active);
		\Tester\Assert::same('A test description', $entity->description);
	}


	public function testHydrateAllScalarTypes(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);
		/** @var \Spameri\Elastic\Factory\EntityFactory $entityFactory */
		$entityFactory = $this->container->getByType(\Spameri\Elastic\Factory\EntityFactory::class);

		// Test string type
		$hitString = new \Spameri\ElasticQuery\Response\Result\Hit(
			source: ['name' => 'String Test', 'count' => 0, 'price' => 0.0, 'active' => false],
			position: 0, index: '', type: '', id: 'str-1', score: 0.0, version: 0,
		);
		$entity = $entityFactory->create($hitString, \SpameriTests\Elastic\Data\Entity\SimpleTestEntity::class, $entityManager);
		\Tester\Assert::type('string', $entity->name);

		// Test int type
		$hitInt = new \Spameri\ElasticQuery\Response\Result\Hit(
			source: ['name' => 'Int Test', 'count' => 999, 'price' => 0.0, 'active' => false],
			position: 0, index: '', type: '', id: 'int-1', score: 0.0, version: 0,
		);
		$entity = $entityFactory->create($hitInt, \SpameriTests\Elastic\Data\Entity\SimpleTestEntity::class, $entityManager);
		\Tester\Assert::type('int', $entity->count);
		\Tester\Assert::same(999, $entity->count);

		// Test float type
		$hitFloat = new \Spameri\ElasticQuery\Response\Result\Hit(
			source: ['name' => 'Float Test', 'count' => 0, 'price' => 123.456, 'active' => false],
			position: 0, index: '', type: '', id: 'float-1', score: 0.0, version: 0,
		);
		$entity = $entityFactory->create($hitFloat, \SpameriTests\Elastic\Data\Entity\SimpleTestEntity::class, $entityManager);
		\Tester\Assert::type('float', $entity->price);
		\Tester\Assert::same(123.456, $entity->price);

		// Test bool type - true
		$hitBoolTrue = new \Spameri\ElasticQuery\Response\Result\Hit(
			source: ['name' => 'Bool Test', 'count' => 0, 'price' => 0.0, 'active' => true],
			position: 0, index: '', type: '', id: 'bool-t', score: 0.0, version: 0,
		);
		$entity = $entityFactory->create($hitBoolTrue, \SpameriTests\Elastic\Data\Entity\SimpleTestEntity::class, $entityManager);
		\Tester\Assert::type('bool', $entity->active);
		\Tester\Assert::true($entity->active);

		// Test bool type - false
		$hitBoolFalse = new \Spameri\ElasticQuery\Response\Result\Hit(
			source: ['name' => 'Bool Test', 'count' => 0, 'price' => 0.0, 'active' => false],
			position: 0, index: '', type: '', id: 'bool-f', score: 0.0, version: 0,
		);
		$entity = $entityFactory->create($hitBoolFalse, \SpameriTests\Elastic\Data\Entity\SimpleTestEntity::class, $entityManager);
		\Tester\Assert::false($entity->active);
	}


	public function testHydrateNullableProperties(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);
		/** @var \Spameri\Elastic\Factory\EntityFactory $entityFactory */
		$entityFactory = $this->container->getByType(\Spameri\Elastic\Factory\EntityFactory::class);

		// With null value
		$hitNull = new \Spameri\ElasticQuery\Response\Result\Hit(
			source: [
				'name' => 'Nullable Test',
				'count' => 1,
				'price' => 1.0,
				'active' => true,
				'description' => null,
			],
			position: 0, index: '', type: '', id: 'null-1', score: 0.0, version: 0,
		);
		$entity = $entityFactory->create($hitNull, \SpameriTests\Elastic\Data\Entity\SimpleTestEntity::class, $entityManager);
		\Tester\Assert::null($entity->description);

		// With value
		$hitValue = new \Spameri\ElasticQuery\Response\Result\Hit(
			source: [
				'name' => 'Nullable Test',
				'count' => 1,
				'price' => 1.0,
				'active' => true,
				'description' => 'Has a value',
			],
			position: 0, index: '', type: '', id: 'null-2', score: 0.0, version: 0,
		);
		$entity = $entityFactory->create($hitValue, \SpameriTests\Elastic\Data\Entity\SimpleTestEntity::class, $entityManager);
		\Tester\Assert::same('Has a value', $entity->description);
	}


	public function testHydrateWithDefaultValues(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);
		/** @var \Spameri\Elastic\Factory\EntityFactory $entityFactory */
		$entityFactory = $this->container->getByType(\Spameri\Elastic\Factory\EntityFactory::class);

		// Without description field (should use default null)
		$hit = new \Spameri\ElasticQuery\Response\Result\Hit(
			source: [
				'name' => 'Default Test',
				'count' => 1,
				'price' => 1.0,
				'active' => true,
				// description is not present
			],
			position: 0, index: '', type: '', id: 'default-1', score: 0.0, version: 0,
		);

		$entity = $entityFactory->create($hit, \SpameriTests\Elastic\Data\Entity\SimpleTestEntity::class, $entityManager);

		// Should use default value (null for description)
		\Tester\Assert::null($entity->description);
	}


	public function testHydrateCreatesElasticIdFromHitId(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);
		/** @var \Spameri\Elastic\Factory\EntityFactory $entityFactory */
		$entityFactory = $this->container->getByType(\Spameri\Elastic\Factory\EntityFactory::class);

		$hit = new \Spameri\ElasticQuery\Response\Result\Hit(
			source: ['name' => 'ID Test', 'count' => 1, 'price' => 1.0, 'active' => true],
			position: 0, index: '', type: '', id: 'custom-elastic-id-xyz', score: 0.0, version: 0,
		);

		$entity = $entityFactory->create($hit, \SpameriTests\Elastic\Data\Entity\SimpleTestEntity::class, $entityManager);

		\Tester\Assert::type(\Spameri\Elastic\Entity\Property\ElasticId::class, $entity->id());
		\Tester\Assert::same('custom-elastic-id-xyz', $entity->id()->value());
	}

}

(new BasicHydrationTest())->run();
