<?php

declare(strict_types = 1);

namespace Spameri\Elastic\Model;

readonly class EntitySettingsLocator
{

	public function __construct(
		private \Nette\DI\Container $container,
	)
	{
	}


	public function locate(string $indexName): \Spameri\ElasticQuery\Mapping\Settings
	{
		$indexConfigs = $this->locateAll();

		/** @var \Spameri\Elastic\Settings\IndexConfigInterface $indexConfig */
		foreach ($indexConfigs as $indexConfig) {
			if (\str_contains($indexConfig->provide()->indexName(), $indexName)) {
				return $indexConfig->provide();
			}
		}

		throw new \Spameri\Elastic\Exception\SettingsNotLocated($indexName);
	}


	public function locateByEntityClass(string $entityClass): \Spameri\ElasticQuery\Mapping\Settings
	{
		$indexConfigs = $this->locateAll();

		/** @var \Spameri\Elastic\Settings\AbstractIndexConfig $indexConfig */
		foreach ($indexConfigs as $indexConfig) {
if ($indexConfig->entityClass() === $entityClass) {
return $indexConfig->provide();
			}
		}

		throw new \Spameri\Elastic\Exception\SettingsNotLocated($entityClass);
	}


	/**
	 * @return \Generator<\Spameri\Elastic\Settings\IndexConfigInterface>
	 */
	public function locateAll(): \Generator
	{
		$serviceNames = $this->container->findByType(\Spameri\Elastic\Settings\IndexConfigInterface::class);
		foreach ($serviceNames as $serviceName) {
			yield $this->container->getService($serviceName);
		}
	}

}
