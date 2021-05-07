<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\DI;


require_once __DIR__ . '/../../../bootstrap.php';


class Extension extends \Tester\TestCase
{

	/**
	 * @var \Nette\Configurator
	 */
	private $config;


	public function setUp(): void
	{
		$config = new \Nette\Configurator();
		$config->setTempDirectory(\TEMP_DIR);
		$config->addConfig(__DIR__ . '/../Data/Config/Common.neon');

		$this->config = $config;
	}


	public function testInitialize(): void
	{
		$container = $this->config->createContainer();

		$search = $container->getByType(\Spameri\Elastic\Model\Search::class);
		\Tester\Assert::true($search instanceof \Spameri\Elastic\Model\Search);

		$resultMapper = $container->getByType(\Spameri\ElasticQuery\Response\ResultMapper::class);
		\Tester\Assert::true($resultMapper instanceof \Spameri\ElasticQuery\Response\ResultMapper);

		/** @var \Spameri\Elastic\ClientProvider $clientProvider */
		$clientProvider = $container->getByType(\Spameri\Elastic\ClientProvider::class);
		$connection = $clientProvider->client()->transport->connectionPool->nextConnection();

		/** @var \Spameri\Elastic\Model\VersionProvider $versionProvider */
		$versionProvider = $container->getByType(\Spameri\Elastic\Model\VersionProvider::class);
		\Tester\Assert::type('int', $versionProvider->provide());

		if ($versionProvider->provide() >= \Spameri\ElasticQuery\Response\Result\Version::ELASTIC_VERSION_ID_7) {
			\Tester\Assert::same(\SpameriTests\Elastic\Config::HOST, $connection->getHost());
			\Tester\Assert::same(\SpameriTests\Elastic\Config::PORT, $connection->getPort());

		} else {
			\Tester\Assert::same(\SpameriTests\Elastic\Config::CONNECTION, $connection->getHost());
		}
	}

}
(new Extension())->run();
