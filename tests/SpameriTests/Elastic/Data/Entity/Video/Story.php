<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity\Video;

class Story implements \Spameri\Elastic\Entity\EntityInterface
{

	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Video\Story\Synopsis|NULL
	 */
	private ?\SpameriTests\Elastic\Data\Entity\Video\Story\Synopsis $synopsis = null;

	private \SpameriTests\Elastic\Data\Entity\Video\Story\KeyWordCollection $keyWords;


	public function __construct(
		private \SpameriTests\Elastic\Data\Entity\Property\Description $description,
		private \SpameriTests\Elastic\Data\Entity\Video\Story\TagLineCollection $tagLines,
		private \SpameriTests\Elastic\Data\Entity\Video\Story\PlotSummaryCollection $plots,
		\SpameriTests\Elastic\Data\Entity\Video\Story\KeyWordCollection $keyWord,
		\SpameriTests\Elastic\Data\Entity\Video\Story\Synopsis|null $synopsis = null,
	)
	{
		$this->synopsis = $synopsis;
		$this->keyWords = $keyWord;
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


	public function changeDescription(
		\SpameriTests\Elastic\Data\Entity\Property\Description $description,
	): void
	{
		$this->description = $description;
	}


	public function tagLines(): \SpameriTests\Elastic\Data\Entity\Video\Story\TagLineCollection
	{
		return $this->tagLines;
	}


	public function plots(): \SpameriTests\Elastic\Data\Entity\Video\Story\PlotSummaryCollection
	{
		return $this->plots;
	}


	public function setSynopsis(
		\SpameriTests\Elastic\Data\Entity\Video\Story\Synopsis|null $synopsis,
	): void
	{
		$this->synopsis = $synopsis;
	}


	public function synopsis(): \SpameriTests\Elastic\Data\Entity\Video\Story\Synopsis|null
	{
		return $this->synopsis;
	}


	public function keyWords(): \SpameriTests\Elastic\Data\Entity\Video\Story\KeyWordCollection
	{
		return $this->keyWords;
	}

}
