<?php declare(strict_types = 1);

namespace SpameriTests\Data\Entity\Video\Story;


class KeyWord implements \Spameri\Elastic\Entity\ValueInterface
{

	/**
	 * @var string
	 */
	private $value;


	public function __construct(
		string $value
	)
	{
		if ($value === '') {
			throw new \InvalidArgumentException();
		}
		if (\strlen($value) > 55) {
			throw new \InvalidArgumentException();
		}

		$this->value = $value;
	}


	public function value() : string
	{
		return $this->value;
	}
}
