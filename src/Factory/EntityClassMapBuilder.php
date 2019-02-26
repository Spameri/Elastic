<?php declare(strict_types = 1);

namespace Spameri\Elastic\Factory;


class EntityClassMapBuilder
{

	/**
	 * @var array
	 */
	private $entities;


	public function __construct(
		array $entities
	)
	{
		$this->entities = $entities;
	}

}
