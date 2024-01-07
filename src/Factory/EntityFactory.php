<?php

declare(strict_types = 1);

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
		string|null $parentFieldName = null,
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

			} elseif (\count($property->getAttributes()) > 0) {
				foreach ($property->getAttributes() as $attribute) {
					if (
						\in_array(
							$attribute->getName(),
							[
								\Spameri\Elastic\Mapping\Collection::class,
								\Spameri\Elastic\Mapping\Entity::class,
							]
						) === true
					) {
						$arguments = $attribute->getArguments();

						if ($arguments['class'] === \Spameri\Elastic\Entity\Property\ElasticId::class) {
							$propertyValue = new $arguments['class'](
								$hit->id(),
							);

						} elseif ($attribute->getName() === \Spameri\Elastic\Mapping\Collection::class) {
							$propertyValue = new \Spameri\Elastic\Entity\Collection\EntityCollection();
							foreach ($hit->getValue($hitKey) as $collectionItemKey => $collectionItem) {
								$propertyValue->add(new $arguments['class'](
									... $this->resolveProperties($hit, $arguments['class'], $hitKey . '.' . $collectionItemKey),
								));
							}

						} elseif ($attribute->getName() === \Spameri\Elastic\Mapping\ElasticCollection::class) {
							$propertyValue = new \Spameri\Elastic\Entity\Collection\ElasticEntityCollection(
								// TODO musíme předat service nebo udělat novou kolekci co implementuje ElasticEntityCollectionInterface
							);
							foreach ($hit->getValue($hitKey) as $collectionItemKey => $collectionItem) {
								$propertyValue->add(new $arguments['class'](
									... $this->resolveProperties($hit, $arguments['class'], $hitKey . '.' . $collectionItemKey),
								));
							}

						} else {
							$propertyValue = new $arguments['class'](
								... $this->resolveProperties($hit, $propertyTypeName, $hitKey),
							);
						}
					}
				}

			} elseif (\class_exists($propertyTypeName)) {
				if (isset(\class_implements($propertyTypeName)[\Spameri\Elastic\Entity\ValueInterface::class]) === true) {
					$propertyValue = new $propertyTypeName(
						$hit->getValue($hitKey),
					);

				} else {
					$propertyValue = new $propertyTypeName(
						... $this->resolveProperties($hit, $propertyTypeName, $hitKey),
					);
				}

			} else {
				$propertyValue = $hit->getValue($hitKey);
			}

			$resolvedProperties[$property->getName()] = $propertyValue;

			unset($propertyValue);
		}

		return $resolvedProperties;
	}

}
