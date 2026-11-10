<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity;

/**
 * Abstract parent for nested STI entities.
 * Used to test #[STIEntity] attribute on nested objects.
 */
abstract class STIMediaInfo implements \Spameri\Elastic\Entity\EntityInterface
{

	abstract public function key(): string;


	public function entityVariables(): array
	{
		return \get_object_vars($this);
	}

}
