<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity\Property;


class Url implements \Spameri\Elastic\Entity\ValueInterface
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
