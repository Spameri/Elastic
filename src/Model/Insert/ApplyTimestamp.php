<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model\Insert;


class ApplyTimestamp
{

	/**
	 * @var \Spameri\Elastic\Model\UserProvider
	 */
	protected $userProvider;

	/**
	 * @var \Kdyby\DateTimeProvider\Provider\ConstantProvider
	 */
	protected $dateTimeProvider;


	public function __construct(
		\Spameri\Elastic\Model\UserProvider $userProvider
		, \Kdyby\DateTimeProvider\Provider\ConstantProvider $dateTimeProvider
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

		$timestamp = $this->dateTimeProvider->getDateTime()->format('Y-m-d H:i:s');
		$identity = $this->userProvider->getIdentity();
		$userId = $identity ? $identity->getId() : NULL;

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
