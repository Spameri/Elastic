<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Model;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
class SearchTest extends \SpameriTests\Elastic\AbstractTestCase
{

	private const INDEX = 'spameri_model_search_test';


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
						'type' => 'text',
						'fields' => [
							'keyword' => [
								'type' => 'keyword',
							],
						],
					],
					'content' => [
						'type' => 'text',
					],
					'tags' => [
						'type' => 'keyword',
					],
				],
			],
		]);

		// Wait for index to be ready
		\usleep(100000);
	}


	public function testBasicFullTextSearch(): void
	{
		$this->indexTestDocument('doc1', [
			'title' => 'The quick brown fox',
			'content' => 'The quick brown fox jumps over the lazy dog',
			'tags' => ['animals', 'nature'],
		]);
		$this->indexTestDocument('doc2', [
			'title' => 'A lazy dog story',
			'content' => 'Once upon a time there was a lazy dog',
			'tags' => ['animals', 'story'],
		]);
		$this->indexTestDocument('doc3', [
			'title' => 'Programming in PHP',
			'content' => 'PHP is a popular programming language',
			'tags' => ['programming', 'technology'],
		]);

		\usleep(1000000);

		/** @var \Spameri\Elastic\Model\Search $search */
		$search = $this->container->getByType(\Spameri\Elastic\Model\Search::class);

		$elasticQuery = new \Spameri\ElasticQuery\ElasticQuery();
		$elasticQuery->addMustQuery(
			new \Spameri\ElasticQuery\Query\ElasticMatch('content', 'lazy dog'),
		);

		$result = $search->execute($elasticQuery, self::INDEX);

		\Tester\Assert::type(\Spameri\ElasticQuery\Response\ResultSearch::class, $result);
		\Tester\Assert::same(2, $result->stats()->total());

		$ids = $result->hits()->ids();
		\Tester\Assert::contains('doc1', $ids);
		\Tester\Assert::contains('doc2', $ids);
	}


	public function testSearchWithMatchPhrase(): void
	{
		$this->indexTestDocument('doc1', [
			'title' => 'Quick brown fox',
			'content' => 'The quick brown fox is fast',
			'tags' => ['animals'],
		]);
		$this->indexTestDocument('doc2', [
			'title' => 'Brown quick animal',
			'content' => 'The brown quick animal ran away',
			'tags' => ['animals'],
		]);

		\usleep(1000000);

		/** @var \Spameri\Elastic\Model\Search $search */
		$search = $this->container->getByType(\Spameri\Elastic\Model\Search::class);

		$elasticQuery = new \Spameri\ElasticQuery\ElasticQuery();
		$elasticQuery->addMustQuery(
			new \Spameri\ElasticQuery\Query\MatchPhrase('content', 'quick brown'),
		);

		$result = $search->execute($elasticQuery, self::INDEX);

		// Only doc1 should match the exact phrase "quick brown"
		\Tester\Assert::same(1, $result->stats()->total());
		\Tester\Assert::contains('doc1', $result->hits()->ids());
	}


	public function testSearchWithTermFilter(): void
	{
		$this->indexTestDocument('doc1', [
			'title' => 'Tech Article',
			'content' => 'Programming is fun',
			'tags' => ['programming', 'technology'],
		]);
		$this->indexTestDocument('doc2', [
			'title' => 'Nature Documentary',
			'content' => 'Animals in the wild',
			'tags' => ['animals', 'nature'],
		]);

		\usleep(1000000);

		/** @var \Spameri\Elastic\Model\Search $search */
		$search = $this->container->getByType(\Spameri\Elastic\Model\Search::class);

		$elasticQuery = new \Spameri\ElasticQuery\ElasticQuery();
		$elasticQuery->addFilter(
			new \Spameri\ElasticQuery\Query\Term('tags', 'programming'),
		);

		$result = $search->execute($elasticQuery, self::INDEX);

		\Tester\Assert::same(1, $result->stats()->total());
		\Tester\Assert::contains('doc1', $result->hits()->ids());
	}


	public function testSearchWithMultipleFilters(): void
	{
		$this->indexTestDocument('doc1', [
			'title' => 'Tech Article',
			'content' => 'Programming is fun',
			'tags' => ['programming', 'technology'],
		]);
		$this->indexTestDocument('doc2', [
			'title' => 'Tech Documentary',
			'content' => 'Technology in nature',
			'tags' => ['technology', 'nature'],
		]);
		$this->indexTestDocument('doc3', [
			'title' => 'Programming Guide',
			'content' => 'Learn programming',
			'tags' => ['programming', 'education'],
		]);

		\usleep(1000000);

		/** @var \Spameri\Elastic\Model\Search $search */
		$search = $this->container->getByType(\Spameri\Elastic\Model\Search::class);

		$elasticQuery = new \Spameri\ElasticQuery\ElasticQuery();
		$elasticQuery->addFilter(
			new \Spameri\ElasticQuery\Query\Term('tags', 'programming'),
		);
		$elasticQuery->addFilter(
			new \Spameri\ElasticQuery\Query\Term('tags', 'technology'),
		);

		$result = $search->execute($elasticQuery, self::INDEX);

		\Tester\Assert::same(1, $result->stats()->total());
		\Tester\Assert::contains('doc1', $result->hits()->ids());
	}


	public function testSearchEmptyResults(): void
	{
		$this->indexTestDocument('doc1', [
			'title' => 'Test Document',
			'content' => 'Some content here',
			'tags' => ['test'],
		]);

		\usleep(1000000);

		/** @var \Spameri\Elastic\Model\Search $search */
		$search = $this->container->getByType(\Spameri\Elastic\Model\Search::class);

		$elasticQuery = new \Spameri\ElasticQuery\ElasticQuery();
		$elasticQuery->addMustQuery(
			new \Spameri\ElasticQuery\Query\ElasticMatch('content', 'nonexistent_term_xyz'),
		);

		$result = $search->execute($elasticQuery, self::INDEX);

		\Tester\Assert::type(\Spameri\ElasticQuery\Response\ResultSearch::class, $result);
		\Tester\Assert::same(0, $result->stats()->total());
		\Tester\Assert::count(0, $result->hits());
	}


	public function testSearchWithShouldQuery(): void
	{
		$this->indexTestDocument('doc1', [
			'title' => 'PHP Programming',
			'content' => 'PHP basics',
			'tags' => ['programming'],
		]);
		$this->indexTestDocument('doc2', [
			'title' => 'Java Programming',
			'content' => 'Java basics',
			'tags' => ['programming'],
		]);
		$this->indexTestDocument('doc3', [
			'title' => 'Python Programming',
			'content' => 'Python basics',
			'tags' => ['programming'],
		]);

		\usleep(1000000);

		/** @var \Spameri\Elastic\Model\Search $search */
		$search = $this->container->getByType(\Spameri\Elastic\Model\Search::class);

		// Use filter with Terms to match PHP OR Java
		$elasticQuery = new \Spameri\ElasticQuery\ElasticQuery();
		$elasticQuery->addFilter(new \Spameri\ElasticQuery\Query\Terms('title.keyword', ['PHP Programming', 'Java Programming']));

		$result = $search->execute($elasticQuery, self::INDEX);

		\Tester\Assert::same(2, $result->stats()->total());

		$ids = $result->hits()->ids();
		\Tester\Assert::contains('doc1', $ids);
		\Tester\Assert::contains('doc2', $ids);
		\Tester\Assert::notContains('doc3', $ids);
	}


	public function testSearchWithMustNotQuery(): void
	{
		$this->indexTestDocument('doc1', [
			'title' => 'Active Item',
			'content' => 'This is active',
			'tags' => ['active'],
		]);
		$this->indexTestDocument('doc2', [
			'title' => 'Inactive Item',
			'content' => 'This is inactive',
			'tags' => ['inactive'],
		]);

		\usleep(1000000);

		/** @var \Spameri\Elastic\Model\Search $search */
		$search = $this->container->getByType(\Spameri\Elastic\Model\Search::class);

		$elasticQuery = new \Spameri\ElasticQuery\ElasticQuery();
		$elasticQuery->addMustQuery(new \Spameri\ElasticQuery\Query\MatchAll());
		$elasticQuery->addMustNotQuery(new \Spameri\ElasticQuery\Query\Term('tags', 'inactive'));

		$result = $search->execute($elasticQuery, self::INDEX);

		\Tester\Assert::same(1, $result->stats()->total());
		\Tester\Assert::contains('doc1', $result->hits()->ids());
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

(new SearchTest())->run();
