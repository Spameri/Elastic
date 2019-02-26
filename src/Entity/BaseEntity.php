<?php declare(strict_types = 1);

namespace Spameri\Elastic\Entity;


abstract class BaseEntity implements IElasticEntity
{

	/**
	 * @var \Spameri\Elastic\Entity\Property\IElasticId
	 */
	private $id;


	public function __construct(
		\Spameri\Elastic\Entity\Property\IElasticId $id
	)
	{
		$this->id = $id;
	}


	public function entityVariables() : array
	{
		return \get_object_vars($this);
	}


	public function id() : \Spameri\Elastic\Entity\Property\IElasticId
	{
		return $this->id;
	}

}
