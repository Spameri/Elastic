<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Factory\EntityFactory;

require_once __DIR__ . '/../../../../bootstrap.php';

/**
 * Tests for lazy loading of collections in EntityFactory.
 *
 * @testCase
 */
class LazyLoadingTest extends \SpameriTests\Elastic\AbstractTestCase
{

	public function testEntityCollectionIsNotInitializedAfterHydration(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);
		/** @var \Spameri\Elastic\Factory\EntityFactory $entityFactory */
		$entityFactory = $this->container->getByType(\Spameri\Elastic\Factory\EntityFactory::class);

		$hit = new \Spameri\ElasticQuery\Response\Result\Hit(
			source: [
				'name' => 'Lazy Entity',
				'items' => [
					[
						'entityClass' => \SpameriTests\Elastic\Data\Entity\NestedObject::class,
						'title' => 'Item 1',
						'order' => 1,
					],
					[
						'entityClass' => \SpameriTests\Elastic\Data\Entity\NestedObject::class,
						'title' => 'Item 2',
						'order' => 2,
					],
				],
			],
			position: 0, index: '', type: '', id: 'lazy-1', score: 0.0, version: 0,
		);

		$entity = $entityFactory->create(
			$hit,
			\SpameriTests\Elastic\Data\Entity\EntityWithCollection::class,
			$entityManager,
		);

