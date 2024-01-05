<?php declare(strict_types = 1);

namespace Spameri\Elastic\Factory;

class EntityFactory implements \Spameri\Elastic\Factory\EntityFactoryInterface
{

	/**
	 * @return \Generator<\SpameriTests\Elastic\Data\Entity\Person>
	 */
	public function create(
		\Spameri\ElasticQuery\Response\Result\Hit $hit,
		string|null $class = null,
	): \Generator
	{
		if ($class === null) {
			throw new \Spameri\Elastic\Exception\InvalidArgument('Class must be set.');
		}

		$properties = $this->resolveProperties($hit, $class);

		yield new $class(
			... $properties,
		);
	}

	protected function resolveProperties(
		\Spameri\ElasticQuery\Response\Result\Hit $hit,
		string $class,
		string|null $parentFieldName = null
	): array
	{
		$reflection = new \ReflectionClass($class);

		$resolvedProperties = [];
		foreach ($reflection->getProperties() as $property) {
			$hitKey = $property->getName();
			if ($parentFieldName !== null) {
				$hitKey = $parentFieldName . '.' . $hitKey;
			}

			if ($property->getType() === null) {
				$resolvedProperties[$property->getName()] = $hit->getValue($parentFieldName);
				continue;
			}

			$propertyTypeName = $property->getType()->getName();
			if ($propertyTypeName === \Spameri\Elastic\Entity\Property\Date::class) {
				$value = $hit->getValue($hitKey);
				if ($value !== null) {
					$propertyValue = new \Spameri\Elastic\Entity\Property\Date(
						datetime: $value,
					);

				} else {
					$propertyValue = null;
				}

			} elseif (\is_subclass_of($propertyTypeName, \Spameri\Elastic\Entity\Collection\AbstractEntityCollection::class)) {
				// TODO collections

			} elseif (\interface_exists($propertyTypeName)) {
				$propertyValue = new \Spameri\Elastic\Entity\Property\ElasticId($hit->id());

			} elseif (\class_exists($propertyTypeName)) {
				$propertyValue = new $propertyTypeName(
					... $this->resolveProperties($hit, $propertyTypeName, $hitKey)
				);

			} else {
				$propertyValue = $hit->getValue($hitKey);
			}

			$resolvedProperties[$property->getName()] = $propertyValue;
		}

		return $resolvedProperties;
	}

}
