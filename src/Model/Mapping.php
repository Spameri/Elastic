<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model;


class Mapping
{

	/**
	 * @var \Spameri\Elastic\ClientProvider
	 */
	private $clientProvider;


	public function __construct(
		\Spameri\Elastic\ClientProvider $clientProvider
	)
	{
		$this->clientProvider = $clientProvider;
	}


	public function updateMapping(
		array $entitySettings
	) : void
	{
		$existingMapping = $this->clientProvider->client()->indices()->getMapping([
			'index' => $entitySettings['index'],
			'type' => $entitySettings['index'],
		]);
		$indexMapping = \reset($existingMapping);

		$newMappings = $this->compareMappings(
			[],
			$indexMapping['mappings'][$entitySettings['index']],
			$entitySettings
		);

	}


	public function compareMappings(
		$newMappings
		, $existingMappings
		, $entityMappings
	) : array
	{
		if (isset($entityMappings['properties'])) {
			foreach ($entityMappings['properties'] as $key => $entityMapping) {
				if ( ! isset($existingMappings['properties'][$key])) {
					$newMappings['properties'][$key] = $entityMapping['properties'];

				} else {
					$comparedMapping = $this->compareMappings(
						$newMappings,
						$existingMappings['properties'][$key],
						$entityMapping
					);

					if ($comparedMapping) {
						$newMappings[$key] = $comparedMapping;
					}
				}
			}
		}

		return $newMappings;
	}

}