		// Collection should exist but NOT be initialized yet
		\Tester\Assert::type(\Spameri\Elastic\Entity\Collection\EntityCollection::class, $entity->items);
		\Tester\Assert::false($entity->items->initialized());
	}


	public function testEntityCollectionInitializesOnFirstAccess(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);
		/** @var \Spameri\Elastic\Factory\EntityFactory $entityFactory */
		$entityFactory = $this->container->getByType(\Spameri\Elastic\Factory\EntityFactory::class);

		$hit = new \Spameri\ElasticQuery\Response\Result\Hit(
			source: [
				'name' => 'Lazy Entity',
				'items' => [
					[
						'entityClass' => \SpameriTests\Elastic\Data\Entity\NestedObject::class,
						'title' => 'Item 1',
						'order' => 1,
					],
					[
						'entityClass' => \SpameriTests\Elastic\Data\Entity\NestedObject::class,
						'title' => 'Item 2',
						'order' => 2,
					],
				],
			],
			position: 0, index: '', type: '', id: 'lazy-2', score: 0.0, version: 0,
		);

		$entity = $entityFactory->create(
			$hit,
			\SpameriTests\Elastic\Data\Entity\EntityWithCollection::class,
			$entityManager,
		);

		\Tester\Assert::false($entity->items->initialized());

		// Accessing count() should trigger initialization
		\Tester\Assert::same(2, $entity->items->count());
		\Tester\Assert::true($entity->items->initialized());
	}


	public function testEntityCollectionInitializesOnEntity(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);
		/** @var \Spameri\Elastic\Factory\EntityFactory $entityFactory */
		$entityFactory = $this->container->getByType(\Spameri\Elastic\Factory\EntityFactory::class);

		$hit = new \Spameri\ElasticQuery\Response\Result\Hit(
			source: [
				'name' => 'Lazy Entity',
				'items' => [
					[
						'entityClass' => \SpameriTests\Elastic\Data\Entity\NestedObject::class,
						'title' => 'First Item',
						'order' => 1,
					],
				],
			],
			position: 0, index: '', type: '', id: 'lazy-3', score: 0.0, version: 0,
		);

		$entity = $entityFactory->create(
			$hit,
			\SpameriTests\Elastic\Data\Entity\EntityWithCollection::class,
			$entityManager,
		);

		\Tester\Assert::false($entity->items->initialized());

		// Accessing entity() should trigger initialization
		$item = $entity->items->entity('1');
		\Tester\Assert::true($entity->items->initialized());
		\Tester\Assert::type(\SpameriTests\Elastic\Data\Entity\NestedObject::class, $item);
		\Tester\Assert::same('First Item', $item->title);
	}


	public function testEntityCollectionInitializesOnIterator(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);
		/** @var \Spameri\Elastic\Factory\EntityFactory $entityFactory */
		$entityFactory = $this->container->getByType(\Spameri\Elastic\Factory\EntityFactory::class);

		$hit = new \Spameri\ElasticQuery\Response\Result\Hit(
			source: [
				'name' => 'Lazy Entity',
				'items' => [
					[
						'entityClass' => \SpameriTests\Elastic\Data\Entity\NestedObject::class,
						'title' => 'Item A',
						'order' => 1,
					],
					[
						'entityClass' => \SpameriTests\Elastic\Data\Entity\NestedObject::class,
						'title' => 'Item B',
						'order' => 2,
					],
				],
			],
			position: 0, index: '', type: '', id: 'lazy-4', score: 0.0, version: 0,
		);

		$entity = $entityFactory->create(
			$hit,
			\SpameriTests\Elastic\Data\Entity\EntityWithCollection::class,
			$entityManager,
		);

		\Tester\Assert::false($entity->items->initialized());

		// foreach triggers getIterator() which should initialize
		$titles = [];
		foreach ($entity->items as $item) {
			$titles[] = $item->title;
		}

		\Tester\Assert::true($entity->items->initialized());
		\Tester\Assert::same(['Item A', 'Item B'], $titles);
	}


	public function testEntityCollectionInitializesOnFirst(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);
		/** @var \Spameri\Elastic\Factory\EntityFactory $entityFactory */
		$entityFactory = $this->container->getByType(\Spameri\Elastic\Factory\EntityFactory::class);

		$hit = new \Spameri\ElasticQuery\Response\Result\Hit(
			source: [
				'name' => 'Lazy Entity',
				'items' => [
					[
						'entityClass' => \SpameriTests\Elastic\Data\Entity\NestedObject::class,
						'title' => 'Only Item',
						'order' => 5,
					],
				],
			],
			position: 0, index: '', type: '', id: 'lazy-5', score: 0.0, version: 0,
		);

		$entity = $entityFactory->create(
			$hit,
			\SpameriTests\Elastic\Data\Entity\EntityWithCollection::class,
			$entityManager,
		);

		\Tester\Assert::false($entity->items->initialized());

		$first = $entity->items->first();
		\Tester\Assert::true($entity->items->initialized());
		\Tester\Assert::same('Only Item', $first->title);
	}


	public function testEntityCollectionInitializesOnKeys(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);
		/** @var \Spameri\Elastic\Factory\EntityFactory $entityFactory */
		$entityFactory = $this->container->getByType(\Spameri\Elastic\Factory\EntityFactory::class);

		$hit = new \Spameri\ElasticQuery\Response\Result\Hit(
			source: [
				'name' => 'Lazy Entity',
				'items' => [
					[
						'entityClass' => \SpameriTests\Elastic\Data\Entity\NestedObject::class,
						'title' => 'Item 1',
						'order' => 10,
					],
					[
						'entityClass' => \SpameriTests\Elastic\Data\Entity\NestedObject::class,
						'title' => 'Item 2',
						'order' => 20,
					],
				],
			],
			position: 0, index: '', type: '', id: 'lazy-6', score: 0.0, version: 0,
		);

		$entity = $entityFactory->create(
			$hit,
			\SpameriTests\Elastic\Data\Entity\EntityWithCollection::class,
			$entityManager,
		);

		\Tester\Assert::false($entity->items->initialized());

		$keys = $entity->items->keys();
		\Tester\Assert::true($entity->items->initialized());
		\Tester\Assert::same(['10', '20'], $keys);
	}


	public function testEntityCollectionInitializesOnIsValue(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);
		/** @var \Spameri\Elastic\Factory\EntityFactory $entityFactory */
		$entityFactory = $this->container->getByType(\Spameri\Elastic\Factory\EntityFactory::class);

		$hit = new \Spameri\ElasticQuery\Response\Result\Hit(
			source: [
				'name' => 'Lazy Entity',
				'items' => [
					[
						'entityClass' => \SpameriTests\Elastic\Data\Entity\NestedObject::class,
						'title' => 'Item',
						'order' => 1,
					],
				],
			],
			position: 0, index: '', type: '', id: 'lazy-7', score: 0.0, version: 0,
		);

		$entity = $entityFactory->create(
			$hit,
			\SpameriTests\Elastic\Data\Entity\EntityWithCollection::class,
			$entityManager,
		);

		\Tester\Assert::false($entity->items->initialized());

		\Tester\Assert::true($entity->items->isValue('1'));
		\Tester\Assert::true($entity->items->initialized());
		\Tester\Assert::false($entity->items->isValue('999'));
	}


	public function testEntityCollectionInitializesOnRemove(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);
		/** @var \Spameri\Elastic\Factory\EntityFactory $entityFactory */
		$entityFactory = $this->container->getByType(\Spameri\Elastic\Factory\EntityFactory::class);

		$hit = new \Spameri\ElasticQuery\Response\Result\Hit(
			source: [
				'name' => 'Lazy Entity',
				'items' => [
					[
						'entityClass' => \SpameriTests\Elastic\Data\Entity\NestedObject::class,
						'title' => 'Item 1',
						'order' => 1,
					],
					[
						'entityClass' => \SpameriTests\Elastic\Data\Entity\NestedObject::class,
						'title' => 'Item 2',
						'order' => 2,
					],
				],
			],
			position: 0, index: '', type: '', id: 'lazy-8', score: 0.0, version: 0,
		);

		$entity = $entityFactory->create(
			$hit,
			\SpameriTests\Elastic\Data\Entity\EntityWithCollection::class,
			$entityManager,
		);

		\Tester\Assert::false($entity->items->initialized());

		$entity->items->remove('1');
		\Tester\Assert::true($entity->items->initialized());
		\Tester\Assert::same(1, $entity->items->count());
	}


	public function testEntityCollectionInitializesOnAdd(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);
		/** @var \Spameri\Elastic\Factory\EntityFactory $entityFactory */
		$entityFactory = $this->container->getByType(\Spameri\Elastic\Factory\EntityFactory::class);

		$hit = new \Spameri\ElasticQuery\Response\Result\Hit(
			source: [
				'name' => 'Lazy Entity',
				'items' => [
					[
						'entityClass' => \SpameriTests\Elastic\Data\Entity\NestedObject::class,
						'title' => 'Item 1',
						'order' => 1,
					],
				],
			],
			position: 0, index: '', type: '', id: 'lazy-9', score: 0.0, version: 0,
		);

		$entity = $entityFactory->create(
			$hit,
			\SpameriTests\Elastic\Data\Entity\EntityWithCollection::class,
			$entityManager,
		);

		\Tester\Assert::false($entity->items->initialized());

		// Adding a new item should trigger initialization first
		$newItem = new \SpameriTests\Elastic\Data\Entity\NestedObject('New Item', 99);
		$entity->items->add($newItem);

		\Tester\Assert::true($entity->items->initialized());
		\Tester\Assert::same(2, $entity->items->count());
		\Tester\Assert::same('Item 1', $entity->items->entity('1')->title);
		\Tester\Assert::same('New Item', $entity->items->entity('99')->title);
	}


	public function testEmptyCollectionIsInitialized(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);
		/** @var \Spameri\Elastic\Factory\EntityFactory $entityFactory */
		$entityFactory = $this->container->getByType(\Spameri\Elastic\Factory\EntityFactory::class);

		$hit = new \Spameri\ElasticQuery\Response\Result\Hit(
			source: [
				'name' => 'Empty Collection Entity',
				'items' => [],
			],
			position: 0, index: '', type: '', id: 'lazy-empty', score: 0.0, version: 0,
		);

		$entity = $entityFactory->create(
			$hit,
			\SpameriTests\Elastic\Data\Entity\EntityWithCollection::class,
			$entityManager,
		);

		// Empty collections should be already initialized (no initializer set)
		\Tester\Assert::true($entity->items->initialized());
		\Tester\Assert::same(0, $entity->items->count());
	}


	public function testNullCollectionIsInitialized(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);
		/** @var \Spameri\Elastic\Factory\EntityFactory $entityFactory */
		$entityFactory = $this->container->getByType(\Spameri\Elastic\Factory\EntityFactory::class);

		$hit = new \Spameri\ElasticQuery\Response\Result\Hit(
			source: [
				'name' => 'Null Collection Entity',
				'items' => null,
			],
			position: 0, index: '', type: '', id: 'lazy-null', score: 0.0, version: 0,
		);

		$entity = $entityFactory->create(
			$hit,
			\SpameriTests\Elastic\Data\Entity\EntityWithCollection::class,
			$entityManager,
		);

		// Null collections should be already initialized (no initializer set)
		\Tester\Assert::true($entity->items->initialized());
		\Tester\Assert::same(0, $entity->items->count());
	}


	public function testLazyCollectionItemsAreMarkedExistingAfterInit(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);
		/** @var \Spameri\Elastic\Factory\EntityFactory $entityFactory */
		$entityFactory = $this->container->getByType(\Spameri\Elastic\Factory\EntityFactory::class);
		/** @var \Spameri\Elastic\Model\ChangeSet $changeSet */
		$changeSet = $this->container->getByType(\Spameri\Elastic\Model\ChangeSet::class);

		$hit = new \Spameri\ElasticQuery\Response\Result\Hit(
			source: [
				'name' => 'ChangeSet Entity',
				'items' => [
					[
						'entityClass' => \SpameriTests\Elastic\Data\Entity\NestedObject::class,
						'title' => 'Tracked Item',
						'order' => 1,
					],
				],
			],
			position: 0, index: '', type: '', id: 'lazy-cs', score: 0.0, version: 0,
		);

		$entity = $entityFactory->create(
			$hit,
			\SpameriTests\Elastic\Data\Entity\EntityWithCollection::class,
			$entityManager,
		);

		// Collection itself is marked existing eagerly
		\Tester\Assert::true($changeSet->isExisting($entity->items));

		// Trigger lazy init by accessing an item
		$item = $entity->items->entity('1');

		// After initialization, the item should be marked as existing
		\Tester\Assert::true($changeSet->isExisting($item));
	}


	public function testLazyCollectionClearResetsInitializationState(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);
		/** @var \Spameri\Elastic\Factory\EntityFactory $entityFactory */
		$entityFactory = $this->container->getByType(\Spameri\Elastic\Factory\EntityFactory::class);

		$hit = new \Spameri\ElasticQuery\Response\Result\Hit(
			source: [
				'name' => 'Clear Entity',
				'items' => [
					[
						'entityClass' => \SpameriTests\Elastic\Data\Entity\NestedObject::class,
						'title' => 'Item 1',
						'order' => 1,
					],
				],
			],
			position: 0, index: '', type: '', id: 'lazy-clear', score: 0.0, version: 0,
		);

		$entity = $entityFactory->create(
			$hit,
			\SpameriTests\Elastic\Data\Entity\EntityWithCollection::class,
			$entityManager,
		);

		\Tester\Assert::false($entity->items->initialized());

		// Clear should reset to initialized state with empty collection
		$entity->items->clear();
		\Tester\Assert::true($entity->items->initialized());
		\Tester\Assert::same(0, $entity->items->count());
	}


	public function testLazyInitializerRunsOnlyOnce(): void
	{
		$callCount = 0;
		$collection = new \Spameri\Elastic\Entity\Collection\EntityCollection();

		$collection->setInitializer(function () use (&$callCount, $collection): void {
			$callCount++;
			$collection->add(new \SpameriTests\Elastic\Data\Entity\NestedObject('Lazy Item', 1));
		});

		\Tester\Assert::false($collection->initialized());
		\Tester\Assert::same(0, $callCount);

		// First access triggers initializer
		$collection->count();
		\Tester\Assert::same(1, $callCount);
		\Tester\Assert::same(1, $collection->count());

		// Second access should NOT trigger initializer again
		$collection->count();
		\Tester\Assert::same(1, $callCount);
	}


	public function testElasticEntityCollectionUsesSetElasticIds(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);
		/** @var \Spameri\Elastic\Factory\EntityFactory $entityFactory */
		$entityFactory = $this->container->getByType(\Spameri\Elastic\Factory\EntityFactory::class);

		$hit = new \Spameri\ElasticQuery\Response\Result\Hit(
			source: [
				'name' => 'Entity With Related',
				'related' => [
					'id-related-1',
					'id-related-2',
				],
			],
			position: 0, index: '', type: '', id: 'lazy-elastic-1', score: 0.0, version: 0,
		);

		$entity = $entityFactory->create(
			$hit,
			\SpameriTests\Elastic\Data\Entity\EntityWithElasticCollection::class,
			$entityManager,
		);

		// ElasticEntityCollection should NOT be initialized (deferred via setElasticIds)
		\Tester\Assert::false($entity->related->initialized());

		// elasticIds should contain the IDs from the source data
		\Tester\Assert::same(['id-related-1', 'id-related-2'], $entity->related->elasticIds());
	}


	public function testElasticEntityCollectionEmptyIsNotDeferred(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);
		/** @var \Spameri\Elastic\Factory\EntityFactory $entityFactory */
		$entityFactory = $this->container->getByType(\Spameri\Elastic\Factory\EntityFactory::class);

		$hit = new \Spameri\ElasticQuery\Response\Result\Hit(
			source: [
				'name' => 'Entity With Empty Related',
				'related' => [],
			],
			position: 0, index: '', type: '', id: 'lazy-elastic-empty', score: 0.0, version: 0,
		);

		$entity = $entityFactory->create(
			$hit,
			\SpameriTests\Elastic\Data\Entity\EntityWithElasticCollection::class,
			$entityManager,
		);

		// Empty ElasticEntityCollection should have no IDs to load
		\Tester\Assert::same([], $entity->related->elasticIds());
	}


	public function testElasticEntityCollectionNullIsNotDeferred(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);
		/** @var \Spameri\Elastic\Factory\EntityFactory $entityFactory */
		$entityFactory = $this->container->getByType(\Spameri\Elastic\Factory\EntityFactory::class);

		$hit = new \Spameri\ElasticQuery\Response\Result\Hit(
			source: [
				'name' => 'Entity With Null Related',
				'related' => null,
			],
			position: 0, index: '', type: '', id: 'lazy-elastic-null', score: 0.0, version: 0,
		);

		$entity = $entityFactory->create(
			$hit,
			\SpameriTests\Elastic\Data\Entity\EntityWithElasticCollection::class,
			$entityManager,
		);

		// Null ElasticEntityCollection should have no IDs to load
		\Tester\Assert::same([], $entity->related->elasticIds());
	}


	public function testElasticEntityCollectionIsMarkedExisting(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);
		/** @var \Spameri\Elastic\Factory\EntityFactory $entityFactory */
		$entityFactory = $this->container->getByType(\Spameri\Elastic\Factory\EntityFactory::class);
		/** @var \Spameri\Elastic\Model\ChangeSet $changeSet */
		$changeSet = $this->container->getByType(\Spameri\Elastic\Model\ChangeSet::class);

		$hit = new \Spameri\ElasticQuery\Response\Result\Hit(
			source: [
				'name' => 'Entity With Related',
				'related' => [
					'id-related-1',
				],
			],
			position: 0, index: '', type: '', id: 'lazy-elastic-cs', score: 0.0, version: 0,
		);

		$entity = $entityFactory->create(
			$hit,
			\SpameriTests\Elastic\Data\Entity\EntityWithElasticCollection::class,
			$entityManager,
		);

		// Collection itself should be marked existing eagerly, even though not initialized
		\Tester\Assert::true($changeSet->isExisting($entity->related));
		\Tester\Assert::false($entity->related->initialized());
	}


	public function testIdentityMapDoesNotTriggerElasticCollectionLazyInit(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);
		/** @var \Spameri\Elastic\Factory\EntityFactory $entityFactory */
		$entityFactory = $this->container->getByType(\Spameri\Elastic\Factory\EntityFactory::class);
		/** @var \Spameri\Elastic\Model\IdentityMap $identityMap */
		$identityMap = $this->container->getByType(\Spameri\Elastic\Model\IdentityMap::class);

		$hit = new \Spameri\ElasticQuery\Response\Result\Hit(
			source: [
				'name' => 'Identity Map Entity',
				'related' => [
					'id-related-1',
					'id-related-2',
				],
			],
			position: 0, index: '', type: '', id: 'lazy-elastic-idmap', score: 0.0, version: 0,
		);

		$entity = $entityFactory->create(
			$hit,
			\SpameriTests\Elastic\Data\Entity\EntityWithElasticCollection::class,
			$entityManager,
		);

		\Tester\Assert::false($entity->related->initialized());

		// markInserted calls getSerializedString which should use elasticIds()
		// instead of keys() for uninitialized ElasticEntityCollections
		$identityMap->markInserted($entity);

		// ElasticEntityCollection should still NOT be initialized
		\Tester\Assert::false($entity->related->initialized());

		// isChanged should also work without triggering initialization
		$result = $identityMap->isChanged($entity);
		\Tester\Assert::false($result);
		\Tester\Assert::false($entity->related->initialized());
	}

}

(new LazyLoadingTest())->run();
