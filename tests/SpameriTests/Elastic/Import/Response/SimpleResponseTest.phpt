<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Import\Response;

require_once __DIR__ . '/../../../../bootstrap-unit.php';

/**
 * @testCase
 */
class SimpleResponseTest extends \Tester\TestCase
{

	public function testImplementsResponseInterface(): void
	{
		$entity = new \SpameriTests\Elastic\Data\Entity\TestImportEntity('key1', 'Test', 100);
		$response = new \Spameri\Elastic\Import\Response\SimpleResponse('success', $entity);

		\Tester\Assert::type(\Spameri\Elastic\Import\ResponseInterface::class, $response);
	}


	public function testIsSuccessfulReturnsTrueForTruthyResponse(): void
	{
		$entity = new \SpameriTests\Elastic\Data\Entity\TestImportEntity('key1', 'Test', 100);

		$response1 = new \Spameri\Elastic\Import\Response\SimpleResponse('success', $entity);
		\Tester\Assert::true($response1->isSuccessful());

		$response2 = new \Spameri\Elastic\Import\Response\SimpleResponse(true, $entity);
		\Tester\Assert::true($response2->isSuccessful());

		$response3 = new \Spameri\Elastic\Import\Response\SimpleResponse(1, $entity);
		\Tester\Assert::true($response3->isSuccessful());

		$response4 = new \Spameri\Elastic\Import\Response\SimpleResponse(['data' => 'value'], $entity);
		\Tester\Assert::true($response4->isSuccessful());
	}


	public function testIsSuccessfulReturnsFalseForFalsyResponse(): void
	{
		$entity = new \SpameriTests\Elastic\Data\Entity\TestImportEntity('key1', 'Test', 100);

		$response1 = new \Spameri\Elastic\Import\Response\SimpleResponse(null, $entity);
		\Tester\Assert::false($response1->isSuccessful());

		$response2 = new \Spameri\Elastic\Import\Response\SimpleResponse(false, $entity);
		\Tester\Assert::false($response2->isSuccessful());

		$response3 = new \Spameri\Elastic\Import\Response\SimpleResponse(0, $entity);
		\Tester\Assert::false($response3->isSuccessful());

		$response4 = new \Spameri\Elastic\Import\Response\SimpleResponse('', $entity);
		\Tester\Assert::false($response4->isSuccessful());
	}


	public function testGetResponseReturnsOriginalResponse(): void
	{
		$entity = new \SpameriTests\Elastic\Data\Entity\TestImportEntity('key1', 'Test', 100);

		// Test with string
		$response1 = new \Spameri\Elastic\Import\Response\SimpleResponse('my-response-data', $entity);
		\Tester\Assert::same('my-response-data', $response1->getResponse());

		// Test with array
		$arrayData = ['id' => '123', 'status' => 'created'];
		$response2 = new \Spameri\Elastic\Import\Response\SimpleResponse($arrayData, $entity);
		\Tester\Assert::same($arrayData, $response2->getResponse());

		// Test with null
		$response3 = new \Spameri\Elastic\Import\Response\SimpleResponse(null, $entity);
		\Tester\Assert::null($response3->getResponse());

		// Test with integer
		$response4 = new \Spameri\Elastic\Import\Response\SimpleResponse(42, $entity);
		\Tester\Assert::same(42, $response4->getResponse());
	}


	public function testGetEntityReturnsOriginalEntity(): void
	{
		$entity = new \SpameriTests\Elastic\Data\Entity\TestImportEntity('key1', 'Test Name', 100);
		$response = new \Spameri\Elastic\Import\Response\SimpleResponse('success', $entity);

		$returnedEntity = $response->getEntity();

		\Tester\Assert::same($entity, $returnedEntity);
		\Tester\Assert::same('key1', $returnedEntity->key());
		\Tester\Assert::same('Test Name', $returnedEntity->name);
		\Tester\Assert::same(100, $returnedEntity->value);
	}


	public function testResponseWithObjectData(): void
	{
		$entity = new \SpameriTests\Elastic\Data\Entity\TestImportEntity('key1', 'Test', 100);
		$objectResponse = new \stdClass();
		$objectResponse->result = 'created';
		$objectResponse->_id = 'abc123';

		$response = new \Spameri\Elastic\Import\Response\SimpleResponse($objectResponse, $entity);

		\Tester\Assert::true($response->isSuccessful());
		\Tester\Assert::same($objectResponse, $response->getResponse());
		\Tester\Assert::same('created', $response->getResponse()->result);
	}


	public function testResponseWithElasticSearchLikeData(): void
	{
		$entity = new \SpameriTests\Elastic\Data\Entity\TestImportEntity('doc-123', 'Product', 50);
		$esResponse = [
			'_index' => 'products',
			'_type' => '_doc',
			'_id' => 'doc-123',
			'_version' => 1,
			'result' => 'created',
			'_shards' => [
				'total' => 2,
				'successful' => 1,
				'failed' => 0,
			],
		];

		$response = new \Spameri\Elastic\Import\Response\SimpleResponse($esResponse, $entity);

		\Tester\Assert::true($response->isSuccessful());
		\Tester\Assert::same($esResponse, $response->getResponse());
		\Tester\Assert::same('doc-123', $response->getResponse()['_id']);
		\Tester\Assert::same('created', $response->getResponse()['result']);
	}


	public function testReadonlyProperty(): void
	{
		$entity = new \SpameriTests\Elastic\Data\Entity\TestImportEntity('key1', 'Test', 100);
		$response = new \Spameri\Elastic\Import\Response\SimpleResponse('data', $entity);

		// Verify the class is readonly (reflection check)
		$reflection = new \ReflectionClass($response);
		\Tester\Assert::true($reflection->isReadOnly());
	}

}

(new SimpleResponseTest())->run();
