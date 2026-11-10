<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity\Video;

class Story implements \Spameri\Elastic\Entity\EntityInterface
{

	/**
	 * @param \SpameriTests\Elastic\Data\Entity\Property\Description $description
	 * @param \SpameriTests\Elastic\Data\Entity\Video\Story\TagLineCollection<\SpameriTests\Elastic\Data\Entity\Video\Story\TagLine> $tagLines
	 * @param \SpameriTests\Elastic\Data\Entity\Video\Story\PlotSummaryCollection<\SpameriTests\Elastic\Data\Entity\Video\Story\PlotSummary> $plots
	 * @param \SpameriTests\Elastic\Data\Entity\Video\Story\KeyWordCollection<\SpameriTests\Elastic\Data\Entity\Video\Story\KeyWord> $keyWords
	 * @param \SpameriTests\Elastic\Data\Entity\Video\Story\Synopsis|null $synopsis
	 */
	public function __construct(
		public \SpameriTests\Elastic\Data\Entity\Property\Description $description,
		public \SpameriTests\Elastic\Data\Entity\Video\Story\TagLineCollection $tagLines,
		public \SpameriTests\Elastic\Data\Entity\Video\Story\PlotSummaryCollection $plots,
		public \SpameriTests\Elastic\Data\Entity\Video\Story\KeyWordCollection $keyWords,
		public \SpameriTests\Elastic\Data\Entity\Video\Story\Synopsis|null $synopsis = null,
	)
	{
	}


	public function entityVariables(): array
	{
		return \get_object_vars($this);
	}


	public function key(): string
	{
		return (string) \spl_object_id($this);
	}


	public function description(): \SpameriTests\Elastic\Data\Entity\Property\Description
	{
		return $this->description;
	}

}
