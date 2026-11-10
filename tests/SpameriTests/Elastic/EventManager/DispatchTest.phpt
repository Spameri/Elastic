<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\EventManager;

require_once __DIR__ . '/../../../bootstrap-unit.php';

/**
 * @testCase
 */
class DispatchTest extends \Tester\TestCase
{

	public function testDispatchToRegisteredListener(): void
	{
		$container = $this->createMockContainer([]);
		$eventManager = new \Spameri\Elastic\EventManager($container);

		$listener = new \SpameriTests\Elastic\Data\Listener\MockListener(
			[\SpameriTests\Elastic\Data\Entity\Title::class],
			\Spameri\Elastic\EventManager::POST_PERSIST,
		);

		$eventManager->addListener(
			\Spameri\Elastic\EventManager::POST_PERSIST,
			\SpameriTests\Elastic\Data\Entity\Title::class,
			$listener,
		);

		$entity = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\ElasticId('test-id'),
			null,
		);

		$eventManager->dispatch(
			\Spameri\Elastic\EventManager::POST_PERSIST,
			\SpameriTests\Elastic\Data\Entity\Title::class,
			$entity,
			null,
		);

		\Tester\Assert::same(1, $listener->getCallCount());
		\Tester\Assert::same($entity, $listener->calls[0]['entity']);
		\Tester\Assert::null($listener->calls[0]['parent']);
	}


	public function testDispatchToMultipleListeners(): void
	{
		$container = $this->createMockContainer([]);
		$eventManager = new \Spameri\Elastic\EventManager($container);

		$listener1 = new \SpameriTests\Elastic\Data\Listener\MockListener(
			[\SpameriTests\Elastic\Data\Entity\Title::class],
			\Spameri\Elastic\EventManager::POST_PERSIST,
		);
		$listener2 = new \SpameriTests\Elastic\Data\Listener\MockListener(
			[\SpameriTests\Elastic\Data\Entity\Title::class],
			\Spameri\Elastic\EventManager::POST_PERSIST,
		);

		$eventManager->addListener(
			\Spameri\Elastic\EventManager::POST_PERSIST,
			\SpameriTests\Elastic\Data\Entity\Title::class,
			$listener1,
		);
		$eventManager->addListener(
			\Spameri\Elastic\EventManager::POST_PERSIST,
			\SpameriTests\Elastic\Data\Entity\Title::class,
			$listener2,
		);

		$entity = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\ElasticId('test-id'),
			null,
		);

		$eventManager->dispatch(
			\Spameri\Elastic\EventManager::POST_PERSIST,
			\SpameriTests\Elastic\Data\Entity\Title::class,
			$entity,
			null,
		);

		\Tester\Assert::same(1, $listener1->getCallCount());
		\Tester\Assert::same(1, $listener2->getCallCount());
	}


	public function testDispatchWithNoListenersDoesNotError(): void
	{
		$container = $this->createMockContainer([]);
		$eventManager = new \Spameri\Elastic\EventManager($container);

		$entity = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\ElasticId('test-id'),
			null,
		);

		// Should not throw
		$eventManager->dispatch(
			\Spameri\Elastic\EventManager::POST_PERSIST,
			\SpameriTests\Elastic\Data\Entity\Title::class,
			$entity,
			null,
		);

		\Tester\Assert::true(true); // No exception thrown
	}


	public function testDispatchAllEventTypes(): void
	{
		$container = $this->createMockContainer([]);
		$eventManager = new \Spameri\Elastic\EventManager($container);

		$events = [
			\Spameri\Elastic\EventManager::PRE_PERSIST,
			\Spameri\Elastic\EventManager::POST_PERSIST,
			\Spameri\Elastic\EventManager::POST_CREATE,
			\Spameri\Elastic\EventManager::POST_UPDATE,
			\Spameri\Elastic\EventManager::PRE_DELETE,
			\Spameri\Elastic\EventManager::POST_DELETE,
		];

		$listeners = [];

		foreach ($events as $event) {
			$listener = new \SpameriTests\Elastic\Data\Listener\MockListener(
				[\SpameriTests\Elastic\Data\Entity\Title::class],
				$event,
			);
			$eventManager->addListener(
				$event,
				\SpameriTests\Elastic\Data\Entity\Title::class,
				$listener,
			);
			$listeners[$event] = $listener;
		}

		$entity = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\ElasticId('test-id'),
			null,
		);

		foreach ($events as $event) {
			$eventManager->dispatch(
				$event,
				\SpameriTests\Elastic\Data\Entity\Title::class,
				$entity,
				null,
			);
		}

		foreach ($events as $event) {
			\Tester\Assert::same(1, $listeners[$event]->getCallCount(), "Event $event should have been called once");
		}
	}


	public function testDispatchWithParentEntity(): void
	{
		$container = $this->createMockContainer([]);
		$eventManager = new \Spameri\Elastic\EventManager($container);

		$listener = new \SpameriTests\Elastic\Data\Listener\MockListener(
			[\SpameriTests\Elastic\Data\Entity\Image::class],
			\Spameri\Elastic\EventManager::POST_PERSIST,
		);

		$eventManager->addListener(
			\Spameri\Elastic\EventManager::POST_PERSIST,
			\SpameriTests\Elastic\Data\Entity\Image::class,
			$listener,
		);

		$parent = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\ElasticId('title-id'),
			null,
		);

		$image = new \SpameriTests\Elastic\Data\Entity\Image(
			new \Spameri\Elastic\Entity\Property\ElasticId('image-id'),
			null,
		);

		$eventManager->dispatch(
			\Spameri\Elastic\EventManager::POST_PERSIST,
			\SpameriTests\Elastic\Data\Entity\Image::class,
			$image,
			$parent,
		);

		\Tester\Assert::same(1, $listener->getCallCount());
		\Tester\Assert::same($image, $listener->calls[0]['entity']);
		\Tester\Assert::same($parent, $listener->calls[0]['parent']);
	}


	public function testDispatchDoesNotCallListenerForDifferentClass(): void
	{
		$container = $this->createMockContainer([]);
		$eventManager = new \Spameri\Elastic\EventManager($container);

		$listener = new \SpameriTests\Elastic\Data\Listener\MockListener(
			[\SpameriTests\Elastic\Data\Entity\Title::class],
			\Spameri\Elastic\EventManager::POST_PERSIST,
		);

		$eventManager->addListener(
			\Spameri\Elastic\EventManager::POST_PERSIST,
			\SpameriTests\Elastic\Data\Entity\Title::class,
			$listener,
		);

		$image = new \SpameriTests\Elastic\Data\Entity\Image(
			new \Spameri\Elastic\Entity\Property\ElasticId('image-id'),
			null,
		);

		$eventManager->dispatch(
			\Spameri\Elastic\EventManager::POST_PERSIST,
			\SpameriTests\Elastic\Data\Entity\Image::class,
			$image,
			null,
		);

		\Tester\Assert::same(0, $listener->getCallCount());
	}


	public function testDispatchToParentClassListener(): void
	{
		$container = $this->createMockContainer([]);
		$eventManager = new \Spameri\Elastic\EventManager($container);

		// Register listener for parent class
		$listener = new \SpameriTests\Elastic\Data\Listener\MockListener(
			[\Spameri\Elastic\Entity\AbstractElasticEntity::class],
			\Spameri\Elastic\EventManager::POST_PERSIST,
		);

		$eventManager->addListener(
			\Spameri\Elastic\EventManager::POST_PERSIST,
			\Spameri\Elastic\Entity\AbstractElasticEntity::class,
			$listener,
		);

		$title = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\ElasticId('title-id'),
			null,
		);

		// Dispatch for child class
		$eventManager->dispatch(
			\Spameri\Elastic\EventManager::POST_PERSIST,
			\SpameriTests\Elastic\Data\Entity\Title::class,
			$title,
			null,
		);

		// Should be called because Title extends AbstractElasticEntity
		\Tester\Assert::same(1, $listener->getCallCount());
	}


	public function testDispatchToInterfaceListener(): void
	{
		$container = $this->createMockContainer([]);
		$eventManager = new \Spameri\Elastic\EventManager($container);

		// Register listener for interface
		$listener = new \SpameriTests\Elastic\Data\Listener\MockListener(
			[\Spameri\Elastic\Entity\ElasticEntityInterface::class],
			\Spameri\Elastic\EventManager::POST_PERSIST,
		);

		$eventManager->addListener(
			\Spameri\Elastic\EventManager::POST_PERSIST,
			\Spameri\Elastic\Entity\ElasticEntityInterface::class,
			$listener,
		);

		$title = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\ElasticId('title-id'),
			null,
		);

		// Dispatch for class implementing the interface
		$eventManager->dispatch(
			\Spameri\Elastic\EventManager::POST_PERSIST,
			\SpameriTests\Elastic\Data\Entity\Title::class,
			$title,
			null,
		);

		// Should be called because Title implements ElasticEntityInterface
		\Tester\Assert::same(1, $listener->getCallCount());
	}


	/**
	 * @param array<string> $listenerServiceNames
	 */
	private function createMockContainer(array $listenerServiceNames): \Nette\DI\Container
	{
		$container = new class ($listenerServiceNames) extends \Nette\DI\Container {
			/**
			 * @var array<string>
			 */
			private array $listenerServiceNames;

			/**
			 * @param array<string> $listenerServiceNames
			 */
			public function __construct(array $listenerServiceNames)
			{
				$this->listenerServiceNames = $listenerServiceNames;
			}

			/**
			 * @return array<string>
			 */
			public function findByType(string $type): array
			{
				if ($type === \Spameri\Elastic\EventManager\ListenerInterface::class) {
					return $this->listenerServiceNames;
				}

				return [];
			}
		};

		return $container;
	}

}

(new DispatchTest())->run();
