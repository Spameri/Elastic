<?php declare(strict_types = 1);

namespace Spameri\Elastic\DI;


class SpameriElasticSearchExtension extends \Nette\DI\CompilerExtension
{

	public $defaults = [
		'host' 		=> 'localhost',
		'port' 		=> 9200,
		'debug'		=> FALSE,
		'synonymPath' => NULL,
		'entities' 	=> [],
		'settings' 	=> [
			'analysis' => [
				'analyzer' => [
					'defaultAnalyzer' => [
						'tokenizer' => 'standard',
						'filter' => [
							'lowercase',
							'removeDuplicities',
							'asciifolding',
						],
					],
					'czechStemmer' => [
						'tokenizer' => 'standard',
						'filter' 	=> [
							'lowercase',
							'stopWords_CZ',
							'stemmer_CZ',
							'removeDuplicities',
							'asciifolding',
						],
					],
					'czechDictionary' => [
						'tokenizer' => 'standard',
						'filter' => [
							'lowercase',
							'stopWords_CZ',
							'dictionary_CZ',
							'lowercase',
							'stopWords_CZ',
							'removeDuplicities',
							'asciifolding',
						],
					],
					'czechSynonym' => [
						'tokenizer' => 'standard',
						'filter' => [
							'lowercase',
							'synonym',
							'lowercase',
							'stopWords_CZ',
							'removeDuplicities',
							'asciifolding',
						],
					],
				],
			],
			'tokenizer' => [],
			'filter' => [
				'stopWords_CZ' => [
					'type' => 'stop',
					'stopwords' => [
						'právě',
						'že',
						'_czech_',
					],
					'ignore_case' => TRUE,
				],
				'dictionary_CZ' => [
					'type' => 'hunspell',
					'locale' => 'cs_CZ',
					'dedup' => TRUE,
					'recursion_level' => 0,
				],
				'removeDuplicities' => [
					'type' => 'unique',
					'only_on_same_position' => TRUE,
				],
				'synonym' => [
					'type' => 'synonym',
					'synonyms_path' => '',
				],
				'stemmer_CZ' => [
					'type' => 'stemmer',
					'name' => 'Czech',
				],
			],
		],
	];


	public function loadConfiguration() : void
	{
		parent::loadConfiguration();

		/** @var array $config */
		$config = \Nette\DI\Config\Helpers::merge($this->getConfig(), $this->defaults);

		$config = $this->toggleSynonymAnalyzer($config);
		$this->compiler->getContainerBuilder()->parameters['spameriElasticSearch'] = $config;

		$services = $this->loadFromFile(__DIR__ . '/../Config/Elastic.neon');

		$services = $this->toggleDebugBar($config, $services);

		if ( ! \class_exists(\Symfony\Component\Console\Command\Command::class)) {
			$services = $this->removeCommandDefinitions($services);
		}

		if ( ! \class_exists(\Nette\Security\User::class)) {
			unset($services['services']['userProvider']);
		}

		$this->setConfigOptions($services, $config);

		$this->compiler->parseServices(
			$this->getContainerBuilder(),
			$services,
			$this->name
		);
	}


	public function toggleSynonymAnalyzer(
		array $config
	) : array
	{
		if ( ! $config['synonymPath']) {
			unset($config['settings']['analysis']['analyzer']['czechSynonym']);
			unset($config['settings']['filter']['synonym']);

		} else {
			$config['settings']['filter']['synonym']['synonyms_path'] = $config['synonymPath'];
		}

		return $config;
	}


	public function setConfigOptions(
		array $services,
		array $config
	) : void
	{
		$updateMapping = $services['services']['updateMapping']['class'];
		$updateMapping->arguments[0] = $config['entities'];

		$createIndex = $services['services']['createIndex']['class'];
		$createIndex->arguments[0] = $config['entities'];

		$validateMapping = $services['services']['validateMapping']['class'];
		$validateMapping->arguments[0] = $config['entities'];
		$validateMapping->arguments[1] = $config['settings'];

		$neonSettingsProvider = $services['services']['neonSettingsProvider']['class'];
		$neonSettingsProvider->arguments[0] = $config['host'];
		$neonSettingsProvider->arguments[1] = $config['port'];
	}


	public function toggleDebugBar(
		array $config,
		array $services
	) : array
	{
		if ( ! $config['debug']) {
			unset($services['tracy']);
			unset($services['services']['elasticPanelLogger']);
			unset($services['services']['nullLogger']);
			unset($services['services']['elasticPanel']);
			unset($services['services']['clientBuilder']['setup']);

		} else {
			$this->getContainerBuilder()
				->getDefinition('tracy.bar')
				->addSetup('addPanel', ['@' . $this->prefix('elasticPanel')])
			;
		}

		return $services;
	}


	public function removeCommandDefinitions(
		array $services
	): array
	{
		$iterableServices = $services['services'];
		foreach ($iterableServices as $serviceKey => $serviceArray) {
			if (isset($serviceArray['tags'])) {
				foreach ($serviceArray['tags'] as $tag) {
					if ($tag === 'kdyby.console.command') {
						unset($services[$serviceKey]);
					}
				}
			}
		}

		return $services;
	}

}
