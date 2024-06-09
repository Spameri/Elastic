<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity\Video\Technical;

class Camera implements \Spameri\Elastic\Entity\ValueInterface
{

	/**
	 * @var string|NULL
	 */
	private ?string $value = null;


	public function __construct(
		string|null $value,
	)
	{
		if ($value !== null && \strlen($value) > 255) {
			throw new \InvalidArgumentException();
		}

		$this->value = $value;
	}


	public function value(): string|null
	{
		return $this->value;
	}

}
