<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model\Insert;


class ApplyTimestamp
{

	/**
	 * @var \Spameri\Elastic\Model\UserProviderInterface
	 */
	protected $userProvider;

	/**
	 * @var \Spameri\Elastic\Provider\DateTimeProvider
	 */
	protected $dateTimeProvider;


	public function __construct(
		\Spameri\Elastic\Model\UserProviderInterface $userProvider
		, \Spameri\Elastic\Provider\DateTimeProvider $dateTimeProvider
	)
	{
		$this->userProvider = $userProvider;
		$this->dateTimeProvider = $dateTimeProvider;
	}


	public function apply(
		\Spameri\Elastic\Entity\IElasticEntity $entity
	) : void
	{
		if ( ! $entity instanceof \Spameri\Elastic\Entity\ITrackedEntity) {
			return;
		}

		$timestamp = $this->dateTimeProvider->provide()->format(\Spameri\Elastic\Entity\Property\DateTime::FORMAT);
		$userId = new \Spameri\Elastic\Entity\Property\ElasticId(
			$this->userProvider->getId()
		);

		if ($entity->id() instanceof \Spameri\Elastic\Entity\Property\EmptyElasticId) {
			$entity->tracking()->initialize(
				$timestamp,
				$userId
			);

		} else {
			$entity->tracking()->edit(
				$timestamp,
				$userId
			);
		}
	}

}
