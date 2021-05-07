<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Model\RestoreIndex;


require_once __DIR__ . '/../../../../bootstrap.php';


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
		$create->execute(\SpameriTests\Elastic\Config::INDEX_RESTORE, []);
	}


	public function testProcess() : void
	{
		/** @var \Spameri\Elastic\Model\RestoreIndex $restoreIndex */
		$restoreIndex = $this->container->getByType(\Spameri\Elastic\Model\RestoreIndex::class);
		$restoreIndex->setOutput(new \Symfony\Component\Console\Output\ConsoleOutput());
		$restoreIndex->execute(__DIR__ . '/restoreData.json', 500);

		$id = new \Spameri\Elastic\Entity\Property\ElasticId('192461');
		/** @var \Spameri\Elastic\Model\Get $get */
		$get = $this->container->getByType(\Spameri\Elastic\Model\Get::class);
		$result = $get->execute($id, \SpameriTests\Elastic\Config::INDEX_RESTORE, \SpameriTests\Elastic\Config::INDEX_RESTORE);

		\Tester\Assert::same($id->value(), $result->hit()->id());
	}

	protected function tearDown()
	{
		/** @var \Spameri\Elastic\Model\Indices\Delete $delete */
		$delete = $this->container->getByType(\Spameri\Elastic\Model\Indices\Delete::class);
		$delete->execute(\SpameriTests\Elastic\Config::INDEX_RESTORE);
	}
}

(new Execute())->run();
