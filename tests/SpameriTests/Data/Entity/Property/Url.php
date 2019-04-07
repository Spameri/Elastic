<?php

namespace SpameriTests\Data\Entity\Property;


class Url implements \Spameri\Elastic\Entity\IValue
{

	/**
	 * @var string
	 */
	private $value;


	public function __construct(
		string $value
	)
	{
		if ( ! \Nette\Utils\Validators::isUrl($value)) {
			throw new \InvalidArgumentException();
		}

		$this->value = $value;
	}


	public function value() : string
	{
		return $this->value;
	}

}
