<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model;


class GetBy
{

	public function execute(
		array $options,
		\Elastica\Type $type
	) : array
	{
		$documents = $type->search($options);

		$data = FALSE;
		if ($documents->count()) {
			$document = $documents->getResults()[0];
			$data = $document->getData();
			$data['id'] = $document->getId();
		}

		if ( ! $data) {
			throw new \Spameri\Elastic\Exception\DocumentNotFound($type->getName());
		}

		return $data;
	}
}
