<?php declare(strict_types = 1);

namespace Spameri\Elastic\Entity\Import;


class DateValue implements ValidationPropertyInterface
{

	/**
	 * @var \DateTime
	 */
	private $value;

	/**
	 * @var string
	 */
	private $key;

	/**
	 * @var string
	 */
	private $format;


	public function __construct(
		\DateTime $value,
		string $key,
		string $format = 'Y-m-d H:i:s'
	)
	{
		$this->value = $value;
		$this->key = $key;
		$this->format = $format;
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
