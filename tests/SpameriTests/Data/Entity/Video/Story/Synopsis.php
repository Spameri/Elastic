<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity\Video\Story;


class Synopsis implements \Spameri\Elastic\Entity\ValueInterface
{

	/**
	 * @var ?string
	 */
	private $value;


	public function __construct(
		?string $value
	)
	{
		$this->value = $value;
	}


	public function value() : ?string
	{
		return $this->value;
	}
}
