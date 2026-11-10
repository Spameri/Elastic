<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\EventManager;

require_once __DIR__ . '/../../../bootstrap-unit.php';

/**
 * @testCase
 */
class DispatchEventsTest extends \Tester\TestCase
{

	public function testDispatchThroughNestedEntities(): void
	{
		$listener = new \SpameriTests\Elastic\Data\Listener\MockListener(
			[\SpameriTests\Elastic\Data\Entity\SimpleNestedEntity::class],
			\Spameri\Elastic\EventManager::POST_PERSIST,
		);

		$container = $this->createContainerWithListener($listener);
		$eventManager = new \Spameri\Elastic\EventManager($container);
		$changeSet = new \Spameri\Elastic\Model\ChangeSet();
		$dispatchEvents = new \Spameri\Elastic\EventManager\DispatchEvents($eventManager, $changeSet);

		$nested = new \SpameriTests\Elastic\Data\Entity\SimpleNestedEntity('nested-1', 'Nested Item');
		$entity = new \SpameriTests\Elastic\Data\Entity\EntityWithNestedContent(
			new \Spameri\Elastic\Entity\Property\ElasticId('test-id'),
			$nested,
			null,
		);

		$dispatchEvents->execute($entity, \Spameri\Elastic\EventManager::POST_PERSIST);

		// Nested entity should have received the event
		\Tester\Assert::same(1, $listener->getCallCount());
		\Tester\Assert::same($nested, $listener->calls[0]['entity']);
		\Tester\Assert::same($entity, $listener->calls[0]['parent']);
	}


	public function testDispatchThroughCollections(): void
	{
		$listener = new \SpameriTests\Elastic\Data\Listener\MockListener(
			[\SpameriTests\Elastic\Data\Entity\SimpleNestedEntity::class],
			\Spameri\Elastic\EventManager::POST_PERSIST,
		);

		$container = $this->createContainerWithListener($listener);
		$eventManager = new \Spameri\Elastic\EventManager($container);
		$changeSet = new \Spameri\Elastic\Model\ChangeSet();
		$dispatchEvents = new \Spameri\Elastic\EventManager\DispatchEvents($eventManager, $changeSet);

		$item1 = new \SpameriTests\Elastic\Data\Entity\SimpleNestedEntity('item-1', 'Item One');
		$item2 = new \SpameriTests\Elastic\Data\Entity\SimpleNestedEntity('item-2', 'Item Two');
		$item3 = new \SpameriTests\Elastic\Data\Entity\SimpleNestedEntity('item-3', 'Item Three');

		$collection = new \Spameri\Elastic\Entity\Collection\EntityCollection($item1, $item2, $item3);

		$entity = new \SpameriTests\Elastic\Data\Entity\EntityWithNestedContent(
			new \Spameri\Elastic\Entity\Property\ElasticId('test-id'),
			null,
			$collection,
		);

		$dispatchEvents->execute($entity, \Spameri\Elastic\EventManager::POST_PERSIST);

		// All collection items should have received events
		\Tester\Assert::same(3, $listener->getCallCount());
	}


	public function testPostCreateOnlyFiresForNewEntities(): void
	{
		$listener = new \SpameriTests\Elastic\Data\Listener\MockListener(
			[\SpameriTests\Elastic\Data\Entity\SimpleNestedEntity::class],
			\Spameri\Elastic\EventManager::POST_CREATE,
		);

		$container = $this->createContainerWithListener($listener);
		$eventManager = new \Spameri\Elastic\EventManager($container);
		$changeSet = new \Spameri\Elastic\Model\ChangeSet();
		$dispatchEvents = new \Spameri\Elastic\EventManager\DispatchEvents($eventManager, $changeSet);

		$existingNested = new \SpameriTests\Elastic\Data\Entity\SimpleNestedEntity('existing', 'Existing Item');
		$newNested = new \SpameriTests\Elastic\Data\Entity\SimpleNestedEntity('new', 'New Item');

		// Mark one as existing
		$changeSet->markExisting($existingNested);

		$collection = new \Spameri\Elastic\Entity\Collection\EntityCollection($existingNested, $newNested);

		$entity = new \SpameriTests\Elastic\Data\Entity\EntityWithNestedContent(
			new \Spameri\Elastic\Entity\Property\ElasticId('test-id'),
			null,
			$collection,
		);

		$dispatchEvents->execute($entity, \Spameri\Elastic\EventManager::POST_CREATE);

		// Only new entity should receive POST_CREATE
		\Tester\Assert::same(1, $listener->getCallCount());
		\Tester\Assert::same($newNested, $listener->calls[0]['entity']);
	}


