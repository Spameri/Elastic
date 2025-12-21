<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Factory\EntityFactory;

require_once __DIR__ . '/../../../../bootstrap.php';

/**
 * @testCase
 */
class ValueObjectTest extends \SpameriTests\Elastic\AbstractTestCase
{

	public function testHydrateValueInterfaceProperties(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);
		/** @var \Spameri\Elastic\Factory\EntityFactory $entityFactory */
		$entityFactory = $this->container->getByType(\Spameri\Elastic\Factory\EntityFactory::class);

		$hit = new \Spameri\ElasticQuery\Response\Result\Hit(
			source: [
				'stringValue' => 'test string',
				'intValue' => 42,
				'boolValue' => true,
			],
			position: 0, index: '', type: '', id: 'vo-1', score: 0.0, version: 0,
		);

		$entity = $entityFactory->create(
			$hit,
			\SpameriTests\Elastic\Data\Entity\EntityWithValueObjects::class,
			$entityManager,
		);

		\Tester\Assert::type(\Spameri\Elastic\Entity\Value\StringValue::class, $entity->stringValue);
		\Tester\Assert::same('test string', $entity->stringValue->value());

		\Tester\Assert::type(\Spameri\Elastic\Entity\Value\IntegerValue::class, $entity->intValue);
		\Tester\Assert::same(42, $entity->intValue->value());

		\Tester\Assert::type(\Spameri\Elastic\Entity\Value\BoolValue::class, $entity->boolValue);
		\Tester\Assert::true($entity->boolValue->value());
	}


	public function testHydrateElasticIdCreation(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);
		/** @var \Spameri\Elastic\Factory\EntityFactory $entityFactory */
		$entityFactory = $this->container->getByType(\Spameri\Elastic\Factory\EntityFactory::class);

		$hit = new \Spameri\ElasticQuery\Response\Result\Hit(
			source: [
				'stringValue' => 'test',
				'intValue' => 1,
				'boolValue' => false,
			],
			position: 0, index: '', type: '', id: 'elastic-id-abc123', score: 0.0, version: 0,
		);

		$entity = $entityFactory->create(
			$hit,
			\SpameriTests\Elastic\Data\Entity\EntityWithValueObjects::class,
			$entityManager,
		);

		\Tester\Assert::type(\Spameri\Elastic\Entity\Property\ElasticId::class, $entity->id());
		\Tester\Assert::same('elastic-id-abc123', $entity->id()->value());
		// ElasticId throws on empty string, so non-empty value is guaranteed
		\Tester\Assert::notSame('', $entity->id()->value());
	}


	public function testHydrateDateHandling(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);
		/** @var \Spameri\Elastic\Factory\EntityFactory $entityFactory */
		$entityFactory = $this->container->getByType(\Spameri\Elastic\Factory\EntityFactory::class);

		$hit = new \Spameri\ElasticQuery\Response\Result\Hit(
			source: [
				'stringValue' => 'test',
				'intValue' => 1,
				'boolValue' => false,
				'dateValue' => '2024-06-15',
			],
			position: 0, index: '', type: '', id: 'date-1', score: 0.0, version: 0,
		);

		$entity = $entityFactory->create(
			$hit,
			\SpameriTests\Elastic\Data\Entity\EntityWithValueObjects::class,
			$entityManager,
		);

		\Tester\Assert::type(\Spameri\Elastic\Entity\Property\Date::class, $entity->dateValue);
		\Tester\Assert::same('2024-06-15', $entity->dateValue->format('Y-m-d'));
	}


	public function testHydrateDateTimeHandling(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);
		/** @var \Spameri\Elastic\Factory\EntityFactory $entityFactory */
		$entityFactory = $this->container->getByType(\Spameri\Elastic\Factory\EntityFactory::class);

		$hit = new \Spameri\ElasticQuery\Response\Result\Hit(
			source: [
				'stringValue' => 'test',
				'intValue' => 1,
				'boolValue' => false,
				'dateTimeValue' => '2024-06-15T14:30:00',
			],
			position: 0, index: '', type: '', id: 'datetime-1', score: 0.0, version: 0,
		);

		$entity = $entityFactory->create(
			$hit,
			\SpameriTests\Elastic\Data\Entity\EntityWithValueObjects::class,
			$entityManager,
		);

		\Tester\Assert::type(\Spameri\Elastic\Entity\Property\DateTime::class, $entity->dateTimeValue);
		\Tester\Assert::same('2024-06-15', $entity->dateTimeValue->format('Y-m-d'));
		\Tester\Assert::same('14:30:00', $entity->dateTimeValue->format('H:i:s'));
	}


	public function testHydrateNullDateValue(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);
		/** @var \Spameri\Elastic\Factory\EntityFactory $entityFactory */
		$entityFactory = $this->container->getByType(\Spameri\Elastic\Factory\EntityFactory::class);

		$hit = new \Spameri\ElasticQuery\Response\Result\Hit(
			source: [
				'stringValue' => 'test',
				'intValue' => 1,
				'boolValue' => false,
				'dateValue' => null,
				'dateTimeValue' => null,
			],
			position: 0, index: '', type: '', id: 'nulldate-1', score: 0.0, version: 0,
		);

		$entity = $entityFactory->create(
			$hit,
			\SpameriTests\Elastic\Data\Entity\EntityWithValueObjects::class,
			$entityManager,
		);

		\Tester\Assert::null($entity->dateValue);
		\Tester\Assert::null($entity->dateTimeValue);
	}


	public function testValueObjectsAreMarkedExisting(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);
		/** @var \Spameri\Elastic\Factory\EntityFactory $entityFactory */
		$entityFactory = $this->container->getByType(\Spameri\Elastic\Factory\EntityFactory::class);
		/** @var \Spameri\Elastic\Model\ChangeSet $changeSet */
		$changeSet = $this->container->getByType(\Spameri\Elastic\Model\ChangeSet::class);

		$hit = new \Spameri\ElasticQuery\Response\Result\Hit(
			source: [
				'stringValue' => 'test',
				'intValue' => 1,
				'boolValue' => false,
				'dateValue' => '2024-01-01',
			],
			position: 0, index: '', type: '', id: 'changeset-1', score: 0.0, version: 0,
		);

		$entity = $entityFactory->create(
			$hit,
			\SpameriTests\Elastic\Data\Entity\EntityWithValueObjects::class,
			$entityManager,
		);

		// Value objects should be marked as existing by the factory
		\Tester\Assert::true($changeSet->isExisting($entity->stringValue));
		\Tester\Assert::true($changeSet->isExisting($entity->intValue));
		\Tester\Assert::true($changeSet->isExisting($entity->boolValue));
		\Tester\Assert::true($changeSet->isExisting($entity->dateValue));
	}

}

(new ValueObjectTest())->run();
