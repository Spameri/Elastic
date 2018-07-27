<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model;


class Insert
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
		\Spameri\Elastic\Model\Insert\PrepareEntityArray $prepareEntityArray,
		\Spameri\Elastic\ClientProvider $clientProvider
	)
	{
		$this->prepareEntityArray = $prepareEntityArray;
		$this->clientProvider = $clientProvider;
	}


	public function execute(
		\Spameri\Elastic\Entity\IElasticEntity $entity,
		string $index
	) : string
	{
		$entityArray = $this->prepareEntityArray->prepare($entity);
		unset($entityArray['id']);

		$document = new \Spameri\ElasticQuery\Document(
			$index,
			new \Spameri\ElasticQuery\Document\Body\Plain($entityArray),
			$index,
			$entity->id()->value()
		);

		$response = $this->clientProvider->client()->index($document->toArray());

		if (\in_array($response['result'], ['created', 'updated'], TRUE)) {
			return $response['_id'];
		}

		throw new \Spameri\Elastic\Exception\DocumentInsertFailed();
	}
}
