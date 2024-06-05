<?php declare(strict_types = 1);

namespace Spameri\Elastic\Mapping;

#[\Attribute(\Attribute::TARGET_PROPERTY|\Attribute::TARGET_PARAMETER)]
class ElasticCollection
{


	public function __construct(
		public string $class,
	)
	{
	}

}
