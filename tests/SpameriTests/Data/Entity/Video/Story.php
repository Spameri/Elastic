<?php

namespace SpameriTests\Data\Entity\Video;


class Story implements \Spameri\Elastic\Entity\IEntity
{

	/**
	 * @var \SpameriTests\Data\Entity\Property\Description
	 */
	private $description;

	/**
	 * @var \SpameriTests\Data\Entity\Video\Story\TagLineCollection
	 */
	private $tagLines;

	/**
	 * @var \SpameriTests\Data\Entity\Video\Story\PlotSummaryCollection
	 */
	private $plots;

	/**
	 * @var \SpameriTests\Data\Entity\Video\Story\Synopsis
	 */
	private $synopsis;

	/**
	 * @var \SpameriTests\Data\Entity\Video\Story\KeyWordCollection
	 */
	private $keyWords;


	public function __construct(
		\SpameriTests\Data\Entity\Property\Description $description
		, \SpameriTests\Data\Entity\Video\Story\TagLineCollection $tagLines
		, \SpameriTests\Data\Entity\Video\Story\PlotSummaryCollection $plots
		, \SpameriTests\Data\Entity\Video\Story\KeyWordCollection $keyWord
		, \SpameriTests\Data\Entity\Video\Story\Synopsis $synopsis = NULL
	)
	{
		$this->description = $description;
		$this->tagLines = $tagLines;
		$this->plots = $plots;
		$this->synopsis = $synopsis;
		$this->keyWords = $keyWord;
	}


	public function entityVariables() : array
	{
		return get_object_vars($this);
	}


	public function key() : string
	{

	}


	public function description() : \SpameriTests\Data\Entity\Property\Description
	{
		return $this->description;
	}


	public function changeDescription(
		\SpameriTests\Data\Entity\Property\Description $description
	) : void
	{
		$this->description = $description;
	}


	public function tagLines() : \SpameriTests\Data\Entity\Video\Story\TagLineCollection
	{
		return $this->tagLines;
	}


	public function plots() : \SpameriTests\Data\Entity\Video\Story\PlotSummaryCollection
	{
		return $this->plots;
	}


	public function setSynopsis(
		\SpameriTests\Data\Entity\Video\Story\Synopsis $synopsis
	) : void
	{
		$this->synopsis = $synopsis;
	}


	public function synopsis() : \SpameriTests\Data\Entity\Video\Story\Synopsis
	{
		return $this->synopsis;
	}


	public function keyWords() : \SpameriTests\Data\Entity\Video\Story\KeyWordCollection
	{
		return $this->keyWords;
	}
}