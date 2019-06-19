<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model;


class InsertMultiple
{

	/**
	 * @var \Spameri\Elastic\Model\Insert\PrepareEntityArray
	 */
	private $prepareEntityArray;

	/**
	 * @var \Spameri\Elastic\ClientProvider
	 */
	private $clientProvider;

	/**
	 * @var \Spameri\ElasticQuery\Response\ResultMapper
	 */
	private $resultMapper;


	public function __construct(
		\Spameri\Elastic\Model\Insert\PrepareEntityArray $prepareEntityArray
		, \Spameri\Elastic\ClientProvider $clientProvider
		, \Spameri\ElasticQuery\Response\ResultMapper $resultMapper
	)
	{
		$this->prepareEntityArray = $prepareEntityArray;
		$this->clientProvider = $clientProvider;
		$this->resultMapper = $resultMapper;
	}


	/**
	 * @throws \Spameri\Elastic\Exception\ElasticSearch
	 * @throws \Spameri\Elastic\Exception\DocumentInsertFailed
	 */
	public function execute(
		\Spameri\Elastic\Entity\IElasticEntityCollection $entityCollection
		, string $index
		, ?string $type = NULL
	) : \Spameri\ElasticQuery\Response\ResultBulk
	{
		if ($type === NULL) {
			$type = $index;
		}

		$documentsArray = [];

		foreach ($entityCollection as $entity) {
			$entityArray = $this->prepareEntityArray->prepare($entity);
			unset($entityArray['id']);

			$documentsArray[] = [
				'index' => [
					'_index' => $index,
					'_type'  => $type,
				],
			];
			$documentsArray[] = $entityArray;
		}

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

		return $this->resultMapper->mapBulkResult($response);
	}

}
