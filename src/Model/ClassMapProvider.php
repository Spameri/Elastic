<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model;


class ClassMapProvider
{

	/**
	 * @var array
	 */
	private $classMap;


	public function add(
		string $entityClass
		, array $classMap
	) : void
	{
		$this->classMap[$entityClass] = $classMap;
	}


	public function provide(
		string $entityClass
	) : array
	{
		return $this->classMap[$entityClass];
	}


	public function all() : array
	{
		return $this->classMap;
	}

}
