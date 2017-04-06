<?php declare(strict_types=1);

namespace Pd\ElasticSearchModule\Model\ElasticQuery;

use Pd;


class Filter implements IFilter
{

	/**
	 * @var Query\ShouldCollection
	 */
	private $should;
	/**
	 * @var Query\MustCollection
	 */
	private $must;


	public function __construct(
		Pd\ElasticSearchModule\Model\ElasticQuery\Query\ShouldCollection $should = NULL,
		Pd\ElasticSearchModule\Model\ElasticQuery\Query\MustCollection $must = NULL
	)
	{
		if ( ! $should) {
			$should = new Pd\ElasticSearchModule\Model\ElasticQuery\Query\ShouldCollection();
		}
		if ( ! $must) {
			$must = new Pd\ElasticSearchModule\Model\ElasticQuery\Query\MustCollection();
		}

		$this->should = $should;
		$this->must = $must;
	}


	public function toArray(): array
	{
		$array = [];

		foreach ($this->should as $should) {
			$array['should'][] = $should->toArray();
		}

		foreach ($this->must as $must) {
			$array['must'][] = $must->toArray();
		}

		return $array;
	}


	public function should(): Query\ShouldCollection
	{
		return $this->should;
	}


	public function must(): Query\MustCollection
	{
		return $this->must;
	}
}
