<?php declare(strict_types = 1);

namespace Spameri\Elastic\Factory;


interface ICollectionFactory
{

	public function create(
		\Spameri\Elastic\Model\IService $service
		, array $elasticIds = NULL
		, \Spameri\Elastic\Entity\IElasticEntity ... $entityCollection
	) : \Spameri\Elastic\Entity\IElasticEntityCollection;

}
