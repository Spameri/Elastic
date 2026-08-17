<?php declare(strict_types = 1);

namespace Spameri\Elastic\Entity;

/**
 * @template-covariant T of \Spameri\Elastic\Entity\EntityInterface
 * @template-extends \IteratorAggregate<T>
 */
interface EntityCollectionInterface extends \IteratorAggregate
{

	public function add(
		\Spameri\Elastic\Entity\EntityInterface $entity,
	): void;


	public function entity(
		string $key,
	): \Spameri\Elastic\Entity\EntityInterface|null;


	public function remove(
		string|int $key,
	): void;


	public function isValue(
		string $key,
	): bool;


	public function count(): int;


	/**
	 * @return array<string>
	 */
	public function keys(): array;


	public function clear(): void;


	public function sort(
		\Spameri\Elastic\Entity\Collection\SortField $sortField,
		string $type,
	): void;

}
