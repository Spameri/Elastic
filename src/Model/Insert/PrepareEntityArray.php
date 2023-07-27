<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model\Insert;

class PrepareEntityArray
{

	/**
	 * @var \Spameri\Elastic\Model\ServiceLocatorInterface
	 */
	private $serviceLocator;


	public function __construct(
		\Spameri\Elastic\Model\ServiceLocatorInterface $serviceLocator
	)
	{
		$this->serviceLocator = $serviceLocator;
	}


	/**
	 * @return array<mixed>
	 */
	public function prepare(
		\Spameri\Elastic\Entity\ElasticEntityInterface $entity
	): array
	{
		return $this->iterateVariables($entity->entityVariables());
	}


	/**
	 * @param array<mixed> $variables
	 * @return array<mixed>
	 */
	public function iterateVariables(
		array $variables
	): array
	{
		$preparedArray = [];

		foreach ($variables as $key => $property) {
			if ($property instanceof \Spameri\Elastic\Entity\ElasticEntityInterface) {
				$preparedArray[$key] = $this->serviceLocator->locate($property)->insert($property);

			} elseif ($property instanceof \Spameri\Elastic\Entity\EntityInterface) {
				$preparedArray[$key] = $this->iterateVariables($property->entityVariables());

			} elseif ($property instanceof \Spameri\Elastic\Entity\ValueInterface) {
				$preparedArray[$key] = $property->value();

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
					/** @var \Spameri\Elastic\Entity\ElasticEntityInterface $item */
					foreach ($property as $item) {
						$preparedArray[$key][] = $this->serviceLocator->locate($item)->insert($item);
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
				|| $property === NULL
			) {
				$preparedArray[$key] = $property;

			} elseif (\is_array($property)) {
				$preparedArray[$key] = $this->iterateVariables($property);

			} elseif ($property instanceof \DateTimeInterface) {
				if ($property instanceof \Spameri\Elastic\Entity\DateTimeInterface) {
					$preparedArray[$key] = $property->format();
				} else {
					$preparedArray[$key] = $property->format(\Spameri\Elastic\Entity\Property\DateTime::FORMAT);
				}
			} else {
				if (\is_object($property)) {
					throw new \Spameri\Elastic\Exception\EntityIsNotValid(
						'Property ' . $key . ' in ' . \get_class($property) . ' is not supported.'
					);
				}

				throw new \Spameri\Elastic\Exception\EntityIsNotValid(
					'Property ' . $key . ' with value' . $property . ' is not supported.'
				);
			}
		}

		return $preparedArray;
	}

}
