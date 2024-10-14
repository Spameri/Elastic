<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model\Insert;

class PrepareEntityArray
{

	public const ENTITY_CLASS = 'entityClass';

	private \Spameri\Elastic\EntityManager $entityManager;


	public function __construct(
		private readonly \Nette\DI\Container $container,
		private readonly \Spameri\Elastic\Model\IdentityMap $identityMap,
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

		return $this->iterateVariables($entityVariables);
	}


	/**
	 * @param array<mixed> $variables
	 * @return array<mixed>
	 */
	public function iterateVariables(
		array $variables,
	): array
	{
		$preparedArray = [];

		foreach ($variables as $key => $property) {
			if ($property instanceof \Spameri\Elastic\Entity\AbstractElasticEntity) {
				if ($this->identityMap->isChanged($property) === false) {
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
				$preparedArray[$key] = $this->iterateVariables($property->entityVariables());

			} elseif ($property instanceof \Spameri\Elastic\Entity\ValueInterface) {
				$preparedArray[$key] = $property->value();

			} elseif ($property instanceof \Spameri\Elastic\Entity\Collection\STIEntityCollection) {
				$preparedArray[$key] = [];
				foreach ($property as $item) {
					$iterateVariables = $this->iterateVariables($item->entityVariables());
					$iterateVariables[self::ENTITY_CLASS] = $item::class;
					$preparedArray[$key][] = $iterateVariables;
				}

			} elseif ($property instanceof \Spameri\Elastic\Entity\EntityCollectionInterface) {
				$preparedArray[$key] = [];
				/** @var \Spameri\Elastic\Entity\EntityInterface $item */
				foreach ($property as $item) {
					$preparedArray[$key][] = $this->iterateVariables($item->entityVariables());
				}

			} elseif ($property instanceof \Spameri\Elastic\Entity\ElasticEntityCollectionInterface) {
				$preparedArray[$key] = [];
				if ( ! $property->initialized()) {
					$preparedArray[$key] = $property->elasticIds();

				} else {
					/** @var \Spameri\Elastic\Entity\AbstractElasticEntity $item */
					foreach ($property as $item) {
						if ($this->identityMap->isChanged($item) === false) {
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
				$preparedArray[$key] = $this->iterateVariables($property);

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

			if ($property instanceof \Spameri\Elastic\Entity\STIEntityInterface) {
				$preparedArray[$key][self::ENTITY_CLASS] = $property::class;
			}
		}

		return $preparedArray;
	}

}
