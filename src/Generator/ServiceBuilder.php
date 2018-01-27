<?php declare(strict_types = 1);

namespace Spameri\Elastic\Generator;


class ServiceBuilder
{

	public function buildService(
		string $entityClass,
		array $entityData
	) : \Nette\PhpGenerator\PhpFile
	{
		$file = new \Nette\PhpGenerator\PhpFile();
		$namespace = $file->addNamespace($entityData['namespace']);
		$class = $namespace->addClass($entityClass);
		$class->addExtend(\Spameri\Elastic\Model\BaseService::class);

		// Insert
		$method = $class->addMethod('insert');
		$method->addParameter('entity');
		$method->addComment('@param \App\Elastic\Entity\IEntity|' . $entityData['class'] . ' $entity');
		$method->addComment('@return bool');
		$method->addBody('return parent::insert($entity);');

		// Get
		$method = $class->addMethod('get');
		$method->addParameter('id');
		$method->addComment('@param string $id');
		$method->addComment('@return bool|' . $entityData['class']);
		$method->addBody('$data = parent::get($id);');
		$method->addBody('');
		$method->addBody('if ($data) {');
		$method->addBody('	return new ' . $entityData['class'] . '($data);');
		$method->addBody('');
		$method->addBody('} else {');
		$method->addBody('	return FALSE;');
		$method->addBody('}');

		// GetBy
		$method = $class->addMethod('getBy');
		$method->addParameter('options');
		$method->addComment('@param array $options');
		$method->addComment('@return bool|' . $entityData['class']);
		$method->addBody('$data = parent::getBy($options);');
		$method->addBody('');
		$method->addBody('if ($data) {');
		$method->addBody('	return new ' . $entityData['class'] . '($data);');
		$method->addBody('');
		$method->addBody('} else {');
		$method->addBody('	return FALSE;');
		$method->addBody('}');

		return $file;
	}
}
