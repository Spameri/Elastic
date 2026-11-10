<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\EdgeCases;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for handling Unicode characters and special ElasticSearch reserved characters.
 * @testCase
 */
class SpecialCharactersTest extends \SpameriTests\Elastic\AbstractTestCase
{

	protected function setUp(): void
	{
		parent::setUp();

		// Delete any existing index or alias with wildcard
		/** @var \Spameri\Elastic\ClientProvider $clientProvider */
		$clientProvider = $this->container->getByType(\Spameri\Elastic\ClientProvider::class);
		try {
			$clientProvider->client()->indices()->delete(['index' => \SpameriTests\Elastic\Config::INDEX_EDGE_CASE . '*']);
		} catch (\Throwable $e) {
			// Ignore if index doesn't exist
		}

		\usleep(100000);

		/** @var \Spameri\Elastic\Model\Indices\Create $create */
		$create = $this->container->getByType(\Spameri\Elastic\Model\Indices\Create::class);
		$create->execute(\SpameriTests\Elastic\Config::INDEX_EDGE_CASE, []);

		// Wait for index to be ready
		\usleep(100000);
	}


	public function testUnicodeLatinExtended(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);

		// Czech/Slovak special characters
		$unicodeText = 'Příliš žluťoučký kůň úpěl ďábelské ódy';

		$entity = new \SpameriTests\Elastic\Data\Entity\EdgeCaseEntity(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			$unicodeText,
			'Czech text content',
		);

		$id = $entityManager->persist($entity);

		// Wait for ES to index
		\usleep(200000);

		$retrieved = $entityManager->find(
			$id,
			\SpameriTests\Elastic\Data\Entity\EdgeCaseEntity::class,
		);

