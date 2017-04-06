<?php declare(strict_types=1);

namespace Pd\ElasticSearchModule\Model\ElasticQuery\Query;

use Pd;


class Match implements ILeafQuery
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
	/**
	 * @var string
	 */
	private $fuzziness;


	public function __construct(
		string $field,
		string $queryString,
		int $boost = 1,
		int $slop = 1,
		string $fuzziness = ''
	)
	{
		$this->field = $field;
		$this->queryString = $queryString;
		$this->boost = $boost;
		$this->slop = $slop;
		$this->fuzziness = $fuzziness;
	}


	public function toArray()
	{
		$array = [
			'match' => [
				$this->field => [
					'query' => $this->queryString,
					'boost' => $this->boost,
					'slop'	=> $this->slop,
				],
			],
		];

		if ($this->fuzziness) {
			$array['match'][$this->field]['fuzziness'] = $this->fuzziness;
		}

		return $array;
	}
}
