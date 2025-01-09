<?php declare(strict_types = 1);

namespace Spameri\Elastic\Factory;

readonly class EntityFactory implements \Spameri\Elastic\Factory\EntityFactoryInterface
{

	public function __construct(
		private \Spameri\Elastic\Reflection\Reflection $reflection,
		private \Spameri\Elastic\Model\IdentityMap $identityMap,
		private \Spameri\Elastic\Model\ChangeSet $changeSet,
		private \Nette\DI\Container $container,
	)
	{

	}

	/**
	 * @template T of \Spameri\Elastic\Entity\AbstractElasticEntity
	 * @param class-string<T> $class
	 * @return T
	 */
	public function create(
		\Spameri\ElasticQuery\Response\Result\Hit $hit,
		string $class,
		\Spameri\Elastic\EntityManager $entityManager,
	): \Spameri\Elastic\Entity\AbstractElasticEntity
	{
		if ($hit->getValue(\Spameri\Elastic\Model\Insert\PrepareEntityArray::ENTITY_CLASS) !== null) {
			$class = $hit->getValue(\Spameri\Elastic\Model\Insert\PrepareEntityArray::ENTITY_CLASS);
		}

		$entity = $this->identityMap->get(
			class: $class,
			id: $hit->id(),
		);
		if ($entity !== null) {
			return $entity;
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

		return $entity;
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
				$setNull = true;

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
                        /** @var array{class: class-string} $arguments */
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
						$attribute->getName() === \Spameri\Elastic\Mapping\ElasticCollection::class
					) {
                        /** @var array{class: class-string} $arguments */
						$arguments = $attribute->getArguments();

						$propertyValue = new $propertyTypeName($entityManager, $arguments['class']);
						$this->changeSet->markExisting($propertyValue);

						if ($value !== null) {
							foreach ($value as $entityKey => $entityId) {
								$collectionElasticEntity = $entityManager->find(
									id: $entityId,
									class: $arguments['class'],
								);

                                if ($collectionElasticEntity === null) {
                                    continue;
                                }

								$propertyValue->add($collectionElasticEntity);

								$this->changeSet->markExisting($collectionElasticEntity);
							}
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
                        $attribute->getName() === \Spameri\Elastic\Mapping\STIElasticEntity::class
                    ) {
                        $propertyValue = $entityManager->find(
							id: $value[\Spameri\Elastic\Model\Insert\PrepareEntityArray::ENTITY_ID],
	                        class: $value[\Spameri\Elastic\Model\Insert\PrepareEntityArray::ENTITY_CLASS],
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

				} elseif ($this->container->getByType($propertyTypeName, false) !== null) {
					$propertyValue = $this->container->getByType($propertyTypeName);

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

			if (
				isset($propertyValue) || isset($setNull)
			) {
				$resolvedProperties[$property->getName()] = $propertyValue;
			}

			unset($propertyValue, $setNull);
		}

		return $resolvedProperties;
	}

}
