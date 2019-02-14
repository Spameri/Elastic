<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model;


class DeleteMultiple
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


	/**
	 * @throws \Spameri\Elastic\Exception\ElasticSearch
	 * @throws \Spameri\Elastic\Exception\DocumentInsertFailed
	 */
	public function execute(
		\Spameri\Elastic\Entity\IElasticEntityCollection $entityCollection
		, string $index
	) : array
	{
		$documentsArray = [];
		/** @var \Spameri\Elastic\Entity\IElasticEntity $entity */
		foreach ($entityCollection as $entity) {
			$documentsArray[] = [
				'delete' => [
					'_index' => $index,
					'_type'  => $index,
					'_id' 	 => $entity->id()->value(),
				],
			];
		}

		if (\count($documentsArray)) {
			$document = new \Spameri\ElasticQuery\Document\Bulk($documentsArray);

			try {
				$response = $this->clientProvider->client()->bulk($document->toArray());

			} catch (\Elasticsearch\Common\Exceptions\ElasticsearchException $exception) {
				throw new \Spameri\Elastic\Exception\ElasticSearch($exception->getMessage());
			}

			try {
				$this->clientProvider->client()->indices()->refresh(
					(
					new \Spameri\ElasticQuery\Document($index)
					)
						->toArray()
				);

			} catch (\Elasticsearch\Common\Exceptions\ElasticsearchException $exception) {
				throw new \Spameri\Elastic\Exception\ElasticSearch($exception->getMessage());
			}

			if ( ! $response['errors']) {
				return $response['items'];
			}
		}

		throw new \Spameri\Elastic\Exception\DocumentInsertFailed();
	}
}
