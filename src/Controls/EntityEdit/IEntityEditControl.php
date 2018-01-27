<?php declare(strict_types = 1);

namespace Spameri\Elastic\Controls\EntityEdit;


interface IEntityEditControl
{

	public function create(
		\Spameri\Elastic\Entity\IElasticEntity $entity
	) : \Spameri\Elastic\Controls\EntityEdit\EntityEditControl;
}
