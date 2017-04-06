<?php declare(strict_types=1);

namespace Pd\ElasticSearchModule\Model\ElasticQuery\Query;

use Pd;


class Terms implements ILeafQuery
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
		array $query,
		int $boost = 1
	)
	{
		$this->field = $field;
		$this->query = $query;
		$this->boost = $boost;
	}


	public function toArray()
	{
		$array = [
			'terms' => [
				$this->field => $this->query,
				'boost' => $this->boost,
			],
		];

		return $array;
	}
}
