<?php

declare(strict_types = 1);

namespace Spameri\Elastic\Mapping;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Collection
{


	public function __construct(
		public string $class,
	)
	{
	}

}
