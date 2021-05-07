<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Model\DumpIndex;


require_once __DIR__ . '/../../../../bootstrap.php';


/**
 * @testCase
 */
class Execute extends \SpameriTests\Elastic\AbstractTestCase
{

	protected function setUp(): void
	{
		parent::setUp();

		/** @var \Spameri\Elastic\Model\RestoreIndex $restoreIndex */
		$restoreIndex = $this->container->getByType(\Spameri\Elastic\Model\RestoreIndex::class);
		$restoreIndex->setOutput(new \Symfony\Component\Console\Output\ConsoleOutput());

		$restoreIndex->execute(__DIR__ . '/dumpData.json', 500);
	}


	public function testProcess() : void
	{
		/** @var \Spameri\Elastic\Model\DumpIndex $dumpIndex */
		$dumpIndex = $this->container->getByType(\Spameri\Elastic\Model\DumpIndex::class);
		$dumpIndex->setOutput(new \Symfony\Component\Console\Output\ConsoleOutput());

		$dumpIndex->execute(
			\SpameriTests\Elastic\Config::INDEX_DUMP,
			'test.log',
			\SpameriTests\Elastic\Config::INDEX_DUMP
		);

		\Tester\Assert::true(\file_exists('test.log'));

		$dumpFile = \file_get_contents('test.log');
		$exploded = explode(\PHP_EOL, $dumpFile);
		$decoded = \Nette\Utils\Json::decode($exploded[10]);

		\Tester\Assert::same('192437', $decoded->index->_id);
	}


	protected function tearDown()
	{
		/** @var \Spameri\Elastic\Model\Indices\Delete $delete */
		$delete = $this->container->getByType(\Spameri\Elastic\Model\Indices\Delete::class);
		$delete->execute(\SpameriTests\Elastic\Config::INDEX_DUMP);

		\Nette\Utils\FileSystem::delete('test.log');
	}

}

(new Execute())->run();
