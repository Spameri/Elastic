<?php declare(strict_types = 1);

namespace Spameri\Elastic\Entity;

/**
 * @template-covariant T of \Spameri\Elastic\Entity\AbstractElasticEntity
 * @template-extends \IteratorAggregate<T>
 */
interface ElasticEntityCollectionInterface extends \IteratorAggregate
{

	public function add(
		\Spameri\Elastic\Entity\AbstractElasticEntity $elasticEntity,
	): void;


	public function entity(
		\Spameri\Elastic\Entity\Property\ElasticIdInterface $id,
	): \Spameri\Elastic\Entity\AbstractElasticEntity|null;

	/**
	 * @return T|null
	 */
	public function first(): \Spameri\Elastic\Entity\AbstractElasticEntity|null;

	public function remove(
		\Spameri\Elastic\Entity\Property\ElasticIdInterface $id,
	): void;


	public function isValue(
		\Spameri\Elastic\Entity\Property\ElasticIdInterface $id,
	): bool;


	public function count(): int;


	public function keys(): array;


	public function clear(): void;


	public function initialized(): bool;


	public function elasticIds(): array;


	public function sort(
		\Spameri\Elastic\Entity\Collection\SortField $sortField,
		string $type,
	): void;

}
