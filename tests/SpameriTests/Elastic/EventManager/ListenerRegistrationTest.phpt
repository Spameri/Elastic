<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\EventManager;

require_once __DIR__ . '/../../../bootstrap-unit.php';

/**
 * @testCase
 */
class ListenerRegistrationTest extends \Tester\TestCase
{

	public function testAutoDiscoveryFromContainer(): void
	{
		$listener = new \SpameriTests\Elastic\Data\Listener\MockListener(
			[\SpameriTests\Elastic\Data\Entity\Title::class],
			\Spameri\Elastic\EventManager::POST_PERSIST,
		);

		$container = $this->createContainerWithListener($listener);
		$eventManager = new \Spameri\Elastic\EventManager($container);

		$entity = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\ElasticId('test-id'),
			null,
		);

		// Dispatch triggers auto-discovery (initListeners)
		$eventManager->dispatch(
			\Spameri\Elastic\EventManager::POST_PERSIST,
			\SpameriTests\Elastic\Data\Entity\Title::class,
			$entity,
			null,
		);

		\Tester\Assert::same(1, $listener->getCallCount());
	}


	public function testListenerReceivesCorrectEntityAndParent(): void
	{
		$listener = new \SpameriTests\Elastic\Data\Listener\MockListener(
			[\SpameriTests\Elastic\Data\Entity\Image::class],
			\Spameri\Elastic\EventManager::POST_PERSIST,
		);

		$container = $this->createContainerWithListener($listener);
		$eventManager = new \Spameri\Elastic\EventManager($container);

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

		\Tester\Assert::same($image, $listener->calls[0]['entity']);
		\Tester\Assert::same($parent, $listener->calls[0]['parent']);
	}


	public function testListenerForParentClassReceivesChildEntities(): void
	{
		$listener = new \SpameriTests\Elastic\Data\Listener\MockListener(
			[\Spameri\Elastic\Entity\AbstractElasticEntity::class],
			\Spameri\Elastic\EventManager::POST_PERSIST,
		);

		$container = $this->createContainerWithListener($listener);
		$eventManager = new \Spameri\Elastic\EventManager($container);

		$title = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\ElasticId('title-id'),
			null,
		);

		$eventManager->dispatch(
			\Spameri\Elastic\EventManager::POST_PERSIST,
			\SpameriTests\Elastic\Data\Entity\Title::class,
			$title,
			null,
		);

		// Parent listener should receive child entity
		\Tester\Assert::same(1, $listener->getCallCount());
		\Tester\Assert::same($title, $listener->calls[0]['entity']);
	}


	public function testListenerForInterfaceReceivesImplementingEntities(): void
	{
		$listener = new \SpameriTests\Elastic\Data\Listener\MockListener(
			[\Spameri\Elastic\Entity\ElasticEntityInterface::class],
			\Spameri\Elastic\EventManager::POST_PERSIST,
		);

		$container = $this->createContainerWithListener($listener);
		$eventManager = new \Spameri\Elastic\EventManager($container);

		$title = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\ElasticId('title-id'),
			null,
		);

		$eventManager->dispatch(
			\Spameri\Elastic\EventManager::POST_PERSIST,
			\SpameriTests\Elastic\Data\Entity\Title::class,
			$title,
			null,
		);

		// Interface listener should receive implementing entity
		\Tester\Assert::same(1, $listener->getCallCount());
	}


	public function testMultipleEntityClassesInSingleListener(): void
	{
		$listener = new \SpameriTests\Elastic\Data\Listener\MockListener(
			[
				\SpameriTests\Elastic\Data\Entity\Title::class,
				\SpameriTests\Elastic\Data\Entity\Image::class,
			],
			\Spameri\Elastic\EventManager::POST_PERSIST,
		);

		$container = $this->createContainerWithListener($listener);
		$eventManager = new \Spameri\Elastic\EventManager($container);

		$title = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\ElasticId('title-id'),
			null,
		);

		$image = new \SpameriTests\Elastic\Data\Entity\Image(
			new \Spameri\Elastic\Entity\Property\ElasticId('image-id'),
			null,
		);

		$eventManager->dispatch(
			\Spameri\Elastic\EventManager::POST_PERSIST,
			\SpameriTests\Elastic\Data\Entity\Title::class,
			$title,
			null,
		);

		$eventManager->dispatch(
			\Spameri\Elastic\EventManager::POST_PERSIST,
			\SpameriTests\Elastic\Data\Entity\Image::class,
			$image,
			null,
		);

		// Should be called twice (once for each class)
		\Tester\Assert::same(2, $listener->getCallCount());
	}


	public function testInitListenersOnlyCalledOnce(): void
	{
		$callCount = 0;

		$listener = new \SpameriTests\Elastic\Data\Listener\MockListener(
			[\SpameriTests\Elastic\Data\Entity\Title::class],
			\Spameri\Elastic\EventManager::POST_PERSIST,
		);

		$container = new class ($listener, $callCount) extends \Nette\DI\Container {
			private \SpameriTests\Elastic\Data\Listener\MockListener $listener;

			private int $callCount;


			public function __construct(
				\SpameriTests\Elastic\Data\Listener\MockListener $listener,
				int &$callCount,
			)
			{
				$this->listener = $listener;
				$this->callCount = &$callCount;
			}


			/**
			 * @return array<string>
			 */
			public function findByType(string $type): array
			{
				if ($type === \Spameri\Elastic\EventManager\ListenerInterface::class) {
					$this->callCount++;

					return ['testListener'];
				}

				return [];
			}


			public function getService(string $name): object
			{
				return $this->listener;
			}
		};

		$eventManager = new \Spameri\Elastic\EventManager($container);

		$entity = new \SpameriTests\Elastic\Data\Entity\Title(
			new \Spameri\Elastic\Entity\Property\ElasticId('test-id'),
			null,
		);

		// Multiple dispatches
		$eventManager->dispatch(
			\Spameri\Elastic\EventManager::POST_PERSIST,
			\SpameriTests\Elastic\Data\Entity\Title::class,
			$entity,
			null,
		);

		$eventManager->dispatch(
			\Spameri\Elastic\EventManager::POST_PERSIST,
			\SpameriTests\Elastic\Data\Entity\Title::class,
			$entity,
			null,
		);

		// findByType should only be called once (initialization is cached)
		\Tester\Assert::same(1, $callCount);
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

(new ListenerRegistrationTest())->run();
