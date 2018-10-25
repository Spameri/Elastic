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
		try {
			$this->clientProvider->client()->indices()->putMapping([
				'index' => $entitySettings['index'],
				'type' => $entitySettings['index'],
				'body' => $entitySettings['properties']
			]);

		} catch (\Elasticsearch\Common\Exceptions\BadRequest400Exception $exception) {
			throw new \Spameri\Elastic\Exception\ConflictingMapping($entitySettings['index'], $exception);
		}
	}

}
