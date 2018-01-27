<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model;


class Delete
{

	public function execute(
		\Spameri\Elastic\Entity\Property\IElasticId $id,
		\Elastica\Type $type
	) : bool
	{
		$response = $type->deleteById($id->value());

		if ($response->getStatus() === \Nette\Http\Response::S200_OK) {
			return TRUE;

		} else {
			return FALSE;
		}
	}
}
