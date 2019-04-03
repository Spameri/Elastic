<?php declare(strict_types = 1);

namespace Spameri\ElasticQuery\Response;


class ResultSearch implements ResultInterface
{

	/**
	 * @var \Spameri\ElasticQuery\Response\Stats
	 */
	private $stats;
	/**
	 * @var \Spameri\ElasticQuery\Response\Shards
	 */
	private $shards;
	/**
	 * @var \Spameri\ElasticQuery\Response\Result\HitCollection
	 */
	private $hitCollection;
	/**
	 * @var \Spameri\ElasticQuery\Response\Result\AggregationCollection
	 */
	private $aggregationCollection;


	public function __construct(
		Stats $stats
		, Shards $shards
		, \Spameri\ElasticQuery\Response\Result\HitCollection $hitCollection
		, \Spameri\ElasticQuery\Response\Result\AggregationCollection $aggregationCollection
	)
	{
		$this->stats = $stats;
		$this->shards = $shards;
		$this->hitCollection = $hitCollection;
		$this->aggregationCollection = $aggregationCollection;
	}


	public function stats() : \Spameri\ElasticQuery\Response\Stats
	{
		return $this->stats;
	}


	public function shards() : \Spameri\ElasticQuery\Response\Shards
	{
		return $this->shards;
	}


	public function hits() : \Spameri\ElasticQuery\Response\Result\HitCollection
	{
		return $this->hitCollection;
	}


	public function aggregations() : \Spameri\ElasticQuery\Response\Result\AggregationCollection
	{
		return $this->aggregationCollection;
	}


	public function getHit(
		string $id
	) : \Spameri\ElasticQuery\Response\Result\Hit
	{
		/** @var \Spameri\ElasticQuery\Response\Result\Hit $hit */
		foreach ($this->hitCollection as $hit) {
			if ($hit->id() === $id) {
				return $hit;
			}
		}

		throw new \Spameri\ElasticQuery\Exception\HitNotFound(
			'Hit with id: ' . $id . 'not found.'
		);
	}


	public function getAggregation(
		string $name
	) : \Spameri\ElasticQuery\Response\Result\Aggregation
	{
		/** @var \Spameri\ElasticQuery\Response\Result\Aggregation $aggregation */
		foreach ($this->aggregationCollection as $aggregation) {
			if ($aggregation->name() === $name) {
				return $aggregation;
			}
		}

		throw new \Spameri\ElasticQuery\Exception\AggregationNotFound(
			'Aggregation with name: ' . $name . ' has not been found.'
		);
	}

}
