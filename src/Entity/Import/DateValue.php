<?php declare(strict_types = 1);

namespace Spameri\Elastic\Entity\Import;

readonly class DateValue implements ValidationPropertyInterface
{

	public function __construct(
		private \DateTime $value,
		private string $key,
		private string $format = 'Y-m-d H:i:s',
	)
	{
	}


	public function key(): string
	{
		return $this->key;
	}


	public function getValue(): string
	{
		return $this->value->format($this->format);
	}

}