		\Tester\Assert::notNull($retrieved);
		\Tester\Assert::same($unicodeText, $retrieved->name);
	}


	public function testUnicodeAsianCharacters(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);

		// Japanese and Chinese characters
		$japaneseText = '日本語テキスト';
		$chineseText = '中文文本内容';

		$entity = new \SpameriTests\Elastic\Data\Entity\EdgeCaseEntity(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			$japaneseText,
			$chineseText,
		);

		$id = $entityManager->persist($entity);

		// Wait for ES to index
		\usleep(200000);

		$retrieved = $entityManager->find(
			$id,
			\SpameriTests\Elastic\Data\Entity\EdgeCaseEntity::class,
		);

		\Tester\Assert::notNull($retrieved);
		\Tester\Assert::same($japaneseText, $retrieved->name);
		\Tester\Assert::same($chineseText, $retrieved->content);
	}


	public function testUnicodeArabicHebrew(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);

		// Arabic and Hebrew (RTL text)
		$arabicText = 'النص العربي';
		$hebrewText = 'טקסט עברית';

		$entity = new \SpameriTests\Elastic\Data\Entity\EdgeCaseEntity(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			$arabicText,
			$hebrewText,
		);

		$id = $entityManager->persist($entity);

		// Wait for ES to index
		\usleep(200000);

		$retrieved = $entityManager->find(
			$id,
			\SpameriTests\Elastic\Data\Entity\EdgeCaseEntity::class,
		);

		\Tester\Assert::notNull($retrieved);
		\Tester\Assert::same($arabicText, $retrieved->name);
		\Tester\Assert::same($hebrewText, $retrieved->content);
	}


	public function testUnicodeEmojis(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);

		// Various emoji characters
		$emojiText = '🎉 Hello 👋 World 🌍 Test 🚀 Data 💻';

		$entity = new \SpameriTests\Elastic\Data\Entity\EdgeCaseEntity(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			'emoji-test',
			$emojiText,
		);

		$id = $entityManager->persist($entity);

		// Wait for ES to index
		\usleep(200000);

		$retrieved = $entityManager->find(
			$id,
			\SpameriTests\Elastic\Data\Entity\EdgeCaseEntity::class,
		);

		\Tester\Assert::notNull($retrieved);
		\Tester\Assert::same($emojiText, $retrieved->content);
	}


	public function testElasticSearchReservedCharactersInContent(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);

		// ES reserved characters: + - = && || > < ! ( ) { } [ ] ^ " ~ * ? : \ /
		$reservedChars = 'Query with + - = && || > < ! ( ) { } [ ] ^ " ~ * ? : \\ / characters';

		$entity = new \SpameriTests\Elastic\Data\Entity\EdgeCaseEntity(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			'reserved-chars-test',
			$reservedChars,
		);

		$id = $entityManager->persist($entity);

		// Wait for ES to index
		\usleep(200000);

		$retrieved = $entityManager->find(
			$id,
			\SpameriTests\Elastic\Data\Entity\EdgeCaseEntity::class,
		);

		\Tester\Assert::notNull($retrieved);
		\Tester\Assert::same($reservedChars, $retrieved->content);
	}


	public function testSpecialHtmlEntities(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);

		// HTML entities and special HTML characters
		$htmlContent = '<div class="test">&amp; &lt; &gt; &quot; &#39; &nbsp;</div>';

		$entity = new \SpameriTests\Elastic\Data\Entity\EdgeCaseEntity(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			'html-entities',
			$htmlContent,
		);

		$id = $entityManager->persist($entity);

		// Wait for ES to index
		\usleep(200000);

		$retrieved = $entityManager->find(
			$id,
			\SpameriTests\Elastic\Data\Entity\EdgeCaseEntity::class,
		);

		\Tester\Assert::notNull($retrieved);
		\Tester\Assert::same($htmlContent, $retrieved->content);
	}


	public function testNewlinesAndTabs(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);

		// Whitespace characters
		$whitespaceContent = "Line1\nLine2\rLine3\r\nLine4\tTabbed";

		$entity = new \SpameriTests\Elastic\Data\Entity\EdgeCaseEntity(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			'whitespace-test',
			$whitespaceContent,
		);

		$id = $entityManager->persist($entity);

		// Wait for ES to index
		\usleep(200000);

		$retrieved = $entityManager->find(
			$id,
			\SpameriTests\Elastic\Data\Entity\EdgeCaseEntity::class,
		);

		\Tester\Assert::notNull($retrieved);
		\Tester\Assert::same($whitespaceContent, $retrieved->content);
	}


	public function testNullBytes(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);

		// Content without null bytes (null bytes can cause issues in JSON)
		$content = "Before\x00After";

		// Sanitize null bytes as ES may not handle them well
		$sanitizedContent = \str_replace("\x00", '', $content);

		$entity = new \SpameriTests\Elastic\Data\Entity\EdgeCaseEntity(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			'null-byte-test',
			$sanitizedContent,
		);

		$id = $entityManager->persist($entity);

		// Wait for ES to index
		\usleep(200000);

		$retrieved = $entityManager->find(
			$id,
			\SpameriTests\Elastic\Data\Entity\EdgeCaseEntity::class,
		);

		\Tester\Assert::notNull($retrieved);
		\Tester\Assert::same($sanitizedContent, $retrieved->content);
	}


	public function testMixedMultilingualContent(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);

		// Mixed content with multiple scripts
		$mixedContent = 'English Čeština 日本語 العربية Ελληνικά Кириллица';

		$entity = new \SpameriTests\Elastic\Data\Entity\EdgeCaseEntity(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			'multilingual',
			$mixedContent,
		);

		$id = $entityManager->persist($entity);

		// Wait for ES to index
		\usleep(200000);

		$retrieved = $entityManager->find(
			$id,
			\SpameriTests\Elastic\Data\Entity\EdgeCaseEntity::class,
		);

		\Tester\Assert::notNull($retrieved);
		\Tester\Assert::same($mixedContent, $retrieved->content);
	}


	public function testSearchForUnicodeContent(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);

		$czechText = 'žluťoučký';

		$entity = new \SpameriTests\Elastic\Data\Entity\EdgeCaseEntity(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			$czechText,
			'content for search test',
		);

		$entityManager->persist($entity);

		// Wait for ES to index
		\usleep(300000);

		// Search for the Czech text using match query
		$query = new \Spameri\ElasticQuery\ElasticQuery();
		$query->addMustQuery(new \Spameri\ElasticQuery\Query\Term('name.keyword', $czechText));

		$result = $entityManager->findBy(
			$query,
			\SpameriTests\Elastic\Data\Entity\EdgeCaseEntity::class,
		);

		\Tester\Assert::same(1, $result->count());
		\Tester\Assert::same($czechText, $result->first()->name);
	}


	public function testEmptyString(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);

		$entity = new \SpameriTests\Elastic\Data\Entity\EdgeCaseEntity(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			'',
			'',
		);

		$id = $entityManager->persist($entity);

		// Wait for ES to index
		\usleep(200000);

		$retrieved = $entityManager->find(
			$id,
			\SpameriTests\Elastic\Data\Entity\EdgeCaseEntity::class,
		);

		\Tester\Assert::notNull($retrieved);
		\Tester\Assert::same('', $retrieved->name);
		\Tester\Assert::same('', $retrieved->content);
	}


	public function testJsonSpecialCharacters(): void
	{
		/** @var \Spameri\Elastic\EntityManager $entityManager */
		$entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);

		// Characters that need escaping in JSON
		$jsonSpecial = '{"key": "value with \\ backslash and \" quotes"}';

		$entity = new \SpameriTests\Elastic\Data\Entity\EdgeCaseEntity(
			new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
			'json-chars',
			$jsonSpecial,
		);

		$id = $entityManager->persist($entity);

		// Wait for ES to index
		\usleep(200000);

		$retrieved = $entityManager->find(
			$id,
			\SpameriTests\Elastic\Data\Entity\EdgeCaseEntity::class,
		);

		\Tester\Assert::notNull($retrieved);
		\Tester\Assert::same($jsonSpecial, $retrieved->content);
	}


	protected function tearDown(): void
	{
		/** @var \Spameri\Elastic\Model\Indices\Delete $delete */
		$delete = $this->container->getByType(\Spameri\Elastic\Model\Indices\Delete::class);

		try {
			$delete->execute(\SpameriTests\Elastic\Config::INDEX_EDGE_CASE);
		} catch (\Throwable $e) {
			// Ignore if index doesn't exist
		}
	}

}

(new SpecialCharactersTest())->run();
