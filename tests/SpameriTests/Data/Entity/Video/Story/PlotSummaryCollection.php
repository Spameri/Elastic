<?php

namespace SpameriTests\Data\Entity\Video\Story;


class PlotSummaryCollection implements \Spameri\Elastic\Entity\IValueCollection
{

	/**
	 * @var PlotSummary[]
	 */
	private $collection;


	public function __construct(
		PlotSummary ... $collection
	)
	{
		$this->collection = [];
		foreach ($collection as $plotSummary) {
			$this->add($plotSummary);
		}
	}


	public function add(
		PlotSummary $plotSummary
	) : void
	{
		$this->collection[\md5($plotSummary->value())] = $plotSummary;
	}


	public function first() :? PlotSummary
	{
		$first = reset($this->collection);

		return $first ?: NULL;
	}


	public function getIterator() : \ArrayIterator
	{
		return new \ArrayIterator($this->collection);
	}

}
