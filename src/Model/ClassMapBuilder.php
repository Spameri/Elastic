<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model;


class ClassMapBuilder
{

	/**
	 * @var \Spameri\Elastic\Model\EntityMappingProvider
	 */
	private $entityMappingProvider;

	/**
	 * @var array
	 */
	private $map;


	public function __construct(
		EntityMappingProvider $entityMappingProvider
	)
	{
		$this->entityMappingProvider = $entityMappingProvider;
	}


	public function getMapping() : array
	{
		return $this->map;
	}


	public function build(): void
	{
		foreach ($this->entityMappingProvider->provide() as $entityMapping) {
			$class = $entityMapping['class'];

			$reflection = new \ReflectionClass($class);
			$map = $this->processProperties($reflection);

			$this->map[$class] = $map;
		}
	}


	public function processProperties(
		\ReflectionClass $reflectionClass
	): array
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
