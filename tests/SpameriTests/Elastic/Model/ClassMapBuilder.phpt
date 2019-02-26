<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Model;


require_once __DIR__ . '/../../../bootstrap.php';


class ClassMapBuilder extends \Tester\TestCase
{

	public function testBuild() : void
	{
		$data['id'] = '123456asd';
		$data['name'] = 'FooBar';

		$classMapBuilder = new \Spameri\Elastic\Model\ClassMapBuilder(
			new \Spameri\Elastic\Model\EntityMappingProvider([
				'Video' => [
					'index' => 'spameri_video',
					'class' => \SpameriTests\TestEntity::class,
				]
			])
		);

		$classMapBuilder->build();

		$map = $classMapBuilder->getMapping();

		var_dump($map);

		$entityClass = \array_keys($map);
		$entityClassKey = \reset($entityClass);
		$entityClass = '\\' . $entityClassKey;

		$idClass = \array_keys($map[$entityClassKey]);
		$idClassKey = \reset($idClass);
		$idClass = '\\' . $map[$entityClassKey][$idClassKey];

		$entity = new $entityClass(
			new $idClass($data['id']),
			$data['name']
		);

		var_dump($entity);
	}

}

(new ClassMapBuilder())->run();
