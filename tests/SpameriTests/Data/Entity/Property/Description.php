<?php declare(strict_types = 1);

namespace SpameriTests\Data\Entity\Property;


class Description implements \Spameri\Elastic\Entity\IValue
{

	/**
	 * @var ?string
	 */
	private $value;


	public function __construct(
		?string $description
	)
	{
		$this->value = $description;
	}


	public function value() : ?string
	{
		return $this->value;
	}
}
