<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model\Insert;


class PrepareEntityArray
{

	/**
	 * @var \Spameri\Elastic\Model\Insert\ApplyTimestamp
	 */
	protected $applyTimestamp;
	/**
	 * @var \Spameri\Elastic\Model\ServiceLocator
	 */
	private $serviceLocator;


	public function __construct(
		ApplyTimestamp $applyTimestamp
		, \Spameri\Elastic\Model\ServiceLocator $serviceLocator
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

		return $this->iterateVariables($entity);
	}


	public function iterateVariables(
		\Spameri\Elastic\Entity\IEntity $entity
	) : array
	{
		$preparedArray = [];

		foreach ($entity->entityVariables() as $key => $property) {
			if ($property instanceof \Spameri\Elastic\Entity\IEntity) {
				$preparedArray[$key] = $this->iterateVariables($property);

			} elseif ($property instanceof \Spameri\Elastic\Entity\IValue) {
				$preparedArray[$key] = $property->value();

			} elseif ($property instanceof \Spameri\Elastic\Entity\IEntityCollection) {
				$preparedArray[$key] = [];
				foreach ($property as $item) {
					$preparedArray[$key][] = $this->iterateVariables($item);
				}

			} elseif ($property instanceof \Spameri\Elastic\Entity\IValueCollection) {
				/**
				 * @var $value \Spameri\Elastic\Entity\IValue
				 */
				$preparedArray[$key] = [];
				foreach ($property as $value) {
					$preparedArray[$key][] = $value->value();
				}

			} elseif ($property instanceof \Spameri\Elastic\Entity\IElasticEntity) {
				$this->serviceLocator->locate($property)->insert($property);

			} elseif (
				is_string($property)
				|| is_int($property)
				|| is_bool($property)
				|| $property === NULL
			) {
				$preparedArray[$key] = $property;

			} elseif ($property instanceof \DateTime) {
				$preparedArray[$key] = $property->format('Y-m-d H:i:s');

			} else {
				if (is_object($property)) {
					throw new \Spameri\Elastic\Exception\EntityIsNotValid('Property ' . $key . ' in ' . get_class($property) . ' is not supported.');
				} else {
					throw new \Spameri\Elastic\Exception\EntityIsNotValid('Property ' . $key . ' with value' . $property . ' is not supported.');
				}
			}
		}

		return $preparedArray;
	}
}
