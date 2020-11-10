<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity\Video;


class Story implements \Spameri\Elastic\Entity\EntityInterface
{

	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Property\Description
	 */
	private $description;

	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Video\Story\TagLineCollection
	 */
	private $tagLines;

	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Video\Story\PlotSummaryCollection
	 */
	private $plots;

	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Video\Story\Synopsis|NULL
	 */
	private $synopsis;

	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Video\Story\KeyWordCollection
	 */
	private $keyWords;


	public function __construct(
		\SpameriTests\Elastic\Data\Entity\Property\Description $description
		, \SpameriTests\Elastic\Data\Entity\Video\Story\TagLineCollection $tagLines
		, \SpameriTests\Elastic\Data\Entity\Video\Story\PlotSummaryCollection $plots
		, \SpameriTests\Elastic\Data\Entity\Video\Story\KeyWordCollection $keyWord
		, ?\SpameriTests\Elastic\Data\Entity\Video\Story\Synopsis $synopsis = NULL
	)
	{
		$this->description = $description;
		$this->tagLines = $tagLines;
		$this->plots = $plots;
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
		\SpameriTests\Elastic\Data\Entity\Property\Description $description
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
		?\SpameriTests\Elastic\Data\Entity\Video\Story\Synopsis $synopsis
	): void
	{
		$this->synopsis = $synopsis;
	}


	public function synopsis(): ?\SpameriTests\Elastic\Data\Entity\Video\Story\Synopsis
	{
		return $this->synopsis;
	}


	public function keyWords(): \SpameriTests\Elastic\Data\Entity\Video\Story\KeyWordCollection
	{
		return $this->keyWords;
	}
}
