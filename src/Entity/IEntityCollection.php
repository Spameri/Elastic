<?php declare(strict_types = 1);

namespace Spameri\Elastic\Entity;


interface IEntityCollection extends \IteratorAggregate
{

	public function add(
		\Spameri\Elastic\Entity\IEntity $entity
	) : void;


	public function entity(
		string $key
	) : ?\Spameri\Elastic\Entity\IEntity;


	public function remove(
		string $key
	) : void;


	public function isValue(
		string $key
	) : bool;


	public function count() : int;


	public function keys() : array;


	public function clear() : void;


	public function sort(
		\Spameri\Elastic\Entity\Collection\SortField $sortField,
		string $type
	) : void;

}
