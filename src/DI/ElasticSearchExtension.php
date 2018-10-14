<?php declare(strict_types = 1);

namespace Spameri\Elastic\DI;


class ElasticSearchExtension extends \Nette\DI\CompilerExtension
{

	public $defaults = [
		'host' 		=> 'localhost',
		'port' 		=> 9200,
		'debug'		=> FALSE,
		'entities' 	=> [],
		'settings' 	=> [
			'analysis' => [
				'analyzer' => [
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
					'synonym' => [
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

		$config = \Nette\DI\Config\Helpers::merge($this->getConfig(), $this->defaults);

		$builder = $this->getContainerBuilder();

		$services = $this->loadFromFile(__DIR__ . '/../Config/Elastic.neon');

		/** @var \Nette\DI\Statement $entitySettingsProvider */
		$entitySettingsProvider = $services['services']['entitySettingsProvider']['class'];
		$entitySettingsProvider->arguments[0] = $config['entities'];

		$updateMapping = $services['services']['updateMapping']['class'];
		$updateMapping->arguments[0] = $config['entities'];

		$setUpElasticCommand = $services['services']['setUpElasticCommand']['class'];
		$setUpElasticCommand->arguments[0] = $config['entities'];

		$neonSettingsProvider = $services['services']['neonSettingsProvider']['class'];
		$neonSettingsProvider->arguments[0] = $config['host'];
		$neonSettingsProvider->arguments[1] = $config['port'];


		$this->compiler->parseServices(
			$builder,
			$services,
			$this->name
		);
	}


	public static function register(
		\Nette\Configurator $config
	): void
	{
		$config->onCompile[] = static function (
			$config,
			\Nette\DI\Compiler $compiler
		) {
			$compiler->addExtension('elasticSearch', new ElasticSearchExtension());
		};
	}

}
