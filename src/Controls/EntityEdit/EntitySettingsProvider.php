<?php declare(strict_types = 1);

namespace Spameri\Elastic\Controls\EntityEdit;


class EntitySettingsProvider
{

	/**
	 * @var array
	 */
	protected $entitySettings;


	public function __construct(
		array $entitySettings
	)
	{
		$this->entitySettings = $entitySettings;
	}


	public function getEntitySettings(string $entity = NULL) : array
	{
		if ($entity) {
			return $this->entitySettings[$entity];

		} else {
			return $this->entitySettings;
		}
	}


	public function setEntitySettings(array $entitySettings)
	{
		$this->entitySettings = $entitySettings;
	}

}
