<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity\Video\Story;

class PlotSummary implements \Spameri\Elastic\Entity\ValueInterface
{

	private string $value;


	public function __construct(
		string $plot,
	)
	{
		if ($plot === '') {
			throw new \InvalidArgumentException();
		}

		$this->value = $plot;
	}


	public function value(): string
	{
		return $this->value;
	}


	public function __toString(): string
	{
		return $this->value;
	}

}
