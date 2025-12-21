<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Model;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
class ScrollTest extends \SpameriTests\Elastic\AbstractTestCase
{

	private const INDEX = 'spameri_model_scroll_test';


	protected function setUp(): void
	{
		parent::setUp();

		// Delete any existing index or alias with wildcard
		/** @var \Spameri\Elastic\ClientProvider $clientProvider */
		$clientProvider = $this->container->getByType(\Spameri\Elastic\ClientProvider::class);
		try {
			$clientProvider->client()->indices()->delete(['index' => self::INDEX . '*']);
		} catch (\Throwable $e) {
			// Ignore if index doesn't exist
		}

		\usleep(100000);

		/** @var \Spameri\Elastic\Model\Indices\Create $create */
		$create = $this->container->getByType(\Spameri\Elastic\Model\Indices\Create::class);
		$create->execute(self::INDEX, [
			'mappings' => [
				'properties' => [
					'title' => [
						'type' => 'keyword',
					],
					'order' => [
						'type' => 'integer',
					],
				],
			],
		]);

		// Wait for index to be ready
		\usleep(100000);
	}


	public function testScrollThroughLargeDataset(): void
	{
		// Create 25 documents
		for ($i = 1; $i <= 25; $i++) {
			$this->indexTestDocument('doc' . $i, [
				'title' => 'Document ' . $i,
				'order' => $i,
			]);
		}

		\usleep(500000);

		/** @var \Spameri\Elastic\Model\Scroll $scroll */
		$scroll = $this->container->getByType(\Spameri\Elastic\Model\Scroll::class);

		$elasticQuery = new \Spameri\ElasticQuery\ElasticQuery();
		$elasticQuery->addMustQuery(new \Spameri\ElasticQuery\Query\MatchAll());
		$elasticQuery->options()->changeSize(10);
		$elasticQuery->options()->startScroll('1m');

		// First scroll batch
		$result1 = $scroll->execute($elasticQuery, self::INDEX);

		\Tester\Assert::type(\Spameri\ElasticQuery\Response\ResultSearch::class, $result1);
		\Tester\Assert::count(10, $result1->hits());
		\Tester\Assert::same(25, $result1->stats()->total());
		\Tester\Assert::notNull($elasticQuery->options()->scrollId());

		// Second scroll batch
		$result2 = $scroll->execute($elasticQuery, self::INDEX);
		\Tester\Assert::count(10, $result2->hits());

		// Third scroll batch (last 5 documents)
		$result3 = $scroll->execute($elasticQuery, self::INDEX);
		\Tester\Assert::count(5, $result3->hits());

		// Fourth scroll should be empty
		$result4 = $scroll->execute($elasticQuery, self::INDEX);
		\Tester\Assert::count(0, $result4->hits());

		// Clean up scroll context
		$scroll->closeScroll($elasticQuery);
	}


	public function testScrollWithCustomBatchSize(): void
	{
		// Create 15 documents
		for ($i = 1; $i <= 15; $i++) {
			$this->indexTestDocument('doc' . $i, [
				'title' => 'Document ' . $i,
				'order' => $i,
			]);
		}

		\usleep(500000);

		/** @var \Spameri\Elastic\Model\Scroll $scroll */
		$scroll = $this->container->getByType(\Spameri\Elastic\Model\Scroll::class);

		$elasticQuery = new \Spameri\ElasticQuery\ElasticQuery();
		$elasticQuery->addMustQuery(new \Spameri\ElasticQuery\Query\MatchAll());
		$elasticQuery->options()->changeSize(7); // Custom batch size
		$elasticQuery->options()->startScroll('1m');

		// First batch: 7 docs
		$result1 = $scroll->execute($elasticQuery, self::INDEX);
		\Tester\Assert::count(7, $result1->hits());

		// Second batch: 7 docs
		$result2 = $scroll->execute($elasticQuery, self::INDEX);
		\Tester\Assert::count(7, $result2->hits());

		// Third batch: 1 doc (remaining)
		$result3 = $scroll->execute($elasticQuery, self::INDEX);
		\Tester\Assert::count(1, $result3->hits());

		$scroll->closeScroll($elasticQuery);
	}


	public function testScrollCollectsAllDocuments(): void
	{
		$documentIds = [];
		for ($i = 1; $i <= 20; $i++) {
			$id = 'doc' . $i;
			$documentIds[] = $id;
			$this->indexTestDocument($id, [
				'title' => 'Document ' . $i,
				'order' => $i,
			]);
		}

		\usleep(500000);

		/** @var \Spameri\Elastic\Model\Scroll $scroll */
		$scroll = $this->container->getByType(\Spameri\Elastic\Model\Scroll::class);

		$elasticQuery = new \Spameri\ElasticQuery\ElasticQuery();
		$elasticQuery->addMustQuery(new \Spameri\ElasticQuery\Query\MatchAll());
		$elasticQuery->options()->changeSize(5);
		$elasticQuery->options()->startScroll('1m');

		$collectedIds = [];

		do {
			$result = $scroll->execute($elasticQuery, self::INDEX);
			foreach ($result->hits() as $hit) {
				$collectedIds[] = $hit->id();
			}
		} while (\count($result->hits()) > 0);

		$scroll->closeScroll($elasticQuery);

		// Verify all documents were collected
		\Tester\Assert::count(20, $collectedIds);
		\sort($documentIds);
		\sort($collectedIds);
		\Tester\Assert::same($documentIds, $collectedIds);
	}


