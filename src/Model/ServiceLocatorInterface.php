<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model;


interface ServiceLocatorInterface
{

	public function locate(
		\Spameri\Elastic\Entity\ElasticEntityInterface $entity
	) : \Spameri\Elastic\Model\ServiceInterface;


	public function locateByEntityClass(
		string $entityClass
	) : \Spameri\Elastic\Model\ServiceInterface;

}
