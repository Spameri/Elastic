<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity\Video\HighLights;


class AlternateVersion implements \Spameri\Elastic\Entity\EntityInterface
{

	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Property\Text
	 */
	private $text;


	public function __construct(
		\SpameriTests\Elastic\Data\Entity\Property\Text $text
	)
	{
		$this->text = $text;
	}


	public function entityVariables(): array
	{
		return \get_object_vars($this);
	}


	public function key(): string
	{
		return $this->text->value();
	}


	public function text(): \SpameriTests\Elastic\Data\Entity\Property\Text
	{
		return $this->text;
	}

}
