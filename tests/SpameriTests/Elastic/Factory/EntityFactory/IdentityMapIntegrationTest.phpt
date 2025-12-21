<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Factory\EntityFactory;

require_once __DIR__ . '/../../../../bootstrap.php';

/**
 * @testCase
 */
class IdentityMapIntegrationTest extends \SpameriTests\Elastic\AbstractTestCase
{

	public function testReturnsCachedEntityFromIdentityMap(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);
		/** @var \Spameri\Elastic\Factory\EntityFactory $entityFactory */
		$entityFactory = $this->container->getByType(\Spameri\Elastic\Factory\EntityFactory::class);
		/** @var \Spameri\Elastic\Model\IdentityMap $identityMap */
		$identityMap = $this->container->getByType(\Spameri\Elastic\Model\IdentityMap::class);

		$hit = new \Spameri\ElasticQuery\Response\Result\Hit(
			source: ['name' => 'Test', 'count' => 1, 'price' => 1.0, 'active' => true],
			position: 0, index: '', type: '', id: 'cached-1', score: 0.0, version: 0,
		);

		// First call creates the entity
		$entity1 = $entityFactory->create(
			$hit,
			\SpameriTests\Elastic\Data\Entity\SimpleTestEntity::class,
			$entityManager,
		);

		// Second call should return the same instance
		$entity2 = $entityFactory->create(
			$hit,
			\SpameriTests\Elastic\Data\Entity\SimpleTestEntity::class,
			$entityManager,
		);

		\Tester\Assert::same($entity1, $entity2);
	}


	public function testStoresNewEntityInIdentityMap(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);
		/** @var \Spameri\Elastic\Factory\EntityFactory $entityFactory */
		$entityFactory = $this->container->getByType(\Spameri\Elastic\Factory\EntityFactory::class);
		/** @var \Spameri\Elastic\Model\IdentityMap $identityMap */
		$identityMap = $this->container->getByType(\Spameri\Elastic\Model\IdentityMap::class);

		$hit = new \Spameri\ElasticQuery\Response\Result\Hit(
			source: ['name' => 'Test', 'count' => 1, 'price' => 1.0, 'active' => true],
			position: 0, index: '', type: '', id: 'store-1', score: 0.0, version: 0,
		);

		$entity = $entityFactory->create(
			$hit,
			\SpameriTests\Elastic\Data\Entity\SimpleTestEntity::class,
			$entityManager,
		);

		// Entity should now be in the identity map
		$cached = $identityMap->get(\SpameriTests\Elastic\Data\Entity\SimpleTestEntity::class, 'store-1');

		\Tester\Assert::same($entity, $cached);
	}


	public function testChangeSetMarkingOnCreate(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);
		/** @var \Spameri\Elastic\Factory\EntityFactory $entityFactory */
		$entityFactory = $this->container->getByType(\Spameri\Elastic\Factory\EntityFactory::class);
		/** @var \Spameri\Elastic\Model\ChangeSet $changeSet */
		$changeSet = $this->container->getByType(\Spameri\Elastic\Model\ChangeSet::class);

		$hit = new \Spameri\ElasticQuery\Response\Result\Hit(
			source: ['name' => 'Test', 'count' => 1, 'price' => 1.0, 'active' => true],
			position: 0, index: '', type: '', id: 'changeset-mark-1', score: 0.0, version: 0,
		);

		$entity = $entityFactory->create(
			$hit,
			\SpameriTests\Elastic\Data\Entity\SimpleTestEntity::class,
			$entityManager,
		);

		// Entity should be marked as existing (loaded from database)
		\Tester\Assert::true($changeSet->isExisting($entity));
	}


	public function testDifferentEntitiesStoredSeparately(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);
		/** @var \Spameri\Elastic\Factory\EntityFactory $entityFactory */
		$entityFactory = $this->container->getByType(\Spameri\Elastic\Factory\EntityFactory::class);
		/** @var \Spameri\Elastic\Model\IdentityMap $identityMap */
		$identityMap = $this->container->getByType(\Spameri\Elastic\Model\IdentityMap::class);

		$hit1 = new \Spameri\ElasticQuery\Response\Result\Hit(
			source: ['name' => 'Entity 1', 'count' => 1, 'price' => 1.0, 'active' => true],
			position: 0, index: '', type: '', id: 'sep-1', score: 0.0, version: 0,
		);

		$hit2 = new \Spameri\ElasticQuery\Response\Result\Hit(
			source: ['name' => 'Entity 2', 'count' => 2, 'price' => 2.0, 'active' => false],
			position: 0, index: '', type: '', id: 'sep-2', score: 0.0, version: 0,
		);

		$entity1 = $entityFactory->create($hit1, \SpameriTests\Elastic\Data\Entity\SimpleTestEntity::class, $entityManager);
		$entity2 = $entityFactory->create($hit2, \SpameriTests\Elastic\Data\Entity\SimpleTestEntity::class, $entityManager);

		\Tester\Assert::notSame($entity1, $entity2);
		\Tester\Assert::same($entity1, $identityMap->get(\SpameriTests\Elastic\Data\Entity\SimpleTestEntity::class, 'sep-1'));
		\Tester\Assert::same($entity2, $identityMap->get(\SpameriTests\Elastic\Data\Entity\SimpleTestEntity::class, 'sep-2'));
	}


	public function testIdentityMapPreventsDuplicateInstances(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);
		/** @var \Spameri\Elastic\Factory\EntityFactory $entityFactory */
		$entityFactory = $this->container->getByType(\Spameri\Elastic\Factory\EntityFactory::class);

		// Same ID but different data (simulating stale data)
		$hit1 = new \Spameri\ElasticQuery\Response\Result\Hit(
			source: ['name' => 'Original', 'count' => 1, 'price' => 1.0, 'active' => true],
			position: 0, index: '', type: '', id: 'dup-check', score: 0.0, version: 0,
		);

		$hit2 = new \Spameri\ElasticQuery\Response\Result\Hit(
			source: ['name' => 'Updated', 'count' => 999, 'price' => 999.0, 'active' => false],
			position: 0, index: '', type: '', id: 'dup-check', score: 0.0, version: 0,
		);

		$entity1 = $entityFactory->create($hit1, \SpameriTests\Elastic\Data\Entity\SimpleTestEntity::class, $entityManager);
		$entity2 = $entityFactory->create($hit2, \SpameriTests\Elastic\Data\Entity\SimpleTestEntity::class, $entityManager);

		// Should return same instance (identity map)
		\Tester\Assert::same($entity1, $entity2);

		// Should have original data, not updated
		\Tester\Assert::same('Original', $entity2->name);
	}

}

(new IdentityMapIntegrationTest())->run();
