<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Import;

require_once __DIR__ . '/../../../bootstrap-unit.php';

/**
 * @testCase
 */
class SimpleRunTest extends \Tester\TestCase
{

	private string $logDir;


	protected function setUp(): void
	{
		$this->logDir = \TEMP_DIR . '/logs';
		\Nette\Utils\FileSystem::createDir($this->logDir);
	}


	protected function tearDown(): void
	{
		\Nette\Utils\FileSystem::delete($this->logDir);
	}


	private function createSimpleRun(
		\Spameri\Elastic\Import\DataProviderInterface $dataProvider,
		\Spameri\Elastic\Import\PrepareImportDataInterface $prepareImportData,
		\Spameri\Elastic\Import\DataImportInterface $dataImport,
	): \Spameri\Elastic\Import\SimpleRun {
		return new \Spameri\Elastic\Import\SimpleRun(
			$this->logDir,
			new \Spameri\Elastic\Import\Run\NullLoggerHandler(),
			new \Spameri\Elastic\Import\Lock\NullLock(),
			new \Spameri\Elastic\Import\RunHandler\NullHandler(),
			$dataProvider,
			$prepareImportData,
			$dataImport,
			new \Spameri\Elastic\Import\AfterImport\NullAfterImport(),
		);
	}


	public function testSimpleRunExtendsRun(): void
	{
		$dataProvider = new \SpameriTests\Elastic\Data\Import\MockDataProvider();
		$prepareImportData = new \SpameriTests\Elastic\Data\Import\MockPrepareImportData();
		$dataImport = new \SpameriTests\Elastic\Data\Import\MockDataImport();

		$simpleRun = $this->createSimpleRun($dataProvider, $prepareImportData, $dataImport);

		\Tester\Assert::type(\Spameri\Elastic\Import\Run::class, $simpleRun);
		\Tester\Assert::type(\Spameri\Elastic\Import\SimpleRun::class, $simpleRun);
	}


	public function testSimpleImportWithoutLocks(): void
	{
		$dataProvider = new \SpameriTests\Elastic\Data\Import\MockDataProvider();
		$dataProvider->setData([
			['key' => 'simple1', 'name' => 'Simple Item 1', 'value' => 10],
			['key' => 'simple2', 'name' => 'Simple Item 2', 'value' => 20],
		]);

		$prepareImportData = new \SpameriTests\Elastic\Data\Import\MockPrepareImportData();
		$dataImport = new \SpameriTests\Elastic\Data\Import\MockDataImport();

		$simpleRun = $this->createSimpleRun($dataProvider, $prepareImportData, $dataImport);

		$output = new \Symfony\Component\Console\Output\NullOutput();
		$simpleRun->setOutput($output);

		$options = new \Spameri\Elastic\Import\Run\Options(60);
		$simpleRun->execute($options);

		\Tester\Assert::same(2, $prepareImportData->getCallCount());
		\Tester\Assert::same(2, $dataImport->getCallCount());
	}


	public function testBatchProcessing(): void
	{
		$dataProvider = new \SpameriTests\Elastic\Data\Import\MockDataProvider();

		// Create a larger batch
		$data = [];
		for ($i = 1; $i <= 10; $i++) {
			$data[] = ['key' => "batch{$i}", 'name' => "Item {$i}", 'value' => $i * 10];
		}
		$dataProvider->setData($data);

		$prepareImportData = new \SpameriTests\Elastic\Data\Import\MockPrepareImportData();
		$dataImport = new \SpameriTests\Elastic\Data\Import\MockDataImport();

		$simpleRun = $this->createSimpleRun($dataProvider, $prepareImportData, $dataImport);

		$output = new \Symfony\Component\Console\Output\NullOutput();
		$simpleRun->setOutput($output);

		$options = new \Spameri\Elastic\Import\Run\Options(60);
		$simpleRun->execute($options);

		\Tester\Assert::same(10, $prepareImportData->getCallCount());
		\Tester\Assert::same(10, $dataImport->getCallCount());

		// Verify all items were imported
		$importedEntities = $dataImport->getImportedEntities();
		\Tester\Assert::same(10, \count($importedEntities));
	}


