<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model;


class ClassMapBuilder
{

	/**
	 * @var \Spameri\Elastic\Model\EntityMappingProvider
	 */
	private $entityMappingProvider;
	/**
	 * @var \Spameri\Elastic\Model\ClassMapProvider
	 */
	private $classMapProvider;


	public function __construct(
		EntityMappingProvider $entityMappingProvider
		, ClassMapProvider $classMapProvider
	)
	{
		$this->entityMappingProvider = $entityMappingProvider;
		$this->classMapProvider = $classMapProvider;
	}


	public function getMapping() : array
	{
		return $this->classMapProvider->all();
	}


	public function build() : void
	{
		foreach ($this->entityMappingProvider->provide() as $entityMapping) {
			$class = $entityMapping['class'];

			$reflection = new \ReflectionClass($class);
			$this->classMapProvider->add($class, $this->processProperties($reflection));
		}
	}


	public function processProperties(
		\ReflectionClass $reflectionClass
	) : array
	{
		$map = [];

		/** @var \ReflectionParameter $parameter */
		foreach ($reflectionClass->getMethod('__construct')->getParameters() as $num => $parameter) {
			$argumentType = \Nette\Utils\Reflection::getParameterType($parameter);

			if ($argumentType instanceof \Spameri\Elastic\Entity\IElasticEntity) {
				$map[$num] = \Spameri\Elastic\Entity\Property\ElasticId::class;

			} elseif ($parameter->getClass()->isSubclassOf(\Spameri\Elastic\Entity\Property\IElasticId::class)) {
				$map[$num] = \Spameri\Elastic\Entity\Property\ElasticId::class;

			} elseif ($argumentType instanceof \Spameri\Elastic\Entity\IEntity) {
				$map[$num] = $this->processProperties($parameter->getClass());

			} elseif ($argumentType instanceof \Spameri\Elastic\Entity\IValue) {
				$map[$num] = $argumentType;

			} elseif ($argumentType instanceof \Spameri\Elastic\Entity\IEntityCollection) {
				$map[$num][$argumentType] = $this->processProperties($parameter->getClass());

			} elseif ($argumentType instanceof \Spameri\Elastic\Entity\IElasticEntityCollection) {
				$map[$num][$argumentType] = \Spameri\Elastic\Entity\Property\ElasticId::class;

			} elseif ($argumentType instanceof \Spameri\Elastic\Entity\IValueCollection) {
				$map[$num][$argumentType] = $this->processProperties($parameter->getClass());

			} elseif (
				\is_string($argumentType)
				|| \is_int($argumentType)
				|| \is_bool($argumentType)
				|| $argumentType === NULL
			) {
				$map[$num] = $argumentType;

			} elseif ($argumentType instanceof \Spameri\Elastic\Entity\Property\Date) {
				$map[$num] = \Spameri\Elastic\Entity\Property\Date::class;

			} elseif ($argumentType instanceof \Spameri\Elastic\Entity\Property\DateTime) {
				$map[$num] = \Spameri\Elastic\Entity\Property\DateTime::class;

			} elseif ($argumentType instanceof \DateTime) {
				$map[$num] = \DateTime::class;
			}
		}

		return $map;
	}

}
