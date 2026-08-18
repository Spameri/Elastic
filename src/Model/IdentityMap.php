<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model;

class IdentityMap
{

	/**
	 * @var array<class-string, array<string, \Spameri\Elastic\Entity\AbstractElasticEntity>>
	 */
	public array $identityMap = [];

	/**
	 * @var array<class-string, array<string, string>>
	 */
	public array $persisted = [];

	/**
	 * @var array<class-string, array<string, bool>>
	 */
	public array $creatingEntityList = [];

	/**
	 * entity that is missing, its id, propertyName where it is missing, entityId, entityClass that has missing entity in property
	 *
	 * @var array<class-string, array<string, array<string, array<string, class-string>>>>
	 */
	public array $uninitializedEntityList = [];

	/**
	 * entities currently being persisted (write side) - used to break circular references
	 *
	 * @var array<class-string, array<string, bool>>
	 */
	public array $persistingList = [];


	public function add(
		\Spameri\Elastic\Entity\AbstractElasticEntity $entity,
	): void
	{
		if ($entity->id instanceof \Spameri\Elastic\Entity\Property\EmptyElasticId) {
			return;
		}

		$this->identityMap[$entity::class][$entity->id()->value()] = $entity;

		/** @var string|false $parentClass */
		$parentClass = \get_parent_class($entity);
		if (
			\is_string($parentClass) === true
			&& $parentClass !== \Spameri\Elastic\Entity\AbstractElasticEntity::class
		) {
			$this->identityMap[$parentClass][$entity->id()->value()] = $entity;
		}
	}


	public function get(
		string $class,
		string|int $id,
	): \Spameri\Elastic\Entity\AbstractElasticEntity|null
	{
		return $this->identityMap[$class][$id] ?? null;
	}


	public function remove(
		\Spameri\Elastic\Entity\AbstractElasticEntity $entity,
	): void
	{
		if ($entity->id instanceof \Spameri\Elastic\Entity\Property\EmptyElasticId) {
			return;
		}

		unset($this->identityMap[$entity::class][$entity->id()->value()]);
		unset($this->persisted[$entity::class][$entity->id()->value()]);

		/** @var string|false $parentClass */
		$parentClass = \get_parent_class($entity);
		if (
			\is_string($parentClass) === true
			&& $parentClass !== \Spameri\Elastic\Entity\AbstractElasticEntity::class
		) {
			unset($this->identityMap[$parentClass][$entity->id()->value()]);
		}
	}


	public function markInserted(
		\Spameri\Elastic\Entity\AbstractElasticEntity $entity,
	): void
	{
		$this->add($entity);

		$this->persisted[$entity::class][$entity->id()->value()] = $this->getSerializedString($entity);
	}


	public function markPersisting(
		\Spameri\Elastic\Entity\AbstractElasticEntity $entity,
	): void
	{
		if ($entity->id instanceof \Spameri\Elastic\Entity\Property\EmptyElasticId) {
			return;
		}

		$this->persistingList[$entity::class][$entity->id()->value()] = true;
	}


	public function unmarkPersisting(
		\Spameri\Elastic\Entity\AbstractElasticEntity $entity,
	): void
	{
		if ($entity->id instanceof \Spameri\Elastic\Entity\Property\EmptyElasticId) {
			return;
		}

		unset($this->persistingList[$entity::class][$entity->id()->value()]);
	}


	public function isPersisting(
		\Spameri\Elastic\Entity\AbstractElasticEntity $entity,
	): bool
	{
		if ($entity->id instanceof \Spameri\Elastic\Entity\Property\EmptyElasticId) {
			return false;
		}

		return isset($this->persistingList[$entity::class][$entity->id()->value()]);
	}


	public function isChanged(
		\Spameri\Elastic\Entity\AbstractElasticEntity $entity,
	): bool
	{
		if (isset($this->persisted[$entity::class][$entity->id()->value()]) === false) {
			return true;
		}

		$hash = $this->getSerializedString($entity);

		return $this->persisted[$entity::class][$entity->id()->value()] !== $hash;
	}


	private function getSerializedString(\Spameri\Elastic\Entity\AbstractElasticEntity $entity): string
	{
		$serializedArray = $entity->entityVariables();

		foreach ($serializedArray as $key => $item) {
			if ($item instanceof \Spameri\Elastic\Entity\ElasticEntityInterface) {
				$serializedArray[$key] = $item->id()->value();
			} elseif ($item instanceof \Spameri\Elastic\Entity\ElasticEntityCollectionInterface) {
				$serializedArray[$key] = $item->initialized() ? $item->keys() : $item->elasticIds();
			} elseif ($item instanceof \Spameri\Elastic\Entity\EntityCollectionInterface) {
				$serializedArray[$key] = $item->keys();
			}
		}

		return \md5(\serialize($serializedArray));
	}


	public function clear(): void
	{
		$this->identityMap = [];
		$this->persisted = [];
		$this->creatingEntityList = [];
		$this->uninitializedEntityList = [];
		$this->persistingList = [];
	}

}