	public function testParentPassedCorrectlyThroughTree(): void
	{
		$listener = new \SpameriTests\Elastic\Data\Listener\MockListener(
			[\SpameriTests\Elastic\Data\Entity\SimpleNestedEntity::class],
			\Spameri\Elastic\EventManager::POST_PERSIST,
		);

		$container = $this->createContainerWithListener($listener);
		$eventManager = new \Spameri\Elastic\EventManager($container);
		$changeSet = new \Spameri\Elastic\Model\ChangeSet();
		$dispatchEvents = new \Spameri\Elastic\EventManager\DispatchEvents($eventManager, $changeSet);

		$nested = new \SpameriTests\Elastic\Data\Entity\SimpleNestedEntity('nested', 'Nested');
		$entity = new \SpameriTests\Elastic\Data\Entity\EntityWithNestedContent(
			new \Spameri\Elastic\Entity\Property\ElasticId('test-id'),
			$nested,
			null,
		);

		$dispatchEvents->execute($entity, \Spameri\Elastic\EventManager::POST_PERSIST);

		// Parent should be the containing entity
		\Tester\Assert::same($entity, $listener->calls[0]['parent']);
	}


	public function testNoDispatchForEmptyEntity(): void
	{
		$listener = new \SpameriTests\Elastic\Data\Listener\MockListener(
			[\SpameriTests\Elastic\Data\Entity\SimpleNestedEntity::class],
			\Spameri\Elastic\EventManager::POST_PERSIST,
		);

		$container = $this->createContainerWithListener($listener);
		$eventManager = new \Spameri\Elastic\EventManager($container);
		$changeSet = new \Spameri\Elastic\Model\ChangeSet();
		$dispatchEvents = new \Spameri\Elastic\EventManager\DispatchEvents($eventManager, $changeSet);

		// Entity with no nested content
		$entity = new \SpameriTests\Elastic\Data\Entity\EntityWithNestedContent(
			new \Spameri\Elastic\Entity\Property\ElasticId('test-id'),
			null,
			null,
		);

		$dispatchEvents->execute($entity, \Spameri\Elastic\EventManager::POST_PERSIST);

		// No nested entities, no dispatch
		\Tester\Assert::same(0, $listener->getCallCount());
	}


	public function testDispatchForAllEventTypes(): void
	{
		$events = [
			\Spameri\Elastic\EventManager::PRE_PERSIST,
			\Spameri\Elastic\EventManager::POST_PERSIST,
			\Spameri\Elastic\EventManager::PRE_DELETE,
			\Spameri\Elastic\EventManager::POST_DELETE,
			\Spameri\Elastic\EventManager::POST_UPDATE,
		];

		foreach ($events as $eventType) {
			$listener = new \SpameriTests\Elastic\Data\Listener\MockListener(
				[\SpameriTests\Elastic\Data\Entity\SimpleNestedEntity::class],
				$eventType,
			);

			$container = $this->createContainerWithListener($listener);
			$eventManager = new \Spameri\Elastic\EventManager($container);
			$changeSet = new \Spameri\Elastic\Model\ChangeSet();
			$dispatchEvents = new \Spameri\Elastic\EventManager\DispatchEvents($eventManager, $changeSet);

			$nested = new \SpameriTests\Elastic\Data\Entity\SimpleNestedEntity('nested', 'Nested');
			$entity = new \SpameriTests\Elastic\Data\Entity\EntityWithNestedContent(
				new \Spameri\Elastic\Entity\Property\ElasticId('test-id'),
				$nested,
				null,
			);

			$dispatchEvents->execute($entity, $eventType);

			\Tester\Assert::same(1, $listener->getCallCount(), "Event $eventType should dispatch to nested entities");
		}
	}


	private function createContainerWithListener(
		\Spameri\Elastic\EventManager\ListenerInterface $listener,
	): \Nette\DI\Container
	{
		return new class ($listener) extends \Nette\DI\Container {
			private \Spameri\Elastic\EventManager\ListenerInterface $listener;


			public function __construct(\Spameri\Elastic\EventManager\ListenerInterface $listener)
			{
				$this->listener = $listener;
			}


			/**
			 * @return array<string>
			 */
			public function findByType(string $type): array
			{
				if ($type === \Spameri\Elastic\EventManager\ListenerInterface::class) {
					return ['testListener'];
				}

				return [];
			}


			public function getService(string $name): object
			{
				return $this->listener;
			}
		};
	}

}

(new DispatchEventsTest())->run();
