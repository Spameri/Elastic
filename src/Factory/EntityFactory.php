<?php declare(strict_types = 1);

namespace Spameri\Elastic\Factory;

readonly class EntityFactory implements \Spameri\Elastic\Factory\EntityFactoryInterface
{

	public function __construct(
		private \Spameri\Elastic\Reflection\Reflection $reflection,
		private \Spameri\Elastic\Model\IdentityMap $identityMap,
		private \Spameri\Elastic\Model\ChangeSet $changeSet,
	)
	{

	}

	/**
	 * @return \Generator<\Spameri\Elastic\Entity\AbstractElasticEntity>
	 */
	public function create(
		\Spameri\ElasticQuery\Response\Result\Hit $hit,
		string|null $class,
		\Spameri\Elastic\EntityManager|null $entityManager,
	): \Generator
	{
		if ($class === null) {
			throw new \InvalidArgumentException('Class must be set');
		}

		if ($entityManager === null) {
			throw new \InvalidArgumentException('EntityManager must be set');
		}

		if ($hit->getValue(\Spameri\Elastic\Model\Insert\PrepareEntityArray::ENTITY_CLASS) !== null) {
			$class = $hit->getValue(\Spameri\Elastic\Model\Insert\PrepareEntityArray::ENTITY_CLASS);
		}

		$entity = $this->identityMap->get(
			class: $class,
			id: $hit->id(),
		);
		if ($entity !== null) {
			yield $entity;
		}

		$properties = $this->resolveProperties(
			hit: $hit,
			class: $class,
			entityManager: $entityManager,
		);

		$entity = new $class(
			... $properties,
		);

		$this->changeSet->markExisting($entity);

		$this->identityMap->add($entity);

		yield $entity;
	}

	protected function resolveProperties(
		\Spameri\ElasticQuery\Response\Result\Hit $hit,
		string $class,
		\Spameri\Elastic\EntityManager $entityManager,
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

			} elseif (
				$propertyTypeName === \Spameri\Elastic\Entity\Property\Date::class
				|| $propertyTypeName === \Spameri\Elastic\Entity\Property\DateTime::class
			) {
				if ($value !== null) {
					$propertyValue = new $propertyTypeName(
						datetime: $value,
					);

					$this->changeSet->markExisting($propertyValue);

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
								... $this->resolveProperties(
									hit: $hit,
									class: $propertyTypeName,
								entityManager: $entityManager,
									parentFieldName: $hitKey,
								),
							);

							$this->changeSet->markExisting($propertyValue);
						}

					} elseif (
						$attribute->getName() === \Spameri\Elastic\Mapping\Collection::class
					) {
						$propertyValue = new $propertyTypeName();
						$this->changeSet->markExisting($propertyValue);
						if ($value !== null) {
							foreach ($value as $entityKey => $entity) {
								$collectionEntity = new $entity[\Spameri\Elastic\Model\Insert\PrepareEntityArray::ENTITY_CLASS](
									... $this->resolveProperties(
										hit: $hit,
										class: $entity[\Spameri\Elastic\Model\Insert\PrepareEntityArray::ENTITY_CLASS],
										entityManager: $entityManager,
										parentFieldName: $hitKey . '.' . $entityKey,
									),
								);

								$propertyValue->add($collectionEntity);

								$this->changeSet->markExisting($collectionEntity);
							}
						}

					} elseif (
						$attribute->getName() === \Spameri\Elastic\Mapping\STIEntity::class
					) {
						$propertyValue = new $value[\Spameri\Elastic\Model\Insert\PrepareEntityArray::ENTITY_CLASS](
							... $this->resolveProperties(
								hit: $hit,
								class: $propertyTypeName,
								entityManager: $entityManager,
								parentFieldName: $hitKey,
							),
						);

						$this->changeSet->markExisting($propertyValue);

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

					$this->changeSet->markExisting($propertyValue);

				} elseif (
					isset(\class_implements($propertyTypeName)[\Spameri\Elastic\Entity\ElasticEntityInterface::class]) === true
					&& \is_string($value) === true
				) {
					$propertyValue = $entityManager->find(
						id: $value,
						class: $propertyTypeName,
					);

				} else {
					$propertyValue = new $propertyTypeName(
						... $this->resolveProperties(
							hit: $hit,
							class: $propertyTypeName,
							entityManager: $entityManager,
							parentFieldName: $hitKey,
						),
					);

					$this->changeSet->markExisting($propertyValue);
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
