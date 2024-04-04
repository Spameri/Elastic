<?php declare(strict_types = 1);

namespace Spameri\Elastic\Entity\Import;

interface ValidationObjectInterface extends \Spameri\ElasticQuery\Entity\ArrayInterface
{

	public function key(): mixed;


	/**
	 * @return array<mixed>
	 */
	public function entityVariables(): array;

}
