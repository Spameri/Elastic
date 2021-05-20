<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity\Video\Technical;

class Camera implements \Spameri\Elastic\Entity\ValueInterface
{

	/**
	 * @var string|NULL
	 */
	private $value;


	public function __construct(
		?string $value
	)
	{
		if ($value !== NULL && \strlen($value) > 255) {
			throw new \InvalidArgumentException();
		}

		$this->value = $value;
	}


	public function value(): ?string
	{
		return $this->value;
	}

}
