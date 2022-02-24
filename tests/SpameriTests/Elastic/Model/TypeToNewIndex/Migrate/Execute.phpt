<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Model\TypeToNewIndex\Migrate;

require_once __DIR__ . '/../../../../../bootstrap.php';

/**
 * @testCase
 */
class Execute extends \SpameriTests\Elastic\AbstractTestCase
{

	protected function setUp(): void
	{
		parent::setUp();

		/** @var \Spameri\Elastic\Model\Indices\Create $create */
		$create = $this->container->getByType(\Spameri\Elastic\Model\Indices\Create::class);
		$create->execute(\SpameriTests\Elastic\Config::INDEX_MIGRATE, []);

		/** @var \Spameri\Elastic\Model\RestoreIndex $restoreIndex */
		$restoreIndex = $this->container->getByType(\Spameri\Elastic\Model\RestoreIndex::class);
		$restoreIndex->setOutput(new \Symfony\Component\Console\Output\ConsoleOutput());

		$restoreIndex->execute(__DIR__ . '/migrateData.json', 500);
	}


	public function testProcess(): void
	{
		/** @var \Spameri\Elastic\Model\TypeToNewIndex\Migrate $migrate */
		$migrate = $this->container->getByType(\Spameri\Elastic\Model\TypeToNewIndex\Migrate::class);

		$id = new \Spameri\Elastic\Entity\Property\ElasticId('192489');

		/** @var \Spameri\Elastic\Model\Get $get */
		$get = $this->container->getByType(\Spameri\Elastic\Model\Get::class);
		$responseOldIndex = $get->execute(
			$id,
			\SpameriTests\Elastic\Config::INDEX_MIGRATE,
			\SpameriTests\Elastic\Config::INDEX_MIGRATE
		);

		\Tester\Assert::same($id->value(), $responseOldIndex->hit()->id());

		$migrate->setOutput(new \Symfony\Component\Console\Output\ConsoleOutput());

		$migrate->execute(
			\SpameriTests\Elastic\Config::INDEX_MIGRATE,
			\SpameriTests\Elastic\Config::INDEX_MIGRATE,
			\SpameriTests\Elastic\Config::INDEX_MIGRATE_NEW,
			\SpameriTests\Elastic\Config::INDEX_MIGRATE_NEW,
			\SpameriTests\Elastic\Config::INDEX_MIGRATE_NEW,
			FALSE
		);

		$newType = \SpameriTests\Elastic\Config::INDEX_MIGRATE_NEW;
		/** @var \Spameri\Elastic\Model\VersionProvider $versionProvider */
		$versionProvider = $this->container->getByType(\Spameri\Elastic\Model\VersionProvider::class);
		if ($versionProvider->provide() >= \Spameri\ElasticQuery\Response\Result\Version::ELASTIC_VERSION_ID_7) {
			$newType = '_doc';
		}

		$responseNewIndex = $get->execute(
			$id,
			\SpameriTests\Elastic\Config::INDEX_MIGRATE_NEW,
			$newType
		);

		\Tester\Assert::same($id->value(), $responseNewIndex->hit()->id());
	}


	protected function tearDown(): void
	{
		/** @var \Spameri\Elastic\Model\Indices\Delete $delete */
		$delete = $this->container->getByType(\Spameri\Elastic\Model\Indices\Delete::class);
		$delete->execute(\SpameriTests\Elastic\Config::INDEX_MIGRATE);
		$delete->execute(\SpameriTests\Elastic\Config::INDEX_MIGRATE . '*');
	}

}

(new Execute())->run();
