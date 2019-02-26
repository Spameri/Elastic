<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model;


class EntityMappingProvider
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


	public function provide() : array
	{
		return $this->entities;
	}

}
