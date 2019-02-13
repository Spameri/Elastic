<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model\ValidateMapping;


class Display
{

	public const BLOCKING = 'blocking';
	public const TOKENIZER = 'tokenizer';
	public const ANALYZER = 'analyzer';
	public const CONFLICT = 'conflict';
	public const TYPE = 'type';
	public const UNIQUE = 'unique';

	/**
	 * @var array
	 */
	private $blockNameIssue;

	/**
	 * @var array
	 */
	private $tokenizerIssues;

	/**
	 * @var array
	 */
	private $analyzerIssue;

	/**
	 * @var array
	 */
	private $conflictingNameIssue;

	/**
	 * @var array
	 */
	private $uniqueFieldNames;

	/**
	 * @var array
	 */
	private $typeIssue;


	public function store(
		string $type,
		string $entity,
		string $propertyName,
		string $value
	) : void
	{
		switch ($type) {
			case self::BLOCKING:
				$this->blockNameIssue[$entity][$propertyName] = $value;
				break;

			case self::TOKENIZER:
				$this->tokenizerIssues[$entity][$propertyName] = $value;
				break;

			case self::ANALYZER:
				$this->analyzerIssue[$entity][$propertyName] = $value;
				break;

			case self::CONFLICT:
				$this->conflictingNameIssue[$entity][$propertyName] = $value;
				break;

			case self::TYPE:
				$this->typeIssue[$entity][$propertyName] = $value;
				break;

			case self::UNIQUE:
				$this->uniqueFieldNames[$entity][$propertyName] = $value;
				break;

			default:
				throw new \Spameri\ElasticQuery\Exception\InvalidArgumentException('Not supported type.');
				break;
		}
	}


	public function isUnique(
		string $entityName,
		string $propertyName
	) : bool
	{
		return ! isset($this->uniqueFieldNames[$entityName][$propertyName]);
	}


	public function render(
		\Symfony\Component\Console\Output\OutputInterface $output
	) : void
	{
		$blockingFailed = FALSE;
		$output->writeln('<options=bold>Blocking issues:</>');
		foreach ($this->blockNameIssue as $entityName => $blockIssues) {
			foreach ($blockIssues as $key => $blockIssue) {
				$output->writeln(
					'For Entity: <bg=magenta;options=bold>' . $entityName . '</> field: <bg=magenta;options=bold>' . $key . '</>'
					. ' is not supported config namespace: <bg=magenta;options=bold>' . $blockIssue . '</>'
				);
				$blockingFailed = TRUE;
			}
		}

		if ($blockingFailed) {
			$output->writeln('<bg=red;options=bold>FAIL</>');

		} else {
			$output->writeln('<bg=green;options=bold>OK</>');
		}
		$output->writeln('');

		/////////////////////////////////////////////////////////////////////////////////////////////////////

		$tokenizerFailed = FALSE;
		$output->writeln('<options=bold>Tokenizer issues:</>');
		foreach ($this->tokenizerIssues as $entityName => $tokenizerIssues) {
			foreach ($tokenizerIssues as $key => $tokenizerIssue) {
				$output->writeln(
					'For Entity: <bg=magenta;options=bold>' . $entityName . '</> field: <bg=magenta;options=bold>' . $key . '</>'
					. ' has not supported tokenizer: <bg=magenta;options=bold>' . $tokenizerIssue . '</>'
				);
			}
			$tokenizerFailed = TRUE;
		}

		if ($tokenizerFailed) {
			$output->writeln('<bg=red;options=bold>FAIL</>');

		} else {
			$output->writeln('<bg=green;options=bold>OK</>');
		}
		$output->writeln('');

		/////////////////////////////////////////////////////////////////////////////////////////////////////

		$analyzerFailed = FALSE;
		$output->writeln('<options=bold>Analyzer issues:</>');
		foreach ($this->analyzerIssue as $entityName => $analyzerIssues) {
			foreach ($analyzerIssues as $key => $analyzerIssue) {
				$output->writeln(
					'For Entity: <bg=magenta;options=bold>' . $entityName . '</> field: <bg=magenta;options=bold>' . $key . '</>'
					. ' has not supported analyzer: <bg=magenta;options=bold>' . $analyzerIssue . '</>'
				);
				$analyzerFailed = TRUE;
			}
		}

		if ($analyzerFailed) {
			$output->writeln('<bg=red;options=bold>FAIL</>');

		} else {
			$output->writeln('<bg=green;options=bold>OK</>');
		}
		$output->writeln('');

		/////////////////////////////////////////////////////////////////////////////////////////////////////

		$conflictingFailed = FALSE;
		$output->writeln('<options=bold>Conflicting issues:</>');
		foreach ($this->conflictingNameIssue as $entityName => $conflictingFieldNames) {
			foreach ($conflictingFieldNames as $conflictingFieldName) {
				$output->writeln(
					'For Entity: <bg=magenta;options=bold>' . $entityName . '</> field name: <bg=magenta;options=bold>' . $conflictingFieldName . '</>'
					. ' is not unique in index context, this may lead to issues.'
				);
				$conflictingFailed = TRUE;
			}
		}

		if ($conflictingFailed) {
			$output->writeln('<bg=red;options=bold>FAIL</>');

		} else {
			$output->writeln('<bg=green;options=bold>OK</>');
		}
		$output->writeln('');

		/////////////////////////////////////////////////////////////////////////////////////////////////////

		$typeFailed = FALSE;
		$output->writeln('<options=bold>Type issues:</>');
		foreach ($this->typeIssue as $entityName => $typeIssues) {
			foreach ($typeIssues as $key => $typeIssue) {
				$output->writeln(
					'For Entity: <bg=magenta;options=bold>' . $entityName . '</> field: <bg=magenta;options=bold>' . $key . '</>'
					. ' has not supported type: <bg=magenta;options=bold>' . $typeIssue . '</>'
				);
				$typeFailed = TRUE;
			}
		}
		if ($typeFailed) {
			$output->writeln('<bg=red;options=bold>FAIL</>');

		} else {
			$output->writeln('<bg=green;options=bold>OK</>');
		}
	}

}
