<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model;

class EntitySettingsLocator
{

	private \Nette\DI\Container $container;


	public function __construct(
		\Nette\DI\Container $container,
	)
	{
		$this->container = $container;
	}


	public function locate(string $indexName): \Spameri\ElasticQuery\Mapping\Settings
	{
		$indexConfigs = $this->locateAll();

		foreach ($indexConfigs as $indexConfig) {
			if (\strpos($indexConfig->provide()->indexName(), $indexName) !== FALSE) {
				return $indexConfig->provide();
			}
		}

		throw new \Spameri\Elastic\Exception\SettingsNotLocated($indexName);
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
