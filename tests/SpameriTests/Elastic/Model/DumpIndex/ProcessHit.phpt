<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Model\DumpIndex;

require_once __DIR__ . '/../../../../bootstrap.php';

/**
 * @testCase
 */
class ProcessHit extends \SpameriTests\Elastic\AbstractTestCase
{

	public function testProcess(): void
	{
		/** @var \Spameri\ElasticQuery\Response\ResultMapper $resultMapper */
		$resultMapper = $this->container->getByType(\Spameri\ElasticQuery\Response\ResultMapper::class);
		$hits = $resultMapper->mapHits(
			[
				'hits' => [
					'hits' => [
						0 => [
							'_index' => 'spameri_guessed-2018-11-29_21-25-34',
							'_type' => 'spameri_guessed',
							'_id' => 'EWhVcWcBhsjlL-GzEP8i',
							'_score' => 1.0,
							'_source' => [
								'guess' => '7kASSmUBq9pZLj7-1Uv4',
								'user' => 'xyC3UmcBeqWWbOzi4uKU',
								'guessed' => 'asdf',
								'when' => '2018-12-02T23:50:58',
								'success' => FALSE,
								'rank' => 0,
							],
						],
					],
				],
			]
		);

		/** @var \Spameri\ElasticQuery\Response\Result\Hit $hit */
		$hit = $hits->getIterator()->offsetGet(0);

		/** @var \Spameri\Elastic\Model\DumpIndex $dumpIndex */
		$dumpIndex = $this->container->getByType(\Spameri\Elastic\Model\DumpIndex::class);

		$dumpIndex->processHit($hit);
		$dumpIndex->writeToFile('test.log');

		\Tester\Assert::same(
			'{"index":{"_index":"spameri_guessed-2018-11-29_21-25-34","_type":"spameri_guessed","_id":"EWhVcWcBhsjlL-GzEP8i"}}'
			. "\r\n"
			. '{"guess":"7kASSmUBq9pZLj7-1Uv4","user":"xyC3UmcBeqWWbOzi4uKU","guessed":"asdf","when":"2018-12-02T23:50:58","success":false,"rank":0}'
			. "\r\n",
			\Nette\Utils\FileSystem::read('test.log')
		);
	}


	protected function tearDown(): void
	{
		\Nette\Utils\FileSystem::delete('test.log');
		parent::tearDown();
	}

}

(new ProcessHit())->run();
