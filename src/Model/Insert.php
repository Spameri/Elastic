<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model;


class Insert
{

	/**
	 * @var \Spameri\Elastic\Model\Insert\PrepareEntityArray
	 */
	private $prepareEntityArray;


	public function __construct(
		\Spameri\Elastic\Model\Insert\PrepareEntityArray $prepareEntityArray
	)
	{
		$this->prepareEntityArray = $prepareEntityArray;
	}


	public function execute(
		\Spameri\Elastic\Entity\IElasticEntity $entity,
		\Elastica\Type $type
	) : string
	{
		$entityArray = $this->prepareEntityArray->prepare($entity);
		unset($entityArray['id']);

		if ($entity->id() instanceof \Spameri\Elastic\Entity\Property\EmptyElasticId) {
			$document = new \Elastica\Document('', $entityArray);

		} else {
			$document = new \Elastica\Document($entity->id()->value(), $entityArray);
		}

		$response = $type->addDocument($document);

		$type->getIndex()->refresh();

		if (\in_array($response->getStatus(), [\Nette\Http\Response::S200_OK, \Nette\Http\Response::S201_CREATED], TRUE)) {
			return $response->getData()['_id'];
		}

		throw new \Spameri\Elastic\Exception\DocumentInsertFailed();
	}
}
