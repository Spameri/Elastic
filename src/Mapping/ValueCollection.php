<?php declare(strict_types = 1);

namespace Spameri\Elastic\Mapping;

/**
 * A collection of value objects, stored as a flat list of scalars.
 *
 * The class is named for the same reason ElasticCollection names one: the
 * document holds ["Action", "Drama"] and nothing else, so the value type
 * cannot be recovered from what was written. Without it the collection can be
 * written but never read back.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY|\Attribute::TARGET_PARAMETER)]
class ValueCollection
{


	public function __construct(
		public string $class,
	)
	{
	}

}
