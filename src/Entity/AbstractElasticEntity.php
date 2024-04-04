<?php declare(strict_types = 1);

namespace Spameri\Elastic\Entity;

abstract class AbstractElasticEntity implements ElasticEntityInterface
{

	public function __construct(
		public \Spameri\Elastic\Entity\Property\ElasticIdInterface $id,
	)
	{
	}


	public function id(): \Spameri\Elastic\Entity\Property\ElasticIdInterface
	{
		return $this->id;
	}


	public function entityVariables(): array
	{
		return \get_object_vars($this);
	}

}