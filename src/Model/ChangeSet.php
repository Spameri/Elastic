<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model;

class ChangeSet
{

	/**
	 * @var array<string, array<string, bool>>
	 */
	public array $created = [];


	public function markExisting(
		object $entity,
	): void
	{
		$this->created[$entity::class][\spl_object_hash($entity)] = true;
	}


	public function isExisting(
		object $entity,
	): bool
	{
		return isset($this->created[$entity::class][\spl_object_hash($entity)]);
	}

}
