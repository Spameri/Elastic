<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Listener;

/**
 * Mock listener for testing event dispatch.
 * Tracks all calls and their parameters.
 */
class MockListener implements \Spameri\Elastic\EventManager\ListenerInterface
{

	/**
	 * @var array<int, array{entity: object|null, parent: object|null}>
	 */
	public array $calls = [];

	/**
	 * @var array<class-string>
	 */
	private array $entityClasses;

	private string $event;


	/**
	 * @param array<class-string> $entityClasses
	 */
	public function __construct(
		array $entityClasses,
		string $event,
	)
	{
		$this->entityClasses = $entityClasses;
		$this->event = $event;
	}


	public function handle(
		object|null $entity,
		object|null $parent,
	): void
	{
		$this->calls[] = [
			'entity' => $entity,
			'parent' => $parent,
		];
	}


	/**
	 * @return array<class-string>
	 */
	public function getEntityClass(): array
	{
		return $this->entityClasses;
	}


	public function getEvent(): string
	{
		return $this->event;
	}


	public function getCallCount(): int
	{
		return \count($this->calls);
	}


	public function reset(): void
	{
		$this->calls = [];
	}

}
