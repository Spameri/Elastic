<?php declare(strict_types=1);

namespace Pd\ElasticSearchModule\Model\ElasticQuery\Query;

use Pd;


class Term implements ILeafQuery
{

	/**
	 * @var string
	 */
	private $field;
	/**
	 * @var string
	 */
	private $query;
	/**
	 * @var int
	 */
	private $boost;


	public function __construct(
		string $field,
		$query,
		float $boost = 1.0
	)
	{
		$this->field = $field;
		$this->query = $query;
		$this->boost = $boost;
	}


	public function toArray()
	{
		$array = [
			'term' => [
				$this->field => [
					'value' => $this->query,
					'boost' => $this->boost,
				],
			],
		];

		return $array;
	}
}
