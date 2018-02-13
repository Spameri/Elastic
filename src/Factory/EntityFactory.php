<?php declare(strict_types = 1);

namespace Spameri\Elastic\Factory;


class EntityFactory
{
	public function create(
		array $data
		, string $entityClass
	) : \Spameri\Elastic\Entity\IElasticEntity
	{

		$entity = new $entityClass();



		return $entity;
	}
}
