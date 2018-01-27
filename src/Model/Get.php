<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model;


class Get
{

	public function execute(
		\Spameri\Elastic\Entity\Property\ElasticId $id,
		\Elastica\Type $type
	) : array
	{
		$document = $type->getDocument($id->value());

		$data = FALSE;
		if ($document) {
			$data = $document->getData();
			$data['id'] = $document->getId();
		}

		return $data;
	}
}
