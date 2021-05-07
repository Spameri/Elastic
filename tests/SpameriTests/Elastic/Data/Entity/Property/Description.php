<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity\Property;

class Description implements \Spameri\Elastic\Entity\ValueInterface
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


	public function value(): ?string
	{
		return $this->value;
	}

}
