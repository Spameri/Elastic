<?php declare(strict_types = 1);

namespace Spameri\Elastic\Factory;


abstract class BaseEntityFactory implements IEntityFactory
{

	/**
	 * @var \Spameri\Elastic\Factory\DependencyLoader
	 */
	private $dependencyLoader;

	/**
	 * @var \Spameri\Elastic\Factory\EntityClassResolver
	 */
	private $entityClassResolver;
	/**
	 * @var \Spameri\Elastic\Model\ClassMapProvider
	 */
	private $classMapProvider;


	public function __construct(
		DependencyLoader $dependencyLoader
		, \Spameri\Elastic\Factory\EntityClassResolver $entityClassResolver
		, \Spameri\Elastic\Model\ClassMapProvider $classMapProvider
	)
	{
		$this->dependencyLoader = $dependencyLoader;
		$this->entityClassResolver = $entityClassResolver;
		$this->classMapProvider = $classMapProvider;
	}


	public function create(
		\Spameri\ElasticQuery\Response\Result\Hit $hit
	) : \Spameri\Elastic\Entity\IElasticEntity
	{
		// 1. Figure out entity class
		$class = $this->entityClassResolver->getEntityClass($hit);

		// 2. Build class map for properties from reflection
		$map = $this->classMapProvider->provide($class);

		// 3. Create properties and entity
		$entity = $this->mapDataToEntity($hit, $map, $class);

		// 4. Load dependencies
		$this->dependencyLoader->load($entity);

		// 5. Done
		return $entity;
	}








	/**
	 * Calls method using autowiring.
	 *
	 * @return mixed
	 */
	public function callMethod(
		callable $function,
		array $args = []
	)
	{
		return $function(...\Nette\DI\Helpers::autowireArguments(\Nette\Utils\Callback::toReflection($function), $args, $this));
	}
}
