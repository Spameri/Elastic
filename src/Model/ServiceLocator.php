<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model;

class ServiceLocator implements ServiceLocatorInterface
{

	/**
	 * @var \Nette\DI\Container
	 */
	private $container;

	/**
	 * @var array<string>
	 */
	private array $services;


	public function __construct(
		\Nette\DI\Container $container
	)
	{
		$this->container = $container;
		$this->services = $container->findByType(\Spameri\Elastic\Model\ServiceInterface::class);
	}


	public function locate(
		\Spameri\Elastic\Entity\ElasticEntityInterface $entity
	): \Spameri\Elastic\Model\ServiceInterface
	{
		$entityName = \get_class($entity);
		$serviceName = \str_replace('Entity', 'Model', $entityName . 'Service');

		/** @var \Spameri\Elastic\Model\ServiceInterface $service */
		$service = $this->container->getByType($serviceName);

		return $service;
	}


	public function locateByEntityClass(
		string $entityClass
	): \Spameri\Elastic\Model\ServiceInterface
	{
		$serviceName = \str_replace('Entity', 'Model', $entityClass . 'Service');

		$serviceName = \str_replace('Interface', '', $serviceName);
		$serviceName = \str_replace('Abstract', '', $serviceName);
		$serviceName = \str_replace('Trait', '', $serviceName);

		/** @var \Spameri\Elastic\Model\ServiceInterface $service */
		$service = $this->container->getByType($serviceName);

		return $service;
	}


	public function locateByIndex(string $index): \Spameri\Elastic\Model\ServiceInterface|null
	{
		foreach ($this->services as $serviceType) {
			$service = $this->container->getService($serviceType);
			if (($service instanceof \Spameri\Elastic\Model\AbstractBaseService) === false) {
				continue;
			}

			if ($service->index === $index) {
				return $service;
			}
		}

		return null;
	}
}