	public function testSimpleRunWithEmptyData(): void
	{
		$dataProvider = new \SpameriTests\Elastic\Data\Import\MockDataProvider();
		$dataProvider->setData([]);

		$prepareImportData = new \SpameriTests\Elastic\Data\Import\MockPrepareImportData();
		$dataImport = new \SpameriTests\Elastic\Data\Import\MockDataImport();

		$simpleRun = $this->createSimpleRun($dataProvider, $prepareImportData, $dataImport);

		$output = new \Symfony\Component\Console\Output\NullOutput();
		$simpleRun->setOutput($output);

		$options = new \Spameri\Elastic\Import\Run\Options(60);
		$simpleRun->execute($options);

		// Should complete without errors
		\Tester\Assert::same(0, $prepareImportData->getCallCount());
		\Tester\Assert::same(0, $dataImport->getCallCount());
	}


	public function testSimpleRunUsesNullLock(): void
	{
		$dataProvider = new \SpameriTests\Elastic\Data\Import\MockDataProvider();
		$dataProvider->setData([
			['key' => '1', 'name' => 'Test', 'value' => 1],
		]);

		$prepareImportData = new \SpameriTests\Elastic\Data\Import\MockPrepareImportData();
		$dataImport = new \SpameriTests\Elastic\Data\Import\MockDataImport();

		// Multiple concurrent runs should succeed (NullLock doesn't block)
		$simpleRun1 = $this->createSimpleRun($dataProvider, $prepareImportData, $dataImport);
		$simpleRun2 = $this->createSimpleRun($dataProvider, $prepareImportData, $dataImport);

		$output = new \Symfony\Component\Console\Output\NullOutput();
		$simpleRun1->setOutput($output);
		$simpleRun2->setOutput($output);

		$options = new \Spameri\Elastic\Import\Run\Options(60);

		// Both should execute without blocking
		$simpleRun1->execute($options);
		$simpleRun2->execute($options);

		// Total: 2 runs * 1 item = 2 calls each
		\Tester\Assert::same(2, $prepareImportData->getCallCount());
	}


	public function testSimpleRunHandlesExceptions(): void
	{
		$dataProvider = new \SpameriTests\Elastic\Data\Import\MockDataProvider();
		$dataProvider->setData([
			['key' => '1', 'name' => 'One', 'value' => 1],
			['key' => '2', 'name' => 'Two', 'value' => 2],
			['key' => '3', 'name' => 'Three', 'value' => 3],
		]);

		$prepareImportData = new \SpameriTests\Elastic\Data\Import\MockPrepareImportData();
		// Skip second item
		$prepareImportData->setExceptionForCall(1, new \Spameri\Elastic\Import\Exception\Omit('Skipping'));

		$dataImport = new \SpameriTests\Elastic\Data\Import\MockDataImport();

		$simpleRun = $this->createSimpleRun($dataProvider, $prepareImportData, $dataImport);

		$output = new \Symfony\Component\Console\Output\NullOutput();
		$simpleRun->setOutput($output);

		$options = new \Spameri\Elastic\Import\Run\Options(60);
		$simpleRun->execute($options);

		// All items processed (exception caught), only 2 imported
		\Tester\Assert::same(3, $prepareImportData->getCallCount());
		\Tester\Assert::same(2, $dataImport->getCallCount());
	}


	public function testImportedEntitiesHaveCorrectData(): void
	{
		$dataProvider = new \SpameriTests\Elastic\Data\Import\MockDataProvider();
		$dataProvider->setData([
			['key' => 'product-001', 'name' => 'Widget', 'value' => 999],
		]);

		$prepareImportData = new \SpameriTests\Elastic\Data\Import\MockPrepareImportData();
		$dataImport = new \SpameriTests\Elastic\Data\Import\MockDataImport();

		$simpleRun = $this->createSimpleRun($dataProvider, $prepareImportData, $dataImport);

		$output = new \Symfony\Component\Console\Output\NullOutput();
		$simpleRun->setOutput($output);

		$options = new \Spameri\Elastic\Import\Run\Options(60);
		$simpleRun->execute($options);

		$importedEntities = $dataImport->getImportedEntities();
		\Tester\Assert::same(1, \count($importedEntities));

		$entity = $importedEntities[0];
		\Tester\Assert::same('product-001', $entity->key());
		\Tester\Assert::same('Widget', $entity->name);
		\Tester\Assert::same(999, $entity->value);
	}

}

(new SimpleRunTest())->run();
