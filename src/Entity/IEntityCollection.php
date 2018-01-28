<?php declare(strict_types = 1);

namespace Spameri\Elastic\Entity;


interface IEntityCollection extends \IteratorAggregate
{

	public function add(IElasticEntity $entity) : void;

	public function entity(\Spameri\Elastic\Entity\Property\IElasticId $id) : ?IElasticEntity;

	public function remove(\Spameri\Elastic\Entity\Property\IElasticId $id) : void;

	public function isValue(\Spameri\Elastic\Entity\Property\IElasticId $id) : bool;

	public function count() : int;

	public function keys() : array;

	public function clear() : void;

	public function sort(
		\Spameri\Elastic\Model\Collection\SortField $sortField,
		string $type
	) : void;

}
