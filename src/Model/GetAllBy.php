<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model;


class GetAllBy
{

	public function execute(
		array $options,
		\Elastica\Type $type
	) : \Elastica\ResultSet
	{
		return $type->search($options);
	}
}
