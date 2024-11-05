<?php declare(strict_types = 1);

namespace Spameri\Elastic\Factory;

interface EntityFactoryInterface
{

	public function create(
		\Spameri\ElasticQuery\Response\Result\Hit $hit,
		string $class,
		\Spameri\Elastic\EntityManager $entityManager,
	): \Spameri\Elastic\Entity\AbstractElasticEntity;

}
