<?php

namespace Pd\ElasticSearchModule\Model\ElasticQuery;


class Sort
{
	/**
	 * @var string
	 */
	private $field;
	/**
	 * @var string
	 */
	private $type;


	public function __construct(
		string $field,
		string $type
	)
	{

		$this->field = $field;
		$this->type = $type;
	}


	public function toArray() : array
	{
		return [
			$this->field => [
				'order' => $this->type,
			],
		];
	}
}
