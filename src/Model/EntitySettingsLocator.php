<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model;

class EntitySettingsLocator
{

	private \Nette\DI\Container $container;


	public function __construct(
		\Nette\DI\Container $container
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


	public function locateAll(): array
	{
		/** @var array<\Spameri\Elastic\Settings\IndexConfigInterface> $settings */
		$settings = $this->container->getByType(\Spameri\Elastic\Settings\IndexConfigInterface::class);

		return $settings;
	}

}
