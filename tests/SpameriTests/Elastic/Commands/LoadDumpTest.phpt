<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Commands;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
class LoadDumpTest extends \SpameriTests\Elastic\AbstractTestCase
{

	private const INDEX = 'spameri_command_load_test';
	private string $dumpFile;


	protected function setUp(): void
	{
		parent::setUp();
		$this->dumpFile = \TEMP_DIR . '/load_test.dump';

		// Create index
		/** @var \Spameri\Elastic\Model\Indices\Create $create */
		$create = $this->container->getByType(\Spameri\Elastic\Model\Indices\Create::class);
		$create->execute(self::INDEX, []);

		// Create dump file with test data
		$this->createDumpFile();
	}


	private function createDumpFile(): void
	{
		$content = '';
		for ($i = 1; $i <= 5; $i++) {
			// Index line
			$content .= \json_encode([
				'index' => [
					'_index' => self::INDEX,
					'_id' => 'doc_' . $i,
				],
			]) . "\r\n";
			// Data line
			$content .= \json_encode([
				'title' => 'Loaded Document ' . $i,
				'value' => $i * 100,
			]) . "\r\n";
		}

		\file_put_contents($this->dumpFile, $content);
	}


	public function testLoadDumpImportsDocuments(): void
	{
		/** @var \Spameri\Elastic\Commands\LoadDump $command */
		$command = $this->container->getByType(\Spameri\Elastic\Commands\LoadDump::class);

		$input = new \Symfony\Component\Console\Input\ArrayInput([
			'filename' => $this->dumpFile,
		]);
		$output = new \Symfony\Component\Console\Output\BufferedOutput();

		$result = $command->run($input, $output);

		\Tester\Assert::same(0, $result);
		\Tester\Assert::contains('Done', $output->fetch());

		// Wait for ES to index
		/** @var \Spameri\Elastic\ClientProvider $clientProvider */
		$clientProvider = $this->container->getByType(\Spameri\Elastic\ClientProvider::class);
		$clientProvider->client()->indices()->refresh(['index' => self::INDEX]);

		// Verify documents were imported
		$response = $clientProvider->client()->search([
			'index' => self::INDEX,
			'body' => [
				'query' => ['match_all' => new \stdClass()],
			],
		])->asArray();

		\Tester\Assert::same(5, $response['hits']['total']['value']);
	}


	public function testLoadDumpWithCustomStep(): void
	{
		/** @var \Spameri\Elastic\Commands\LoadDump $command */
		$command = $this->container->getByType(\Spameri\Elastic\Commands\LoadDump::class);

		$input = new \Symfony\Component\Console\Input\ArrayInput([
			'filename' => $this->dumpFile,
			'step' => '4',
		]);
		$output = new \Symfony\Component\Console\Output\BufferedOutput();

		$result = $command->run($input, $output);

		\Tester\Assert::same(0, $result);

		// Wait for ES to index
		/** @var \Spameri\Elastic\ClientProvider $clientProvider */
		$clientProvider = $this->container->getByType(\Spameri\Elastic\ClientProvider::class);
		$clientProvider->client()->indices()->refresh(['index' => self::INDEX]);

		// Verify documents were imported
		$response = $clientProvider->client()->search([
			'index' => self::INDEX,
			'body' => [
				'query' => ['match_all' => new \stdClass()],
			],
		])->asArray();

		\Tester\Assert::same(5, $response['hits']['total']['value']);
	}


	public function testLoadDumpPreservesDocumentData(): void
	{
		/** @var \Spameri\Elastic\Commands\LoadDump $command */
		$command = $this->container->getByType(\Spameri\Elastic\Commands\LoadDump::class);

		$input = new \Symfony\Component\Console\Input\ArrayInput([
			'filename' => $this->dumpFile,
		]);
		$output = new \Symfony\Component\Console\Output\BufferedOutput();
		$command->run($input, $output);

		// Wait for ES to index
		/** @var \Spameri\Elastic\ClientProvider $clientProvider */
		$clientProvider = $this->container->getByType(\Spameri\Elastic\ClientProvider::class);
		$clientProvider->client()->indices()->refresh(['index' => self::INDEX]);

		// Verify specific document
		$response = $clientProvider->client()->get([
			'index' => self::INDEX,
			'id' => 'doc_3',
		])->asArray();

		\Tester\Assert::same('Loaded Document 3', $response['_source']['title']);
		\Tester\Assert::same(300, $response['_source']['value']);
	}


	public function testLoadDumpReportsProgress(): void
	{
		/** @var \Spameri\Elastic\Commands\LoadDump $command */
		$command = $this->container->getByType(\Spameri\Elastic\Commands\LoadDump::class);

		$input = new \Symfony\Component\Console\Input\ArrayInput([
			'filename' => $this->dumpFile,
		]);
		$output = new \Symfony\Component\Console\Output\BufferedOutput();

		$result = $command->run($input, $output);

		\Tester\Assert::same(0, $result);
		$outputContent = $output->fetch();
		\Tester\Assert::contains('Starting', $outputContent);
		\Tester\Assert::contains('Done', $outputContent);
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

(new LoadDumpTest())->run();
