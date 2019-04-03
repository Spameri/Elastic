<?php declare(strict_types = 1);

namespace Spameri\ElasticQuery\Response;


class ResultSingle implements ResultInterface
{

	/**
	 * @var \Spameri\ElasticQuery\Response\Result\Hit
	 */
	private $hit;
	/**
	 * @var \Spameri\ElasticQuery\Response\StatsSingle
	 */
	private $stats;


	public function __construct(
		\Spameri\ElasticQuery\Response\Result\Hit $hit
		, StatsSingle $stats
	)
	{
		$this->hit = $hit;
		$this->stats = $stats;
	}


	public function hit() : \Spameri\ElasticQuery\Response\Result\Hit
	{
		return $this->hit;
	}


	public function stats() : \Spameri\ElasticQuery\Response\StatsSingle
	{
		return $this->stats;
	}

}
