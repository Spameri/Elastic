<?php declare(strict_types=1);

namespace Pd\ElasticSearchModule\Model\ElasticQuery\Query;

use Pd;


class SubQuery implements ILeafQuery
{

	/**
	 * @var ShouldCollection
	 */
	private $should;
	/**
	 * @var MustCollection
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
			$array['bool']['should'][] = $should->toArray();
		}

		foreach ($this->must as $must) {
			$array['bool']['must'][] = $must->toArray();
		}

		return $array;
	}


	public function should(): ShouldCollection
	{
		return $this->should;
	}


	public function must(): MustCollection
	{
		return $this->must;
	}
}
