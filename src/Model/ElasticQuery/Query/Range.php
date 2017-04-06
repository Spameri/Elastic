<?php declare(strict_types=1);

namespace Pd\ElasticSearchModule\Model\ElasticQuery\Query;

use Pd;


class Range implements ILeafQuery
{

	/**
	 * @var string
	 */
	private $field;
	/**
	 * @var int
	 */
	private $boost;
	/**
	 * @var int
	 */
	private $gte;
	/**
	 * @var int
	 */
	private $lte;


	public function __construct(
		string $field,
		$gte = NULL,
		$lte = NULL,
		int $boost = 1
	)
	{
		if ($gte === NULL && $lte === NULL) {
			throw new \InvalidArgumentException('Range must have at least one border value.');
		}

		$this->field = $field;
		$this->boost = $boost;
		$this->gte = $gte;
		$this->lte = $lte;
	}


	public function toArray()
	{
		$array = [
			'range' => [
				$this->field => [
					'boost' => $this->boost,
				],
			],
		];

		if ($this->gte) {
			$array['range'][$this->field]['gte'] = $this->gte;
		}

		if ($this->lte) {
			$array['range'][$this->field]['lte'] = $this->lte;
		}

		return $array;
	}
}
