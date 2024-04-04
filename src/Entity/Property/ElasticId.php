<?php declare(strict_types = 1);

namespace Spameri\Elastic\Entity\Property;

readonly class ElasticId implements \Spameri\Elastic\Entity\ValueInterface, \Spameri\Elastic\Entity\Property\ElasticIdInterface
{

	public const FIELD_NAME = '_id';

	public function __construct(
		private string $value,
	)
	{
		if ($value === '') {
			throw new \InvalidArgumentException();
		}
	}


	public function value(): string
	{
		return $this->value;
	}

}
