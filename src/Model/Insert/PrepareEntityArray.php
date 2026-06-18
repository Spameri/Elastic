<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model\Insert;

class PrepareEntityArray
{

	public const ENTITY_ID = 'entityId';
	public const ENTITY_CLASS = 'entityClass';

	private \Spameri\Elastic\EntityManager $entityManager;


	public function __construct(
		private readonly \Nette\DI\Container $container,
		private readonly \Spameri\Elastic\Model\IdentityMap $identityMap,
		private readonly \Spameri\Elastic\Reflection\Reflection $reflection,
	)
	{
	}

	private function getEntityManager(): \Spameri\Elastic\EntityManager
	{
		if (isset($this->entityManager)) {
			return $this->entityManager;
		}

		$this->entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);

		return $this->entityManager;
	}

	/**
	 * @return array<mixed>
	 */
	public function prepare(
		\Spameri\Elastic\Entity\AbstractElasticEntity $entity,
		bool $hasSti = false,
	): array
	{
		$this->identityMap->add($entity);

		$entityVariables = $entity->entityVariables();
		if ($hasSti === true) {
			$entityVariables[self::ENTITY_CLASS] = $entity::class;
		}

		$reflection = $this->reflection->createReflection($entity::class);

		return $this->iterateVariables($entityVariables, $reflection);
	}


	/**
	 * @param array<mixed> $variables
	 * @param \ReflectionClass<\Spameri\Elastic\Entity\AbstractElasticEntity>|null $reflectionClass
	 * @return array<mixed>
	 */
	public function iterateVariables(
		array $variables,
		\ReflectionClass|null $reflectionClass,
	): array
	{
		$preparedArray = [];

		foreach ($variables as $key => $property) {
			$attributes = [];
			if (
				$reflectionClass !== null
				&& $reflectionClass->hasProperty($key)
			) {
				$attributes = $reflectionClass->getProperty($key)->getAttributes();
			}

			foreach ($attributes as $attribute) {
				if ($attribute->getName() === \Spameri\Elastic\Mapping\Collection::class) {
					if ($property === null) {
						continue 2;
					}

					/** @var \Spameri\Elastic\Entity\EntityInterface $item */
					foreach ($property as $item) {
						$collectionItemData = $this->iterateVariables(
							$item->entityVariables(),
							$this->reflection->createReflection($item::class),
						);
						$collectionItemData[self::ENTITY_CLASS] = $item::class;

						$preparedArray[$key][] = $collectionItemData;
					}

					continue 2;

				} elseif ($attribute->getName() === \Spameri\Elastic\Mapping\STIEntity::class) {
					if ($property === null) {
						continue 2;
					}

					$preparedArray[$key] = $this->iterateVariables(
						$property->entityVariables(),
						$this->reflection->createReflection($property::class),
					);
					$preparedArray[$key][self::ENTITY_CLASS] = $property::class;

					continue 2;

				} elseif ($attribute->getName() === \Spameri\Elastic\Mapping\STIElasticEntity::class) {
					if ($property === null) {
						continue 2;
					}

					$preparedArray[$key][self::ENTITY_CLASS] = $property::class;

					if (
						$this->identityMap->isPersisting($property) === true
						|| $this->identityMap->isChanged($property) === false
					) {
						$preparedArray[$key][self::ENTITY_ID] = $property->id()->value();

					} else {
						$preparedArray[$key][self::ENTITY_ID] = $this->getEntityManager()->persist($property);
						$this->identityMap->add($property);
					}

					continue 2;

				} elseif ($attribute->getName() === \Spameri\Elastic\Mapping\Ignored::class) {
					continue 2;
				}
			}

			if ($property instanceof \Spameri\Elastic\Entity\AbstractElasticEntity) {
				if (
					$this->identityMap->isPersisting($property) === true
					|| $this->identityMap->isChanged($property) === false
				) {
					$preparedArray[$key] = $property->id()->value();

				} else {
					$preparedArray[$key] = $this->getEntityManager()->persist($property);
					$this->identityMap->add($property);
				}

			} elseif ($property instanceof \Spameri\Elastic\Entity\ElasticEntityInterface) {
				throw new \Spameri\Elastic\Exception\DocumentInsertFailed(
					'Entity ' . $property::class . ' must be extend AbstractElasticEntity.',
				);

			} elseif ($property instanceof \Spameri\Elastic\Entity\EntityInterface) {
				$preparedArray[$key] = $this->iterateVariables(
					$property->entityVariables(),
					$this->reflection->createReflection($property::class),
				);

			} elseif ($property instanceof \Spameri\Elastic\Entity\ValueInterface) {
				$preparedArray[$key] = $property->value();

			} elseif ($property instanceof \Spameri\Elastic\Entity\Collection\STIEntityCollection) {
				$preparedArray[$key] = [];
				foreach ($property as $item) {
					$iterateVariables = $this->iterateVariables(
						$item->entityVariables(),
						$this->reflection->createReflection($item::class),
					);
					$iterateVariables[self::ENTITY_CLASS] = $item::class;
					$preparedArray[$key][] = $iterateVariables;
				}

			} elseif ($property instanceof \Spameri\Elastic\Entity\EntityCollectionInterface) {
				$preparedArray[$key] = [];
				/** @var \Spameri\Elastic\Entity\EntityInterface $item */
				foreach ($property as $item) {
					$preparedArray[$key][] = $this->iterateVariables(
						$item->entityVariables(),
						$this->reflection->createReflection($item::class),
					);
				}

			} elseif ($property instanceof \Spameri\Elastic\Entity\ElasticEntityCollectionInterface) {
				$preparedArray[$key] = [];
				if ( ! $property->initialized()) {
					$preparedArray[$key] = $property->elasticIds();

				} else {
					/** @var \Spameri\Elastic\Entity\AbstractElasticEntity $item */
					foreach ($property as $item) {
						if (
							$this->identityMap->isPersisting($item) === true
							|| $this->identityMap->isChanged($item) === false
						) {
							$preparedArray[$key][] = $item->id()->value();

						} else {
							$preparedArray[$key][] = $this->getEntityManager()->persist($item);
							$this->identityMap->add($item);
						}
					}
				}

			} elseif ($property instanceof \Spameri\Elastic\Entity\ValueCollectionInterface) {
				$preparedArray[$key] = [];
				/** @var \Spameri\Elastic\Entity\ValueInterface|mixed $value */
				foreach ($property as $value) {
					if ($value instanceof \Spameri\Elastic\Entity\ValueInterface) {
						$preparedArray[$key][] = $value->value();

					} else {
						$preparedArray[$key][] = $value;
					}
				}

			} elseif (
				\is_string($property)
				|| \is_int($property)
				|| \is_bool($property)
				|| \is_float($property)
				|| $property === null
			) {
				$preparedArray[$key] = $property;

			} elseif (\is_array($property)) {
				$preparedArray[$key] = $this->iterateVariables(
					$property,
					null,
				);

			} elseif ($property instanceof \Spameri\Elastic\Entity\DateTimeInterface) {
				$preparedArray[$key] = $property->format();

			} elseif ($property instanceof \DateTime) {
				$preparedArray[$key] = $property->format(\Spameri\Elastic\Entity\Property\DateTime::FORMAT);

			} elseif (
				$property instanceof \BackedEnum
			) {
				$preparedArray[$key] = $property->value;

			} else {
				if (\is_object($property)) {
					throw new \Spameri\Elastic\Exception\EntityIsNotValid(
						'Property ' . $key . ' in ' . $property::class . ' is not supported.',
					);
				}

				throw new \Spameri\Elastic\Exception\EntityIsNotValid(
					'Property ' . $key . ' with value' . $property . ' is not supported.',
				);
			}
		}

		return $preparedArray;
	}

}
