<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Commands;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
class DumpIndexTest extends \SpameriTests\Elastic\AbstractTestCase
{

	private const INDEX = 'spameri_command_dump_test';
	private string $dumpFile;


	protected function setUp(): void
	{
		parent::setUp();
		$this->dumpFile = \TEMP_DIR . '/dump_test.dump';

		// Create index and add some data
		/** @var \Spameri\Elastic\Model\Indices\Create $create */
		$create = $this->container->getByType(\Spameri\Elastic\Model\Indices\Create::class);
		$create->execute(self::INDEX, []);

		// Insert some test data
		/** @var \Spameri\Elastic\ClientProvider $clientProvider */
		$clientProvider = $this->container->getByType(\Spameri\Elastic\ClientProvider::class);

		for ($i = 1; $i <= 3; $i++) {
			$clientProvider->client()->index([
				'index' => self::INDEX,
				'id' => 'doc_' . $i,
				'body' => [
					'title' => 'Test Document ' . $i,
					'value' => $i * 10,
				],
			]);
		}

		// Wait for ES to index
		$clientProvider->client()->indices()->refresh(['index' => self::INDEX]);
	}


	public function testDumpIndexToFile(): void
	{
		/** @var \Spameri\Elastic\Commands\DumpIndex $command */
		$command = $this->container->getByType(\Spameri\Elastic\Commands\DumpIndex::class);

		$input = new \Symfony\Component\Console\Input\ArrayInput([
			'index' => self::INDEX,
			'filename' => $this->dumpFile,
		]);
		$output = new \Symfony\Component\Console\Output\BufferedOutput();

		$result = $command->run($input, $output);

		\Tester\Assert::same(0, $result);
		\Tester\Assert::contains('Done', $output->fetch());

		// Verify dump file exists and has content
		\Tester\Assert::true(\file_exists($this->dumpFile));

		$content = \file_get_contents($this->dumpFile);
		\Tester\Assert::true(\strlen($content) > 0);

		// Verify file contains our documents (bulk format - 2 lines per doc)
		$lines = \array_filter(\explode("\n", \trim($content)));
		// 3 documents * 2 lines each = 6 lines
		\Tester\Assert::true(\count($lines) >= 6, 'Dump should contain at least 6 lines for 3 documents');

		// Verify structure (alternating index/data lines)
		$firstLine = \json_decode($lines[0], true);
		\Tester\Assert::true(isset($firstLine['index']));
		\Tester\Assert::same(self::INDEX, $firstLine['index']['_index']);
	}


	public function testDumpIndexContainsCorrectData(): void
	{
		/** @var \Spameri\Elastic\Commands\DumpIndex $command */
		$command = $this->container->getByType(\Spameri\Elastic\Commands\DumpIndex::class);

		$input = new \Symfony\Component\Console\Input\ArrayInput([
			'index' => self::INDEX,
			'filename' => $this->dumpFile,
		]);
		$output = new \Symfony\Component\Console\Output\BufferedOutput();

		$command->run($input, $output);

		$content = \file_get_contents($this->dumpFile);
		$lines = \array_filter(\explode("\n", \trim($content)));

		// Check that data lines contain expected content
		$foundDocuments = 0;
		for ($i = 1; $i < \count($lines); $i += 2) {
			$data = \json_decode($lines[$i], true);
			if (isset($data['title']) && \str_starts_with($data['title'], 'Test Document')) {
				$foundDocuments++;
			}
		}

		\Tester\Assert::same(3, $foundDocuments);
	}


	public function testDumpCreatesDirectoryIfNeeded(): void
	{
		$nestedDumpFile = \TEMP_DIR . '/nested/path/dump.dump';

		/** @var \Spameri\Elastic\Commands\DumpIndex $command */
		$command = $this->container->getByType(\Spameri\Elastic\Commands\DumpIndex::class);

		$input = new \Symfony\Component\Console\Input\ArrayInput([
			'index' => self::INDEX,
			'filename' => $nestedDumpFile,
		]);
		$output = new \Symfony\Component\Console\Output\BufferedOutput();

		$result = $command->run($input, $output);

		\Tester\Assert::same(0, $result);
		\Tester\Assert::true(\file_exists($nestedDumpFile));

		// Cleanup
		@\unlink($nestedDumpFile);
	}


	protected function tearDown(): void
	{
		/** @var \Spameri\Elastic\Model\Indices\Delete $delete */
		$delete = $this->container->getByType(\Spameri\Elastic\Model\Indices\Delete::class);

		try {
			$delete->execute(self::INDEX);
		} catch (\Throwable $e) {
			// Ignore
		}

		@\unlink($this->dumpFile);
	}

}

(new DumpIndexTest())->run();
