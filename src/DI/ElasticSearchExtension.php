<?php declare(strict_types = 1);

namespace Spameri\Elastic\DI;


class ElasticSearchExtension extends \Nette\DI\CompilerExtension
{

	public $defaults = [
		'host' 		=> 'localhost',
		'port' 		=> 9200,
		'debug'		=> FALSE,
		'entities' 	=> [],
	];


	public function loadConfiguration(): void
	{
		parent::loadConfiguration();

		$config = \Nette\DI\Config\Helpers::merge($this->getConfig(), $this->defaults);

		$builder = $this->getContainerBuilder();

		$this->compiler->parseServices(
			$builder,
			$this->loadFromFile(__DIR__ . '/../Config/Elastic.neon'),
			$this->name
		);

//		$builder->getByType();
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
