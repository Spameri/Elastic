<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model;


class ValidateMapping
{

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

	/**
	 * @var array
	 */
	private $entities;

	/**
	 * @var array
	 */
	private $settings;


	public function __construct(
		array $entities
		, array $settings
	)
	{
		$this->entities = $entities;
		$this->settings = $settings;
	}


	public function validate() : void
	{
		foreach ($this->entities as $entity) {
			$this->iterate($entity['properties']);
		}
	}

	public function display(\Symfony\Component\Console\Output\OutputInterface $output) : void
	{
		foreach ($this->blockNameIssue as $key => $blockIssue) {
			$output->writeln('Field: ' . $key . ' is not supported config namespace: ' . $blockIssue);
		}
		foreach ($this->tokenizerIssues as $key => $tokenizerIssue) {
			$output->writeln('Field: ' . $key . ' has not supported tokenizer: ' . $tokenizerIssue);
		}
		foreach ($this->analyzerIssue as $key => $analyzerIssue) {
			$output->writeln('Field: ' . $key . ' has not supported analyzer: ' . $analyzerIssue);
		}
		foreach ($this->conflictingNameIssue as $conflictingFieldName) {
			$output->writeln('Field name: ' . $conflictingFieldName . ' is not unique in index context, this may lead to issues.');
		}
		foreach ($this->typeIssue as $key => $typeIssue) {
			$output->writeln('Field: ' . $key . ' has not supported type: ' . $typeIssue);
		}
	}

	private function iterate(
		array $properties
	): void
	{
		foreach ($properties as $propertyName => $property) {
			foreach ($property as $propertyParameterName => $propertyData) {
				if ( ! \in_array($propertyParameterName, \Spameri\Elastic\Model\ValidateMapping\AllowedValues::BLOCKS, TRUE)) {
					$this->blockNameIssue[$propertyName] = $propertyParameterName;
				}
			}
			if (isset($property['type'])) {
				if ( ! isset(\Spameri\Elastic\Model\ValidateMapping\AllowedValues::TYPES[$property['type']])) {
					$this->typeIssue[$propertyName] = $property['type'];
				}
			}

			if ( ! isset($this->uniqueFieldNames[$propertyName])) {
				$this->uniqueFieldNames[$propertyName] = $propertyName;

			} else {
				$this->conflictingNameIssue[$propertyName] = $propertyName;
			}

			if (isset($property['analyzer'])) {
				if ( ! isset(\Spameri\Elastic\Model\ValidateMapping\AllowedValues::ANALYZERS[$property['analyzer']])) {
					if ( ! isset($this->settings['analysis']['analyzer'])) {
						$this->analyzerIssue[$propertyName] = $property['analyzer'];
					}
				}
			}

			if (isset($property['tokenizer'])) {
				if ( ! isset(\Spameri\Elastic\Model\ValidateMapping\AllowedValues::TOKENIZERS[$property['tokenizer']])) {
					if ( ! isset($this->settings['tokenizer'])) {
						$this->tokenizerIssues[$propertyName] = $property['tokenizer'];
					}
				}
			}

			if (isset($property['properties'])) {
				$this->iterate($property['properties']);
			}
		}
	}

}
