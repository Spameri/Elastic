<?php declare(strict_types=1);

namespace Pd\ElasticSearchModule\Model;

use Pd;


class ElasticQuery
{
	/**
	 * @var Pd\ElasticSearchModule\Model\ElasticQuery\QueryCollection
	 */
	private $query;
	/**
	 * @var Pd\ElasticSearchModule\Model\ElasticQuery\FilterCollection
	 */
	private $filter;
	/**
	 * @var Pd\ElasticSearchModule\Model\ElasticQuery\AggregationCollection
	 */
	private $aggregations;
	/**
	 * @var ElasticQuery\Options
	 */
	private $options;


	public function __construct(
		Pd\ElasticSearchModule\Model\ElasticQuery\Options $options,
		Pd\ElasticSearchModule\Model\ElasticQuery\QueryCollection $query = NULL,
		Pd\ElasticSearchModule\Model\ElasticQuery\FilterCollection $filter = NULL,
		Pd\ElasticSearchModule\Model\ElasticQuery\AggregationCollection $aggregations = NULL
	)
	{
		if ( ! $query) {
			$query = new Pd\ElasticSearchModule\Model\ElasticQuery\QueryCollection();
		}
		if ( ! $filter) {
			$filter = new Pd\ElasticSearchModule\Model\ElasticQuery\FilterCollection();
		}
		if ( ! $aggregations) {
			$aggregations = new Pd\ElasticSearchModule\Model\ElasticQuery\AggregationCollection();
		}

		$this->query = $query;
		$this->filter = $filter;
		$this->aggregations = $aggregations;
		$this->options = $options;
	}


	public function toArray() : array
	{
		$array = $this->options->toArray();

		$array['query']['bool']['must'] = [];
		$array['query']['bool']['should'] = [];
		foreach ($this->query as $query) {
			$queryArray = $query->toArray();
			if (isset($queryArray['must'])) {
				foreach ($queryArray['must'] as $must) {
					$array['query']['bool']['must'][] = $must;
				}
			}

			if (isset($queryArray['should'])) {
				foreach ($queryArray['should'] as $should) {
					$array['query']['bool']['should'][] = $should;
				}
			}
		}

		$array['filter']['bool']['must'] = [];
		$array['filter']['bool']['should'] = [];
		foreach ($this->filter as $filter) {
			$filterArray = $filter->toArray();
			if (isset($filterArray['must'])) {
				foreach ($filterArray['must'] as $must) {
					$array['filter']['bool']['must'][] = $must;
				}
			}

			if (isset($filterArray['should'])) {
				foreach ($filterArray['should'] as $should) {
					$array['filter']['bool']['should'][] = $should;
				}
			}
		}

		foreach ($this->aggregations as $aggregation) {
			$array['aggregation'][] = $aggregation->toArray();
		}

		return $array;
	}


	public function query(): ElasticQuery\QueryCollection
	{
		return $this->query;
	}


	public function filter(): ElasticQuery\FilterCollection
	{
		return $this->filter;
	}


	public function aggregations(): ElasticQuery\AggregationCollection
	{
		return $this->aggregations;
	}
}
