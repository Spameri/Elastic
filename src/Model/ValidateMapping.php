<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model;


class ValidateMapping
{

	/**
	 * @var array
	 */
	private $entities;

	/**
	 * @var array
	 */
	private $settings;

	/**
	 * @var \Spameri\Elastic\Model\ValidateMapping\Display
	 */
	private $display;


	public function __construct(
		array $entities
		, array $settings
		, \Spameri\Elastic\Model\ValidateMapping\Display $display
	)
	{
		$this->entities = $entities;
		$this->settings = $settings;
		$this->display = $display;
	}


	public function validate() : void
	{
		foreach ($this->entities as $entityName => $entity) {
			$this->iterate($entityName, $entity['properties']);
		}
	}


	public function display(
		\Symfony\Component\Console\Output\OutputInterface $output
	) : void
	{
		$this->display->render($output);
	}


	private function iterate(
		string $entityName,
		array $properties
	) : void
	{
		foreach ($properties as $propertyName => $property) {
			foreach ($property as $propertyParameterName => $propertyData) {
				if ( ! \in_array($propertyParameterName, \Spameri\Elastic\Model\ValidateMapping\AllowedValues::BLOCKS, TRUE)) {
					$this->display->store(
						\Spameri\Elastic\Model\ValidateMapping\Display::BLOCKING,
						$entityName,
						$propertyName,
						$propertyParameterName
					);
				}
			}
			if (isset($property['type'])) {
				if ( ! isset(\Spameri\Elastic\Model\ValidateMapping\AllowedValues::TYPES[$property['type']])) {
					$this->display->store(
						\Spameri\Elastic\Model\ValidateMapping\Display::TYPE,
						$entityName,
						$propertyName,
						$property['type']
					);
				}
			}

			if ($this->display->isUnique($entityName, $propertyName)) {
				$this->display->store(
					\Spameri\Elastic\Model\ValidateMapping\Display::UNIQUE,
					$entityName,
					$propertyName,
					$propertyName
				);

			} else {
				$this->display->store(
					\Spameri\Elastic\Model\ValidateMapping\Display::CONFLICT,
					$entityName,
					$propertyName,
					$propertyName
				);
			}

			if (isset($property['analyzer'])) {
				if ( ! isset(\Spameri\Elastic\Model\ValidateMapping\AllowedValues::ANALYZERS[$property['analyzer']])) {
					if ( ! isset($this->settings['analysis']['analyzer'])) {
						$this->display->store(
							\Spameri\Elastic\Model\ValidateMapping\Display::ANALYZER,
							$entityName,
							$propertyName,
							$property['analyzer']
						);
					}
				}
			}

			if (isset($property['tokenizer'])) {
				if ( ! isset(\Spameri\Elastic\Model\ValidateMapping\AllowedValues::TOKENIZERS[$property['tokenizer']])) {
					if ( ! isset($this->settings['tokenizer'])) {
						$this->display->store(
							\Spameri\Elastic\Model\ValidateMapping\Display::TOKENIZER,
							$entityName,
							$propertyName,
							$property['tokenizer']
						);
					}
				}
			}

			if (isset($property['properties'])) {
				$this->iterate($entityName, $property['properties']);
			}
		}
	}

}
