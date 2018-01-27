<?php declare(strict_types = 1);

namespace Spameri\Elastic\Controls\EntityEdit;


trait TEntityEditControl
{

	/**
	 * @var IEntityEditControl @inject
	 */
	public $entityEdit;


	public function createComponentEntityEdit() : \Spameri\Elastic\Controls\EntityEdit\EntityEditControl
	{
		return $this->entityEdit->create($this->getEntity());
	}

}
