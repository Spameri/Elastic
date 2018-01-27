<?php declare(strict_types = 1);

namespace Spameri\Model\Collection;


abstract class Collection
{
	/**
	 * @var array
	 */
	protected $metadata;

	/**
	 * @var array
	 */
	protected $rows;

	/**
	 * @var integer
	 */
	protected $count;

	/**
	 * @var string
	 */
	protected $entity;


	public function __construct(array $data)
	{
		$this->metadata = $data;
	}


	public function getMetadata() : array
	{
		return $this->metadata;
	}


	public function setMetadata(array $metadata)
	{
		$this->metadata = $metadata;
	}


	abstract public function getCount() : int;


	abstract public function setCount(int $count);


	abstract public function getRows() : \Generator;


	abstract public function setRows(array $rows);


	public function getEntity() : string
	{
		return $this->entity;
	}


	public function setEntity(string $entity)
	{
		$this->entity = $entity;
	}
}
