<?php declare(strict_types = 1);

namespace Spameri\Elastic\Factory;

readonly class EntityFactory implements \Spameri\Elastic\Factory\EntityFactoryInterface
{

	public function __construct(
		private \Spameri\Elastic\Reflection\Reflection $reflection,
	)
	{
	}

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

		if ($hit->getValue(\Spameri\Elastic\Model\Insert\PrepareEntityArray::ENTITY_CLASS) !== null) {
			$class = $hit->getValue(\Spameri\Elastic\Model\Insert\PrepareEntityArray::ENTITY_CLASS);
		}

		yield new $class(
			... $properties,
		);
	}

	protected function resolveProperties(
		\Spameri\ElasticQuery\Response\Result\Hit $hit,
		string $class,
		string|null $parentFieldName = null,
	): array
	{
		$reflection = $this->reflection->createReflection($class);

		$resolvedProperties = [];
		foreach ($this->reflection->getProperties($reflection) as $property) {
			$hitKey = $property->getName();
			if ($parentFieldName !== null) {
				$hitKey = $parentFieldName . '.' . $hitKey;
			}

			$reflectionPropertyType = $this->reflection->getPropertyType($property);
			if ($reflectionPropertyType === null) {
				$resolvedProperties[$property->getName()] = $hit->getValue($parentFieldName);
				continue;
			}

			$value = $hit->getValue($hitKey);
			$propertyTypeName = $reflectionPropertyType->getName();
			if ($reflectionPropertyType->allowsNull() && $value === null) {
				$propertyValue = null;

			} elseif ($property->hasDefaultValue() === true && $value === null) {
				$propertyValue = $property->getDefaultValue();

			} elseif ($propertyTypeName === \Spameri\Elastic\Entity\Property\Date::class) {
				if ($value !== null) {
					$propertyValue = new \Spameri\Elastic\Entity\Property\Date(
						datetime: $value,
					);

				} else {
					$propertyValue = null;
				}

			} elseif (\count($property->getAttributes()) > 0) {
				foreach ($property->getAttributes() as $attribute) {
					if (
						$attribute->getName() === \Spameri\Elastic\Mapping\Entity::class
					) {
						$arguments = $attribute->getArguments();

						if ($arguments['class'] === \Spameri\Elastic\Entity\Property\ElasticId::class) {
							$propertyValue = new $arguments['class'](
								$hit->id(),
							);

						} else {
							$propertyValue = new $arguments['class'](
								... $this->resolveProperties($hit, $propertyTypeName, $hitKey),
							);
						}
					} elseif (
						$attribute->getName() === \Spameri\Elastic\Mapping\Collection::class
					) {
						$propertyValue = new $propertyTypeName();
						if ($value !== null) {
							foreach ($value as $entityKey => $entity) {
								$propertyValue->add(
									new $entity[\Spameri\Elastic\Model\Insert\PrepareEntityArray::ENTITY_CLASS](
										... $this->resolveProperties($hit, $entity[\Spameri\Elastic\Model\Insert\PrepareEntityArray::ENTITY_CLASS], $hitKey . '.' . $entityKey),
									),
								);
							}
						}
					} elseif (
						$attribute->getName() === \Spameri\Elastic\Mapping\Ignored::class
					) {
						continue 2;
					}
				}

			} elseif (\class_exists($propertyTypeName)) {
				if (isset(\class_implements($propertyTypeName)[\Spameri\Elastic\Entity\ValueInterface::class]) === true) {
					$propertyValue = new $propertyTypeName(
						$value,
					);

				} else {
					$propertyValue = new $propertyTypeName(
						... $this->resolveProperties($hit, $propertyTypeName, $hitKey),
					);
				}

			} else {
				$propertyValue = $value;
			}

			if (isset($propertyValue)) {
				$resolvedProperties[$property->getName()] = $propertyValue;
			}

			unset($propertyValue);
		}

		return $resolvedProperties;
	}

}
