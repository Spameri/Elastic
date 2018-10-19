<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model;


interface ServiceLocatorInterface
{

	public function locate(
		\Spameri\Elastic\Entity\IElasticEntity $entity
	) : \Spameri\Elastic\Model\IService;


	public function locateByEntityClass(
		string $entityClass
	) : \Spameri\Elastic\Model\IService;

}
