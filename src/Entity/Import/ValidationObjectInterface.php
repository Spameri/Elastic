<?php declare(strict_types = 1);

namespace Spameri\Elastic\Entity\Import;

interface ValidationObjectInterface extends \Spameri\ElasticQuery\Entity\ArrayInterface
{

	/**
	 * @return mixed
	 */
	public function key();

	/**
	 * @return array<mixed>
	 */
	public function entityVariables(): array;

}
