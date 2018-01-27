<?php declare(strict_types = 1);

namespace Spameri\Elastic\Generator;


class FileBuilder
{

	/**
	 * @var EntityBuilder
	 */
	private $entityBuilder;

	/**
	 * @var ServiceBuilder
	 */
	private $serviceBuilder;


	public function __construct(
		EntityBuilder $entityBuilder
		, ServiceBuilder $serviceBuilder
	)
	{
		$this->entityBuilder = $entityBuilder;
		$this->serviceBuilder = $serviceBuilder;
	}


	public function buildForEntity(
		string $entityClass,
		array $entityData
	)
	{
		$entityFile = $this->entityBuilder->buildEntity($entityClass, $entityData);
		$this->writeFile($entityFile, $entityData['namespace'] . '/' . $entityClass . '.php');

		$serviceFile = $this->serviceBuilder->buildService($entityClass . 'Service', $entityData);
		$this->writeFile($serviceFile, $entityData['namespace'] . '/' . $entityClass . 'Service' . '.php');
	}


	public function writeFile(
		\Nette\PhpGenerator\PhpFile $phpFile,
		string $filePath
	)
	{
		$namespace = explode('\\', $filePath);
		$directoryPath = __DIR__ . '/../../../' . implode('/', $namespace);
		$file = fopen($directoryPath, 'wb');
		fwrite($file, $phpFile);
		fclose($file);
	}
}
