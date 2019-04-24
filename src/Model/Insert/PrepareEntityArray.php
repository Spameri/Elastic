<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model\Insert;


class PrepareEntityArray
{

	/**
	 * @var \Spameri\Elastic\Model\Insert\ApplyTimestamp
	 */
	protected $applyTimestamp;

	/**
	 * @var \Spameri\Elastic\Model\ServiceLocatorInterface
	 */
	private $serviceLocator;


	public function __construct(
		ApplyTimestamp $applyTimestamp
		, \Spameri\Elastic\Model\ServiceLocatorInterface $serviceLocator
	)
	{
		$this->applyTimestamp = $applyTimestamp;
		$this->serviceLocator = $serviceLocator;
	}


	public function prepare(
		\Spameri\Elastic\Entity\IElasticEntity $entity
	) : array
	{
		$this->applyTimestamp->apply($entity);

		return $this->iterateVariables($entity->entityVariables());
	}


	public function iterateVariables(
		array $variables
	) : array
	{
		$preparedArray = [];

		foreach ($variables as $key => $property) {
			if ($property instanceof \Spameri\Elastic\Entity\IElasticEntity) {
				$preparedArray[$key] = $this->serviceLocator->locate($property)->insert($property);

			} elseif ($property instanceof \Spameri\Elastic\Entity\IEntity) {
				$preparedArray[$key] = $this->iterateVariables($property->entityVariables());

			} elseif ($property instanceof \Spameri\Elastic\Entity\IValue) {
				$preparedArray[$key] = $property->value();

			} elseif ($property instanceof \Spameri\Elastic\Entity\IEntityCollection) {
				$preparedArray[$key] = [];
				/** @var \Spameri\Elastic\Entity\IEntity $item */
				/** @var \Spameri\Elastic\Entity\IEntityCollection $property */
				foreach ($property as $item) {
					$preparedArray[$key][] = $this->iterateVariables($item->entityVariables());
				}

			} elseif ($property instanceof \Spameri\Elastic\Entity\IElasticEntityCollection) {
				$preparedArray[$key] = [];
				if ( ! $property->initialized()) {
					$preparedArray[$key] = $property->elasticIds();

				} else {
					/** @var \Spameri\Elastic\Entity\IElasticEntity $item */
					/** @var \Spameri\Elastic\Entity\IElasticEntityCollection $property */
					foreach ($property as $item) {
						$preparedArray[$key][] = $this->serviceLocator->locate($item)->insert($item);
					}
				}

			} elseif ($property instanceof \Spameri\Elastic\Entity\IValueCollection) {
				$preparedArray[$key] = [];
				/** @var \Spameri\Elastic\Entity\IValue $value */
				/** @var \Spameri\Elastic\Entity\IValueCollection $property */
				foreach ($property as $value) {
					if ($value instanceof \Spameri\Elastic\Entity\IValue) {
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

			} elseif ($property instanceof \Spameri\Elastic\Entity\DateTimeInterface) {
				$preparedArray[$key] = $property->format();

			} elseif ($property instanceof \DateTime) {
				$preparedArray[$key] = $property->format(\Spameri\Elastic\Entity\Property\DateTime::FORMAT);

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
