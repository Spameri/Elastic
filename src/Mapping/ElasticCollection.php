<?php declare(strict_types = 1);

namespace Spameri\Elastic\Mapping;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class ElasticCollection
{


	public function __construct(
		public string $class,
	)
	{
	}

}
