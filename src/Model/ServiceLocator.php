<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model;


class ServiceLocator
{

	/**
	 * @var \Nette\DI\Container
	 */
	private $container;


	public function __construct(
		\Nette\DI\Container $container
	)
	{
		$this->container = $container;
	}


	public function locate(
		\Spameri\Elastic\Entity\IElasticEntity $entity
	) : \Spameri\Elastic\Model\IService
	{
		$entityName = \get_class($entity);
		$serviceName = \str_replace('Entity', 'Model' , $entityName . 'Service');

		/** @var $service \Spameri\Elastic\Model\IService */
		$service = $this->container->getByType($serviceName);

		return $service;
	}


	public function locateByEntityClass(
		string $entityClass
	) : \Spameri\Elastic\Model\IService
	{
		$serviceName = \str_replace('Entity', 'Model', $entityClass . 'Service');

		/** @var $service \Spameri\Elastic\Model\IService */
		$service = $this->container->getByType($serviceName);

		return $service;
	}

}
