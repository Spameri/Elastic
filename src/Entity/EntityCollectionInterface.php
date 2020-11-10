<?php declare(strict_types = 1);

namespace Spameri\Elastic\Entity;


interface EntityCollectionInterface extends \IteratorAggregate
{

	public function add(
		\Spameri\Elastic\Entity\EntityInterface $entity
	): void;


	public function entity(
		string $key
	): ?\Spameri\Elastic\Entity\EntityInterface;


	public function remove(
		string $key
	): void;


	public function isValue(
		string $key
	): bool;


	public function count(): int;


	public function keys(): array;


	public function clear(): void;


	public function sort(
		\Spameri\Elastic\Entity\Collection\SortField $sortField,
		string $type
	): void;

}
