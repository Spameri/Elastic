<?php declare(strict_types = 1);

namespace Spameri\Elastic\Entity\Import;

interface ValidationPropertyInterface
{

	public function key(): mixed;

	public function getValue(): mixed;

}