	public function testScrollWithEmptyIndex(): void
	{
		// No documents indexed
		\usleep(100000);

		/** @var \Spameri\Elastic\Model\Scroll $scroll */
		$scroll = $this->container->getByType(\Spameri\Elastic\Model\Scroll::class);

		$elasticQuery = new \Spameri\ElasticQuery\ElasticQuery();
		$elasticQuery->addMustQuery(new \Spameri\ElasticQuery\Query\MatchAll());
		$elasticQuery->options()->changeSize(10);
		$elasticQuery->options()->startScroll('1m');

		$result = $scroll->execute($elasticQuery, self::INDEX);

		\Tester\Assert::type(\Spameri\ElasticQuery\Response\ResultSearch::class, $result);
		\Tester\Assert::count(0, $result->hits());
		\Tester\Assert::same(0, $result->stats()->total());

		// Even with empty results, scroll_id should be initialized
		\Tester\Assert::notNull($elasticQuery->options()->scrollId());

		$scroll->closeScroll($elasticQuery);
	}


	public function testScrollWithFilter(): void
	{
		for ($i = 1; $i <= 20; $i++) {
			$this->indexTestDocument('doc' . $i, [
				'title' => 'Document ' . $i,
				'order' => $i,
			]);
		}

		\usleep(500000);

		/** @var \Spameri\Elastic\Model\Scroll $scroll */
		$scroll = $this->container->getByType(\Spameri\Elastic\Model\Scroll::class);

		// Only get documents with order <= 10
		$elasticQuery = new \Spameri\ElasticQuery\ElasticQuery();
		$elasticQuery->addFilter(new \Spameri\ElasticQuery\Query\Range('order', null, 10));
		$elasticQuery->options()->changeSize(5);
		$elasticQuery->options()->startScroll('1m');

		$totalCollected = 0;

		do {
			$result = $scroll->execute($elasticQuery, self::INDEX);
			$totalCollected += \count($result->hits());
		} while (\count($result->hits()) > 0);

		$scroll->closeScroll($elasticQuery);

		\Tester\Assert::same(10, $totalCollected);
	}


	public function testClearScrollContext(): void
	{
		$this->indexTestDocument('doc1', ['title' => 'Test', 'order' => 1]);

		\usleep(500000);

		/** @var \Spameri\Elastic\Model\Scroll $scroll */
		$scroll = $this->container->getByType(\Spameri\Elastic\Model\Scroll::class);

		$elasticQuery = new \Spameri\ElasticQuery\ElasticQuery();
		$elasticQuery->addMustQuery(new \Spameri\ElasticQuery\Query\MatchAll());
		$elasticQuery->options()->changeSize(10);
		$elasticQuery->options()->startScroll('1m');

		$scroll->execute($elasticQuery, self::INDEX);

		// Verify scroll ID was initialized
		\Tester\Assert::notNull($elasticQuery->options()->scrollId());

		// Clear scroll should not throw
		\Tester\Assert::noError(static function () use ($scroll, $elasticQuery): void {
			$scroll->closeScroll($elasticQuery);
		});
	}


	public function testScrollInitializesScrollId(): void
	{
		$this->indexTestDocument('doc1', ['title' => 'Test', 'order' => 1]);

		\usleep(500000);

		/** @var \Spameri\Elastic\Model\Scroll $scroll */
		$scroll = $this->container->getByType(\Spameri\Elastic\Model\Scroll::class);

		$elasticQuery = new \Spameri\ElasticQuery\ElasticQuery();
		$elasticQuery->addMustQuery(new \Spameri\ElasticQuery\Query\MatchAll());
		$elasticQuery->options()->changeSize(10);
		$elasticQuery->options()->startScroll('1m');

		// Before execute, scroll_id should be null
		\Tester\Assert::null($elasticQuery->options()->scrollId());

		$scroll->execute($elasticQuery, self::INDEX);

		// After execute, scroll_id should be set
		\Tester\Assert::notNull($elasticQuery->options()->scrollId());
		\Tester\Assert::type('string', $elasticQuery->options()->scrollId());

		$scroll->closeScroll($elasticQuery);
	}


	/**
	 * @param array<string, mixed> $body
	 */
	private function indexTestDocument(string $id, array $body): void
	{
		/** @var \Spameri\Elastic\ClientProvider $clientProvider */
		$clientProvider = $this->container->getByType(\Spameri\Elastic\ClientProvider::class);

		$clientProvider->client()->index([
			'index' => self::INDEX,
			'id' => $id,
			'body' => $body,
			'refresh' => true,
		]);
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
	}

}

(new ScrollTest())->run();
