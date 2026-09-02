<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model;

readonly class Get
{

	public function __construct(
		private \Spameri\Elastic\ClientProvider $clientProvider,
		private \Spameri\ElasticQuery\Response\ResultMapper $resultMapper,
	)
	{
	}


	/**
	 * @throws \Spameri\Elastic\Exception\DocumentNotFound when the document is not there
	 * @throws \Spameri\Elastic\Exception\ElasticSearch
	 */
	public function execute(
		\Spameri\Elastic\Entity\Property\ElasticId $id,
		string $index,
	): \Spameri\ElasticQuery\Response\ResultSingle
	{
		try {
			$response = $this->clientProvider->client()->get(
				(
					new \Spameri\ElasticQuery\Document(
						$index,
						null,
						$id->value(),
					)
				)
					->toArray(),
			)
			;

		} catch (\Elastic\Elasticsearch\Exception\ClientResponseException $exception) {
			if ($exception->getCode() !== 404) {
				throw new \Spameri\Elastic\Exception\ElasticSearch($exception->getMessage());
			}

			// The client reports a missing document as a 404 and throws. Saying so with the
			// exception the library already has for it makes this path agree with every other one:
			// findOneBy() raises DocumentNotFound for the same situation, and AbstractBaseService
			// means to as well - its found() check cannot be reached today, because the blanket
			// wrap below turns a 404 into ElasticSearch and its catch rethrows that first.
			throw new \Spameri\Elastic\Exception\DocumentNotFound(
				$index . ' with id ' . $id->value(),
			);

		} catch (\Elastic\Elasticsearch\Exception\ElasticsearchException $exception) {
			throw new \Spameri\Elastic\Exception\ElasticSearch($exception->getMessage());
		}

		return $this->resultMapper->mapSingleResult($response->asArray());
	}

}
