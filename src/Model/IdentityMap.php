<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model;

class IdentityMap
{

	/**
	 * @var array<string, array<string, \Spameri\Elastic\Entity\AbstractElasticEntity>>
	 */
	public array $identityMap = [];

	/**
	 * @var array<string, array<string, string>>
	 */
	public array $persisted = [];


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
		if (\is_string($parentClass) === true) {
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


	public function markInserted(
		\Spameri\Elastic\Entity\AbstractElasticEntity $entity,
	): void
	{
		$this->add($entity);

		$this->persisted[$entity::class][$entity->id()->value()] = \md5(\serialize($entity->entityVariables()));
	}


	public function isChanged(
		\Spameri\Elastic\Entity\AbstractElasticEntity $entity,
	): bool
	{
		if (isset($this->persisted[$entity::class][$entity->id()->value()]) === false) {
			return true;
		}

		$hash = \md5(\serialize($entity->entityVariables()));

		return $this->persisted[$entity::class][$entity->id()->value()] !== $hash;
	}

}
