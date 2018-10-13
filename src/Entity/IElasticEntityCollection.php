<?php declare(strict_types = 1);

namespace Spameri\Elastic\Entity;


interface IElasticEntityCollection extends \IteratorAggregate
{

	public function add(
		\Spameri\Elastic\Entity\IElasticEntity $entity
	) : void;


	public function entity(
		\Spameri\Elastic\Entity\Property\IElasticId $id
	) : ?\Spameri\Elastic\Entity\IElasticEntity;


	public function remove(
		\Spameri\Elastic\Entity\Property\IElasticId $id
	) : void;


	public function isValue(
		\Spameri\Elastic\Entity\Property\IElasticId $id
	) : bool;


	public function count() : int;


	public function keys() : array;


	public function clear() : void;


	public function initialized() : bool;


	public function elasticIds() : array;


	public function sort(
		\Spameri\Elastic\Model\Collection\SortField $sortField,
		string $type
	) : void;

}
