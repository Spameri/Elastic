<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Diagnostics;

require_once __DIR__ . '/../../../bootstrap-unit.php';

/**
 * @testCase
 */
class PanelLoggerTest extends \Tester\TestCase
{

	private \Spameri\Elastic\Diagnostics\PanelLogger $panelLogger;


	protected function setUp(): void
	{
		$this->panelLogger = new \Spameri\Elastic\Diagnostics\PanelLogger(
			new \Psr\Log\NullLogger(),
		);
	}


	public function testLogsSingleJsonDocumentRequest(): void
	{
		$this->panelLogger->info('Request: POST /index/_search', [
			'request' => new \GuzzleHttp\Psr7\Request(
				'POST',
				'http://localhost:9200/index/_search',
				[],
				'{"query": {"match_all": {}}}',
			),
		]);

		$queries = $this->panelLogger->getQueries();

		\Tester\Assert::count(1, $queries);
		\Tester\Assert::same('/index/_search', $queries[0]['uri']);
		\Tester\Assert::contains('match_all', $queries[0]['requestBody']);
	}


	public function testLogsNdjsonBulkRequestWithoutThrowing(): void
	{
		$ndjsonBody = '{"index": {"_index": "index", "_id": "1"}}' . "\n"
			. '{"name": "First document"}' . "\n"
			. '{"index": {"_index": "index", "_id": "2"}}' . "\n"
			. '{"name": "Second document"}' . "\n";

		$this->panelLogger->info('Request: POST /_bulk', [
			'request' => new \GuzzleHttp\Psr7\Request(
				'POST',
				'http://localhost:9200/_bulk',
				[],
				$ndjsonBody,
			),
		]);

		$queries = $this->panelLogger->getQueries();

		\Tester\Assert::count(1, $queries);
		\Tester\Assert::same('/_bulk', $queries[0]['uri']);
		\Tester\Assert::contains('First document', $queries[0]['requestBody']);
		\Tester\Assert::contains('Second document', $queries[0]['requestBody']);
	}


	public function testLogsUndecodableBodyAsRawString(): void
	{
		$this->panelLogger->info('Request: POST /index/_doc', [
			'request' => new \GuzzleHttp\Psr7\Request(
				'POST',
				'http://localhost:9200/index/_doc',
				[],
				'not json at all',
			),
		]);

		$queries = $this->panelLogger->getQueries();

		\Tester\Assert::count(1, $queries);
		\Tester\Assert::contains('not json at all', $queries[0]['requestBody']);
	}


	public function testPairsResponseWithRequest(): void
	{
		$this->panelLogger->info('Request: POST /index/_search', [
			'request' => new \GuzzleHttp\Psr7\Request(
				'POST',
				'http://localhost:9200/index/_search',
				[],
				'{"query": {"match_all": {}}}',
			),
		]);
		$this->panelLogger->info('Response (retry 0): 200', [
			'response' => new \GuzzleHttp\Psr7\Response(
				200,
				[],
				'{"took": 1, "hits": {"total": {"value": 0}}}',
			),
		]);

		$queries = $this->panelLogger->getQueries();

		\Tester\Assert::count(1, $queries);
		\Tester\Assert::contains('hits', $queries[0]['responseBody']);
		\Tester\Assert::true(isset($queries[0]['duration']));
	}

}

(new PanelLoggerTest())->run();
