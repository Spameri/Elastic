<?php declare(strict_types=1);

namespace Pd\ElasticSearchModule\Model\ElasticQuery\Query;

use Pd;


class WildCard implements ILeafQuery
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


	public function __construct(
		string $field,
		string $queryString,
		int $boost = 1
	)
	{
		$this->field = $field;
		$this->queryString = $queryString;
		$this->boost = $boost;
	}


	public function toArray()
	{
		$array = [
			'wildcard' => [
				$this->field => [
					'value' => $this->queryString . '*',
					'boost' => $this->boost,
				],
			],
		];

		return $array;
	}
}
