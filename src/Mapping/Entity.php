<?php declare(strict_types = 1);

namespace Spameri\Elastic\Mapping;

#[\Attribute(\Attribute::TARGET_PROPERTY|\Attribute::TARGET_PARAMETER)]
class Entity
{


	public function __construct(
		public string $class,
	)
	{
	}

}
