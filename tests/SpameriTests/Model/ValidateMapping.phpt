<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Model;


require_once __DIR__ . '/../../bootstrap.php';


class ValidateMapping extends \Tester\TestCase
{

	public function testValidateEmpty(): void
	{
		$display = new \Spameri\Elastic\Model\ValidateMapping\Display();
		$validateMapping = new \Spameri\Elastic\Model\ValidateMapping(
			[],
			[],
			$display
		);

		$validateMapping->validate();
		\Tester\Assert::count(0, $display->blockNameIssue());
		\Tester\Assert::count(0, $display->tokenizerIssues());
		\Tester\Assert::count(0, $display->analyzerIssue());
		\Tester\Assert::count(0, $display->conflictingNameIssue());
		\Tester\Assert::count(0, $display->uniqueFieldNames());
		\Tester\Assert::count(0, $display->typeIssue());
	}


	public function testValidateFull(): void
	{
		$display = new \Spameri\Elastic\Model\ValidateMapping\Display();
		$entities = \Nette\Neon\Neon::decode(\file_get_contents(__DIR__ . '/../Data/Config/Video.neon'));
		$validateMapping = new \Spameri\Elastic\Model\ValidateMapping(
			$entities['elasticSearch']['entities'],
			[],
			$display
		);

		$validateMapping->validate();
		\Tester\Assert::count(0, $display->blockNameIssue());
		\Tester\Assert::count(0, $display->tokenizerIssues());
		\Tester\Assert::count(0, $display->analyzerIssue());
		\Tester\Assert::count(8, $display->conflictingNameIssue()['Video']);
		\Tester\Assert::count(63, $display->uniqueFieldNames()['Video']);
		\Tester\Assert::count(0, $display->typeIssue());
	}


	public function testValidateFullWithError(): void
	{
		$display = new \Spameri\Elastic\Model\ValidateMapping\Display();
		$entities = \Nette\Neon\Neon::decode(\file_get_contents(__DIR__ . '/../Data/Config/Video.neon'));
		$entities['elasticSearch']['entities']['Video']['properties']['extraField']['properties']['name']['type'] = 'string';
		$entities['elasticSearch']['entities']['Video']['properties']['name']['typ'] = 'string';
		$entities['elasticSearch']['entities']['Video']['properties']['year']['tokenizer'] = 'floatWell';
		$entities['elasticSearch']['entities']['Video']['properties']['month']['tokenizer'] = 'number';
		$entities['elasticSearch']['entities']['Video']['properties']['year']['analyzer'] = 'floatWell';


		$validateMapping = new \Spameri\Elastic\Model\ValidateMapping(
			$entities['elasticSearch']['entities'],
			[],
			$display
		);

		$validateMapping->validate();
		\Tester\Assert::count(1, $display->blockNameIssue()['Video']);
		\Tester\Assert::count(2, $display->tokenizerIssues()['Video']);
		\Tester\Assert::count(1, $display->analyzerIssue()['Video']);
		\Tester\Assert::count(8, $display->conflictingNameIssue()['Video']);
		\Tester\Assert::count(65, $display->uniqueFieldNames()['Video']);
		\Tester\Assert::count(1, $display->typeIssue()['Video']);
	}

}
(new ValidateMapping())->run();
