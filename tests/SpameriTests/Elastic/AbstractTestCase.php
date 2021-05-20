<?php declare(strict_types = 1);

namespace SpameriTests\Elastic;

abstract class AbstractTestCase extends \Tester\TestCase
{

	protected \Nette\Configurator $config;

	protected \Nette\DI\Container $container;


	protected function setUp(): void
	{
		$config = new \Nette\Configurator();
		$config->setTempDirectory(\TEMP_DIR);
		$config->addConfig(__DIR__ . '/Data/Config/Common.neon');

		$this->config = $config;
		$this->container = $this->config->createContainer();
	}

}
