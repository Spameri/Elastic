<?php declare(strict_types=1);

namespace Pd\ElasticSearchModule\Model\ElasticQuery;


class Options
{

	/**
	 * @var int
	 */
	private $size;
	/**
	 * @var int
	 */
	private $from;
	/**
	 * @var SortCollection
	 */
	private $sort;
	/**
	 * @var int
	 */
	private $minScore;


	public function __construct(
		int $size,
		int $from,
		SortCollection $sort = NULL,
		float $minScore = NULL
	)
	{
		if ( ! $sort) {
			$sort = new SortCollection();
		}

		$this->size = $size;
		$this->from = $from;
		$this->sort = $sort;
		$this->minScore = $minScore;
	}


	public function toArray() : array
	{
		$array = [
			'from' => $this->from,
			'size' => $this->size,
		];

		foreach ($this->sort as $item) {
			$array['sort'][] = $item->toArray();
		}

		if ($this->minScore) {
			$array['min_score'] = $this->minScore;
		}

		return $array;
	}


	public function sort(): SortCollection
	{
		return $this->sort;
	}
}
