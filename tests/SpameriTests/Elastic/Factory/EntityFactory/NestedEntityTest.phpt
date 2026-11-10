<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Factory\EntityFactory;

require_once __DIR__ . '/../../../../bootstrap.php';

/**
 * @testCase
 */
class NestedEntityTest extends \SpameriTests\Elastic\AbstractTestCase
{

	public function testHydrateEntityWithNestedObject(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);
		/** @var \Spameri\Elastic\Factory\EntityFactory $entityFactory */
		$entityFactory = $this->container->getByType(\Spameri\Elastic\Factory\EntityFactory::class);

		$hit = new \Spameri\ElasticQuery\Response\Result\Hit(
			source: [
				'name' => 'Parent Entity',
				'nested' => [
					'title' => 'Nested Title',
					'order' => 5,
				],
			],
			position: 0, index: '', type: '', id: 'nested-1', score: 0.0, version: 0,
		);

		$entity = $entityFactory->create(
			$hit,
			\SpameriTests\Elastic\Data\Entity\EntityWithNested::class,
			$entityManager,
		);

		\Tester\Assert::type(\SpameriTests\Elastic\Data\Entity\EntityWithNested::class, $entity);
		\Tester\Assert::same('Parent Entity', $entity->name);

		\Tester\Assert::type(\SpameriTests\Elastic\Data\Entity\NestedObject::class, $entity->nested);
		\Tester\Assert::same('Nested Title', $entity->nested->title);
		\Tester\Assert::same(5, $entity->nested->order);
	}


	public function testHydrateEntityWithNullNestedObject(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);
		/** @var \Spameri\Elastic\Factory\EntityFactory $entityFactory */
		$entityFactory = $this->container->getByType(\Spameri\Elastic\Factory\EntityFactory::class);

		$hit = new \Spameri\ElasticQuery\Response\Result\Hit(
			source: [
				'name' => 'Parent Entity',
				'nested' => null,
			],
			position: 0, index: '', type: '', id: 'nested-null', score: 0.0, version: 0,
		);

		$entity = $entityFactory->create(
			$hit,
			\SpameriTests\Elastic\Data\Entity\EntityWithNested::class,
			$entityManager,
		);

		\Tester\Assert::same('Parent Entity', $entity->name);
		\Tester\Assert::null($entity->nested);
	}


	public function testHydrateNestedObjectIsMarkedExisting(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);
		/** @var \Spameri\Elastic\Factory\EntityFactory $entityFactory */
		$entityFactory = $this->container->getByType(\Spameri\Elastic\Factory\EntityFactory::class);
		/** @var \Spameri\Elastic\Model\ChangeSet $changeSet */
		$changeSet = $this->container->getByType(\Spameri\Elastic\Model\ChangeSet::class);

		$hit = new \Spameri\ElasticQuery\Response\Result\Hit(
			source: [
				'name' => 'Parent Entity',
				'nested' => [
					'title' => 'Nested',
					'order' => 1,
				],
			],
			position: 0, index: '', type: '', id: 'nested-cs', score: 0.0, version: 0,
		);

		$entity = $entityFactory->create(
			$hit,
			\SpameriTests\Elastic\Data\Entity\EntityWithNested::class,
			$entityManager,
		);

		// Nested object should be marked as existing
		\Tester\Assert::true($changeSet->isExisting($entity->nested));
	}


	public function testHydrateMultipleLevelsOfNesting(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);
		/** @var \Spameri\Elastic\Factory\EntityFactory $entityFactory */
		$entityFactory = $this->container->getByType(\Spameri\Elastic\Factory\EntityFactory::class);

		// Create multi-level nested structure using EntityWithNested
		// where nested also contains nested objects
		$hit = new \Spameri\ElasticQuery\Response\Result\Hit(
			source: [
				'name' => 'Top Level Entity',
				'nested' => [
					'title' => 'Middle Level',
					'order' => 1,
				],
			],
			position: 0, index: '', type: '', id: 'multi-level-1', score: 0.0, version: 0,
		);

		$entity = $entityFactory->create(
			$hit,
			\SpameriTests\Elastic\Data\Entity\EntityWithNested::class,
			$entityManager,
		);

		\Tester\Assert::type(\SpameriTests\Elastic\Data\Entity\EntityWithNested::class, $entity);
		\Tester\Assert::type(\SpameriTests\Elastic\Data\Entity\NestedObject::class, $entity->nested);
		\Tester\Assert::same('Middle Level', $entity->nested->title);
		\Tester\Assert::same(1, $entity->nested->order);
	}

}

(new NestedEntityTest())->run();
