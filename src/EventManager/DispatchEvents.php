<?php declare(strict_types = 1);

namespace Spameri\Elastic\EventManager;

readonly class DispatchEvents
{

	public function __construct(
		private \Spameri\Elastic\EventManager $eventManager,
		private \Spameri\Elastic\Model\ChangeSet $changeSet,
	)
	{
	}


	public function execute(
		\Spameri\Elastic\Entity\AbstractElasticEntity $entity,
		string $eventType,
	): void
	{
		$this->iterateVariables(
			variables: $entity->entityVariables(),
			eventType: $eventType,
			parent: $entity,
		);
	}


	public function iterateVariables(
		array $variables,
		string $eventType,
		\Spameri\Elastic\Entity\AbstractElasticEntity|\Spameri\Elastic\Entity\EntityInterface $parent,
	): void
	{
		foreach ($variables as $property) {
			if ($property instanceof \Spameri\Elastic\Entity\EntityInterface) {
				$this->dispatch($eventType, $property, $parent);

			} elseif ($property instanceof \Spameri\Elastic\Entity\EntityCollectionInterface) {
				/** @var \Spameri\Elastic\Entity\EntityInterface $item */
				foreach ($property as $item) {
					$this->dispatch($eventType, $item, $parent);
				}
			}
		}

	}


	public function dispatch(
		string $eventType,
		\Spameri\Elastic\Entity\EntityInterface $property,
		\Spameri\Elastic\Entity\AbstractElasticEntity|\Spameri\Elastic\Entity\EntityInterface $parent,
	): void
	{
		if (
			$eventType !== \Spameri\Elastic\EventManager::POST_CREATE
		) {
			$this->eventManager->dispatch(
				event: $eventType,
				entityClass: $property::class,
				entity: $property,
				parent: $parent,
			);

		} elseif (
			$this->changeSet->isExisting($property) === false
		) {
			$this->eventManager->dispatch(
				event: $eventType,
				entityClass: $property::class,
				entity: $property,
				parent: $parent,
			);
		}

		$this->iterateVariables($property->entityVariables(), $eventType, $property);
	}

}
