<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model\Insert;


class ApplyTimestamp
{

	/**
	 * @var \Spameri\UserModule\Model\UserProvider
	 */
	protected $userProvider;

	/**
	 * @var \Kdyby\Clock\IDateTimeProvider
	 */
	protected $dateTimeProvider;


	public function __construct(
		\Spameri\UserModule\Model\UserProvider $userProvider
		, \Kdyby\Clock\IDateTimeProvider $dateTimeProvider
	)
	{
		$this->userProvider = $userProvider;
		$this->dateTimeProvider = $dateTimeProvider;
	}


	public function apply(\Spameri\Elastic\Entity\IElasticEntity $entity) : void
	{
		if ( ! $entity instanceof \Spameri\Elastic\Entity\ITrackedEntity) {
			return;
		}

		$timestamp = $this->dateTimeProvider->getDateTime()->format('Y-m-d H:i:s');
		$userId = $this->userProvider->getIdentity()->getId();

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
