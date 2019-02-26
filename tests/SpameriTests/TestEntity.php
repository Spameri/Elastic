<?php declare(strict_types = 1);

namespace SpameriTests;


class TestEntity extends \Spameri\Elastic\Entity\BaseEntity
{

	/**
	 * @var string
	 */
	private $name;


	public function __construct(
		\Spameri\Elastic\Entity\Property\ElasticId $id,
		string $name
	)
	{
		parent::__construct($id);
		$this->name = $name;
	}


	public function name() : string
	{
		return $this->name;
	}

}
