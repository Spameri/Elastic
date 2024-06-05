<?php declare(strict_types = 1);

namespace Spameri\Elastic;

class EventManager
{

	public const PRE_PERSIST = 'prePersist';
	public const POST_PERSIST = 'postPersist';

	public const POST_CREATE = 'postCreate';
	public const POST_UPDATE = 'postUpdate';

	public const PRE_DELETE = 'preDelete';
	public const POST_DELETE = 'postDelete';

	/**
	 * @var array<string, array<string, array<\Spameri\Elastic\EventManager\ListenerInterface>>>
	 */
	private array $listeners;

	private bool $initialized = false;


	public function __construct(
		private readonly \Nette\DI\Container $container,
	)
	{
		$this->listeners[self::PRE_PERSIST] = [];
		$this->listeners[self::POST_PERSIST] = [];
		$this->listeners[self::POST_CREATE] = [];
		$this->listeners[self::POST_UPDATE] = [];
		$this->listeners[self::PRE_DELETE] = [];
		$this->listeners[self::POST_DELETE] = [];
	}


	public function addListener(
		string $event,
		string|null $entityClass,
		\Spameri\Elastic\EventManager\ListenerInterface $listener,
	): void
	{
		$this->listeners[$event][$entityClass][] = $listener;
	}

	private function initListeners(): void
	{
		if ($this->initialized === true) {
			return;
		}

		$listeners = $this->container->findByType(\Spameri\Elastic\EventManager\ListenerInterface::class);

		foreach ($listeners as $listenerName) {
			/** @var \Spameri\Elastic\EventManager\ListenerInterface $listener */
			$listener = $this->container->getService($listenerName);
			foreach ($listener->getEntityClass() as $entityClass) {
				$this->addListener(
					event: $listener->getEvent(),
					entityClass: $entityClass,
					listener: $listener,
				);
			}
		}

		$this->initialized = true;
	}

	public function dispatch(
		string $event,
		string $entityClass,
		object|null $entity = null,
		object|null $parent = null,
	): void
	{
		$this->initListeners();

		foreach ($this->listeners[$event] as $listenerEntityClass => $listeners) {
			if (
				$listenerEntityClass !== ''
				&& \is_a($entityClass, $listenerEntityClass, true) === false
			) {
				continue;
			}

			/** @var \Spameri\Elastic\EventManager\ListenerInterface $listener */
			foreach ($listeners as $listener) {
				try {
					$listener->handle($entity, $parent);

				} catch (\Throwable $exception) {
					// TODO Log
					\Tracy\Debugger::barDump($exception->getMessage());
				}
			}
		}
	}

}
