<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model\Insert;

class PrepareEntityArray
{

	public const ENTITY_CLASS = 'entityClass';

	/**
	 * @var array<string, bool>
	 */
	private array $insertedEntities;


	public function __construct(
		private readonly \Spameri\Elastic\Model\ServiceLocatorInterface $serviceLocator,
	)
	{
	}


	/**
	 * @return array<mixed>
	 */
	public function prepare(
		\Spameri\Elastic\Entity\ElasticEntityInterface $entity,
		bool $hasSti = false,
	): array
	{
		$this->insertedEntities = [];
		$this->insertedEntities[$entity->id()->value()] = true;

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
				if (\in_array($property->id->value(), $this->insertedEntities, true)) {
					$preparedArray[$key] = $property->id()->value();

				} else {
					$preparedArray[$key] = $this->serviceLocator->locate($property)->insert($property);
					$this->insertedEntities[$property->id()->value()] = true;
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
				// TODO kolekce bez klíčů

			} elseif ($property instanceof \Spameri\Elastic\Entity\ElasticEntityCollectionInterface) {
				$preparedArray[$key] = [];
				if ( ! $property->initialized()) {
					$preparedArray[$key] = $property->elasticIds();

				} else {
					/** @var \Spameri\Elastic\Entity\AbstractElasticEntity $item */
					foreach ($property as $item) {
						if (\in_array($item->id()->value(), $this->insertedEntities)) {
							$preparedArray[$key][] = $item->id()->value();

						} else {
							$preparedArray[$key][] = $this->serviceLocator->locate($item)->insert($item);
							$this->insertedEntities[$item->id()->value()] = true;
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
		}

		return $preparedArray;
	}

}
