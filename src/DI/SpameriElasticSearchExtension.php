<?php declare(strict_types = 1);

namespace Spameri\Elastic\DI;

class SpameriElasticSearchExtension extends \Nette\DI\CompilerExtension
{

	/**
	 * @var array<mixed>
	 */
	public array $defaults = [
		'host' => 'localhost',
		'port' => 9200,
		'debug' => false,
		'version' => \Spameri\ElasticQuery\Response\Result\Version::ELASTIC_VERSION_ID_8,
		'synonymPath' => null,
		'entities' => [],
	];


	public function loadConfiguration(): void
	{
		parent::loadConfiguration();

		/** @var array<mixed> $config */
		$config = \Nette\DI\Config\Helpers::merge($this->getConfig(), $this->defaults);

		$this->compiler->getContainerBuilder()->parameters['spameriElasticSearch'] = $config;

		$services = $this->loadFromFile(__DIR__ . '/../Config/Elastic.neon');

		$services = $this->toggleDebugBar($config, $services);

		$services = $this->setConfigOptions($services, $config);

		$aliasedServices = [];
		foreach ($services['services'] as $key => $service) {
			$aliasedServices['spameriElasticSearch.' . $key] = $service;
		}

		$this->compiler->loadDefinitionsFromConfig(
			$aliasedServices,
		);

	}


	public function setConfigOptions(
		array $services,
		array $config,
	): array
	{
		$neonSettingsProvider = $services['services']['neonSettingsProvider']['factory'];
		$neonSettingsProvider->arguments[0] = $config['host'];
		$neonSettingsProvider->arguments[1] = $config['port'];

		$versionProvider = $services['services']['versionProvider']['factory'];
		$versionProvider->arguments[0] = $config['version'];

		return $services;
	}


	/**
	 * @param array<mixed> $config
	 * @param array<mixed> $services
	 * @return array<mixed>
	 */
	public function toggleDebugBar(
		array $config,
		array $services,
	): array
	{
		if ($config['debug'] === false) {
			unset(
				$services['tracy'],
				$services['services']['elasticPanelLogger'],
				$services['services']['nullLogger'],
				$services['services']['elasticPanel'],
				$services['services']['clientBuilder']['setup'],
			);

		} else {
			/** @var \Nette\DI\Definitions\ServiceDefinition $definition */
			$definition = $this->getContainerBuilder()
				->getDefinition('tracy.bar')
			;
			$definition
				->addSetup('addPanel', ['@' . $this->prefix('elasticPanel')]);
		}

		return $services;
	}

}
