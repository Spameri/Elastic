<?php declare(strict_types = 1);

namespace Spameri\Elastic\Reflection;

class Reflection
{


	/**
	 * @throws \ReflectionException
	 *
	 * @return \ReflectionClass<\Spameri\Elastic\Entity\AbstractElasticEntity>
	 */
	public function createReflection(object|string $class): \ReflectionClass
	{
		return new \ReflectionClass($class);
	}


	/**
	 * @param \ReflectionClass<\Spameri\Elastic\Entity\AbstractElasticEntity> $class
	 * @return array<\ReflectionProperty>
	 */
	public function getProperties(\ReflectionClass $class): array
	{
		return $class->getProperties();
	}


	public function getPropertyType(\ReflectionProperty $property): \ReflectionNamedType|null
	{
		$reflectionType = $property->getType();

		if ($reflectionType instanceof \ReflectionNamedType) {
			return $reflectionType;
		}

		return null;
	}
}
