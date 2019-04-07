<?php

namespace SpameriTests\Data\Entity\Video\Story;


class PlotSummary implements \Spameri\Elastic\Entity\IValue
{
	/**
	 * @var string
	 */
	private $value;


	public function __construct(
		string $plot
	)
	{
		if ($plot === '') {
			throw new \InvalidArgumentException();
		}

		$this->value = $plot;
	}


	public function value() : string
	{
		return $this->value;
	}


	public function __toString() : string
	{
		return $this->value;
	}

}
