<?php declare(strict_types=1);

namespace Pd\ElasticSearchModule\Model\ElasticQuery\Query;

use Pd;


class PhasePrefix implements ILeafQuery
{

	/**
	 * @var string
	 */
	private $field;
	/**
	 * @var string
	 */
	private $queryString;
	/**
	 * @var int
	 */
	private $boost;
	/**
	 * @var int
	 */
	private $slop;


	public function __construct(
		string $field,
		string $queryString,
		int $boost = 1,
		int $slop = 1
	)
	{
		$this->field = $field;
		$this->queryString = $queryString;
		$this->boost = $boost;
		$this->slop = $slop;
	}


	public function toArray()
	{
		$array = [
			'match_phrase_prefix' => [
				$this->field => [
					'query' => $this->queryString,
					'boost' => $this->boost,
					'slop'	=> $this->slop,
				],
			],
		];

		return $array;
	}
}
