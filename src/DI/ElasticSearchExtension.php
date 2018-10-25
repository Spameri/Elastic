<?php declare(strict_types = 1);

namespace Spameri\Elastic\DI;


class ElasticSearchExtension extends \Nette\DI\CompilerExtension
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


	public function loadConfiguration(): void
	{
		parent::loadConfiguration();

		/** @var array $config */
		$config = \Nette\DI\Config\Helpers::merge($this->getConfig(), $this->defaults);

		$config = $this->toggleSynonymAnalyzer($config);
		$this->compiler->getContainerBuilder()->parameters['elasticSearch'] = $config;

		$services = $this->loadFromFile(__DIR__ . '/../Config/Elastic.neon');

		$services = $this->toggleDebugBar($config, $services);

		$this->setConfigOptions($services, $config);

		$this->compiler->parseServices(
			$this->getContainerBuilder(),
			$services,
			$this->name
		);
	}


	public static function register(
		$config
	): void
	{
		$config->onCompile[] = static function (
			$config,
			\Nette\DI\Compiler $compiler
		) {
			$compiler->addExtension('elasticSearch', new ElasticSearchExtension());
		};
	}


	public function toggleSynonymAnalyzer(array $config): array
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
	): void
	{
		/** @var \Nette\DI\Statement $entitySettingsProvider */
		$entitySettingsProvider = $services['services']['entitySettingsProvider']['class'];
		$entitySettingsProvider->arguments[0] = $config['entities'];

		$updateMapping = $services['services']['updateMapping']['class'];
		$updateMapping->arguments[0] = $config['entities'];

		$createIndex = $services['services']['createIndex']['class'];
		$createIndex->arguments[0] = $config['entities'];

		$neonSettingsProvider = $services['services']['neonSettingsProvider']['class'];
		$neonSettingsProvider->arguments[0] = $config['host'];
		$neonSettingsProvider->arguments[1] = $config['port'];
	}


	public function toggleDebugBar(
		array $config,
		array $services
	): array
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

}
