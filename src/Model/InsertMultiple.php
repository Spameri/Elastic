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


	public function __construct(
		\Spameri\Elastic\Model\Insert\PrepareEntityArray $prepareEntityArray
		, \Spameri\Elastic\ClientProvider $clientProvider
	)
	{
		$this->prepareEntityArray = $prepareEntityArray;
		$this->clientProvider = $clientProvider;
	}


	public function execute(
		\Spameri\Elastic\Entity\IElasticEntityCollection $entityCollection
		, string $index
	) : array
	{
		$documentsArray = [];
		foreach ($entityCollection as $entity) {
			$entityArray = $this->prepareEntityArray->prepare($entity);
			unset($entityArray['id']);

			$documentsArray[] = [
				'index' => [
					'_index' => $index,
					'_type'  => $index,
				]
			];
			$documentsArray[] = $entityArray;
		}

		$document = new \Spameri\ElasticQuery\Document\Bulk($documentsArray);

		$response = $this->clientProvider->client()->bulk($document->toArray());
		$this->clientProvider->client()->indices()->refresh(
			(
			new \Spameri\ElasticQuery\Document($index)
			)
				->toArray()
		);

		if ( ! $response['errors']) {
			return $response['items'];
		}

		throw new \Spameri\Elastic\Exception\DocumentInsertFailed();
	}
}
