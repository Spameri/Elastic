<?php declare(strict_types = 1);

namespace Spameri\Elastic\Generator;


class EntityBuilder
{

	public function buildEntity(
		string $entityClass,
		array $entityData
	) : \Nette\PhpGenerator\PhpFile
	{
		$file = new \Nette\PhpGenerator\PhpFile();
		$namespace = $file->addNamespace($entityData['namespace']);
		$class = $namespace->addClass($entityClass);
		$class->addExtend(\Spameri\Elastic\Entity\IEntity::class);
		/**
		 * @var $properties array
		 */
		$properties = $entityData['properties'];
		$this->processProperties($class, NULL, $properties);

		return $file;
	}


	public function processProperties(
		\Nette\PhpGenerator\ClassType $class,
		array $parentKey = NULL,
		array $properties
	)
	{
		$arrayNext = FALSE;

		foreach ($properties as $key => $property) {
			$propertyKey = $parentKey;
			$propertyKey[] = $key;

			if ($arrayNext) {
				$this->processArrayProperties($class, $parentKey, [
					$key => $property
				]);

			} elseif ($key === 'type') {
				if ($property === 'array') {
					$arrayNext = TRUE;
				}

			} else {
				if (\count($property) > 1 || \key($property) !== 'type') {
					$this->processProperties($class, $propertyKey, $property);

				} else {
					$this->buildProperty($class, $propertyKey, $property);
				}
			}
		}
	}


	public function buildProperty(
		\Nette\PhpGenerator\ClassType $class,
		array $key,
		array $property
	)
	{
		$arrayKey = '[\'' . implode('\'][\'', $key) . '\']';
		$propertyKey = end($key);
		$functionName = '';
		foreach ($key as $string){
			$functionName .= ucfirst($string);
		}

		$getter = 'get';
		if ($property['type'] === 'bool' || $property['type'] === 'boolean') {
			$getter = 'is';
		}
		if ($property['type'] === 'date') {
			$property['type'] = '\\' . \Nette\Utils\DateTime::class;
		}

		$method = $class->addMethod($getter . $functionName);
		$method->addBody('return $this->metadata' . $arrayKey . ';');
		$method->addComment('@return ' . $property['type']);

		$method = $class->addMethod('set' . $functionName);
		$method->addBody('$this->metadata' . $arrayKey . ' = $' . $propertyKey . ';', [$propertyKey]);
		$method->addParameter($propertyKey);
		$method->addComment('@var ' . $property['type']);

		$classProperty = $class->addProperty($propertyKey);
		$classProperty->addComment('');
		$classProperty->addComment('@var ' . $property['type']);
		$classProperty->addComment('');
	}


	public function processArrayProperties(
		\Nette\PhpGenerator\ClassType $class,
		array $key,
		array $property
	)
	{
		$arrayKey = '[\'' . implode('\'][\'', $key) . '\']';
		$propertyKey = end($key);
		$functionName = '';
		foreach ($key as $string){
			$functionName .= ucfirst($string);
		}

		$method = $class->addMethod('get' . $functionName);
		$method->addBody('$collection = new \\' . \Spameri\Model\Collection\ArrayCollection::class . '($this->metadata' . $arrayKey . ');');
		$method->addBody('$collection->setEntity(' . ucfirst(key($property)) . '::class);');
		$method->addBody('');
		$method->addBody('return $collection;');

		$method->addComment('@return \\' . \Spameri\Model\Collection\ArrayCollection::class);

		$method = $class->addMethod('set' . $functionName);
		$method->addBody('$this->metadata' . $arrayKey . ' = $' . $propertyKey . ';', [$propertyKey]);
		$method->addParameter($propertyKey);
		$method->addComment('@var \\' . \Spameri\Model\Collection\ArrayCollection::class);

		$classProperty = $class->addProperty($propertyKey);
		$classProperty->addComment('');
		$classProperty->addComment('@var \\' . \Spameri\Model\Collection\ArrayCollection::class);
		$classProperty->addComment('');
	}
}
