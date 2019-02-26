<?php declare(strict_types = 1);

namespace Spameri\Elastic\Factory;


class DependencyLoader
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


	public function load(
		\Spameri\Elastic\Entity\IElasticEntity $entity
	) : void
	{
		$this->container->callInjects($entity);
	}

}
