<?php declare(strict_types = 1);

namespace Spameri\Elastic\Entity\Collection;


abstract class ElasticEntityCollection implements \Spameri\Elastic\Entity\IElasticEntityCollection
{
	/**
	 * @var \Spameri\Elastic\Entity\IElasticEntity[]
	 */
	private $collection;


	public function __construct(
		\Spameri\Elastic\Entity\IElasticEntity ... $entityCollection
	)
	{
		$this->collection = [];
		foreach ($entityCollection as $elasticEntity) {
			$this->add($elasticEntity);
		}
	}


	public function add(
		\Spameri\Elastic\Entity\IElasticEntity $elasticEntity
	) : void
	{
		if ($elasticEntity->id() instanceof \Spameri\Elastic\Entity\Property\ElasticId) {
			$this->collection[$elasticEntity->id()->value()] = $elasticEntity;

		} else {
			$this->collection[] = $elasticEntity;
		}
	}


	protected function collection() : array
	{
		return $this->collection;
	}


	public function getIterator(): \ArrayIterator
	{
		return new \ArrayIterator($this->collection);
	}


	public function entity(
		\Spameri\Elastic\Entity\Property\IElasticId $id
	) : ?\Spameri\Elastic\Entity\IElasticEntity
	{
		if ($id instanceof \Spameri\Elastic\Entity\Property\EmptyElasticId) {
			return NULL;
		}

		if (\array_key_exists($id->value(), $this->collection)) {
			return $this->collection[$id->value()];
		}

		return NULL;
	}


	public function remove(
		\Spameri\Elastic\Entity\Property\IElasticId $id
	) : void
	{
		unset($this->collection[$id->value()]);
	}


	public function isValue(
		\Spameri\Elastic\Entity\Property\IElasticId $id
	) : bool
	{
		return \array_key_exists($id->value(), $this->collection);
	}


	public function count() : int
	{
		return \count($this->collection);
	}


	public function keys() : array
	{
		return \array_map('\strval', \array_keys($this->collection));
	}


	public function isKey(
		string $key
	) : bool
	{
		return array_key_exists($key, \array_map('\strval', \array_keys($this->collection)));
	}


	public function clear() : void
	{
		$this->collection = [];
	}


	public function sort(
		\Spameri\Elastic\Model\Collection\SortField $sortField,
		string $type
	) : void
	{
		if ( ! \in_array($type, ['asc', 'desc'], TRUE)) {
			throw new \Nette\InvalidArgumentException('Not supported sorting method.');
		}
	}
}
