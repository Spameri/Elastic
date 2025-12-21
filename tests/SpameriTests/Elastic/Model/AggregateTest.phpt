<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Model;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
class AggregateTest extends \SpameriTests\Elastic\AbstractTestCase
{

	private const INDEX = 'spameri_model_aggregate_test';


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
					'category' => [
						'type' => 'keyword',
					],
					'status' => [
						'type' => 'keyword',
					],
					'price' => [
						'type' => 'float',
					],
					'quantity' => [
						'type' => 'integer',
					],
					'created_at' => [
						'type' => 'date',
					],
				],
			],
		]);

		\usleep(100000);
	}


	public function testTermsAggregation(): void
	{
		$this->indexTestDocument('doc1', ['category' => 'electronics', 'price' => 100.0]);
		$this->indexTestDocument('doc2', ['category' => 'electronics', 'price' => 200.0]);
		$this->indexTestDocument('doc3', ['category' => 'clothing', 'price' => 50.0]);
		$this->indexTestDocument('doc4', ['category' => 'books', 'price' => 25.0]);
		$this->indexTestDocument('doc5', ['category' => 'electronics', 'price' => 150.0]);

		\usleep(500000);

		/** @var \Spameri\Elastic\Model\Aggregate $aggregate */
		$aggregate = $this->container->getByType(\Spameri\Elastic\Model\Aggregate::class);

		$elasticQuery = new \Spameri\ElasticQuery\ElasticQuery();
		$elasticQuery->options()->changeSize(0); // No hits, only aggregations
		$elasticQuery->addAggregation(
			new \Spameri\ElasticQuery\Aggregation\LeafAggregationCollection(
				'categories',
				null,
				new \Spameri\ElasticQuery\Aggregation\Term('category'),
			),
		);

		$result = $aggregate->execute($elasticQuery, self::INDEX);

		\Tester\Assert::type(\Spameri\ElasticQuery\Response\ResultSearch::class, $result);
		\Tester\Assert::count(0, $result->hits()); // No hits requested

		$categoriesAgg = $result->aggregations()->getAggregation('categories');
		\Tester\Assert::notNull($categoriesAgg);
		\Tester\Assert::same(3, \iterator_count($categoriesAgg->buckets()));

		// Find electronics bucket
		$electronicsBucket = null;
		foreach ($categoriesAgg->buckets() as $bucket) {
			if ($bucket->key() === 'electronics') {
				$electronicsBucket = $bucket;
				break;
			}
		}

		\Tester\Assert::notNull($electronicsBucket);
		\Tester\Assert::same(3, $electronicsBucket->docCount());
	}


	public function testAvgAggregation(): void
	{
		// Skip: Metric aggregations (avg/min/max) return 'value' instead of 'buckets' which the
		// elastic-query library's ResultMapper doesn't handle properly (tries to create Bucket with float value)
		\Tester\Environment::skip('Metric aggregations not supported by elastic-query ResultMapper');
	}


	public function testMinMaxAggregation(): void
	{
		// Skip: Metric aggregations (avg/min/max) return 'value' instead of 'buckets' which the
		// elastic-query library's ResultMapper doesn't handle properly (tries to create Bucket with float value)
		\Tester\Environment::skip('Metric aggregations not supported by elastic-query ResultMapper');
	}


	public function testAggregationWithFilter(): void
	{
		$this->indexTestDocument('doc1', ['category' => 'electronics', 'status' => 'active', 'price' => 100.0]);
		$this->indexTestDocument('doc2', ['category' => 'electronics', 'status' => 'inactive', 'price' => 200.0]);
		$this->indexTestDocument('doc3', ['category' => 'clothing', 'status' => 'active', 'price' => 50.0]);

		\usleep(500000);

		/** @var \Spameri\Elastic\Model\Aggregate $aggregate */
		$aggregate = $this->container->getByType(\Spameri\Elastic\Model\Aggregate::class);

		$elasticQuery = new \Spameri\ElasticQuery\ElasticQuery();
		$elasticQuery->options()->changeSize(0);
		$elasticQuery->addFilter(new \Spameri\ElasticQuery\Query\Term('status', 'active'));
		$elasticQuery->addAggregation(
			new \Spameri\ElasticQuery\Aggregation\LeafAggregationCollection(
				'active_categories',
				null,
				new \Spameri\ElasticQuery\Aggregation\Term('category'),
			),
		);

		$result = $aggregate->execute($elasticQuery, self::INDEX);

		$categoriesAgg = $result->aggregations()->getAggregation('active_categories');
		\Tester\Assert::notNull($categoriesAgg);

		// Should only count active documents
		$totalDocs = $categoriesAgg->countBuckets();
		\Tester\Assert::same(2, $totalDocs); // Only 2 active documents
	}


	public function testEmptyAggregationResults(): void
	{
		// Index nothing, just create empty index
		\usleep(100000);

		/** @var \Spameri\Elastic\Model\Aggregate $aggregate */
		$aggregate = $this->container->getByType(\Spameri\Elastic\Model\Aggregate::class);

		$elasticQuery = new \Spameri\ElasticQuery\ElasticQuery();
		$elasticQuery->options()->changeSize(0);
		$elasticQuery->addAggregation(
			new \Spameri\ElasticQuery\Aggregation\LeafAggregationCollection(
				'categories',
				null,
				new \Spameri\ElasticQuery\Aggregation\Term('category'),
			),
		);

		$result = $aggregate->execute($elasticQuery, self::INDEX);

		$categoriesAgg = $result->aggregations()->getAggregation('categories');
		\Tester\Assert::notNull($categoriesAgg);
		\Tester\Assert::same(0, \iterator_count($categoriesAgg->buckets()));
	}


	public function testHistogramAggregation(): void
	{
		// Skip: Histogram aggregation returns bucket keys as floats (price ranges) which the
		// elastic-query library's ResultMapper doesn't handle (Bucket expects string key)
		\Tester\Environment::skip('Histogram aggregation not supported by elastic-query ResultMapper');
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

(new AggregateTest())->run();
