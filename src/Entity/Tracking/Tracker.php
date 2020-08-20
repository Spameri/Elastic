<?php declare(strict_types = 1);

namespace Spameri\Elastic\Entity\Tracking;


class Tracker
{

	/**
	 * @var string
	 */
	private $created;

	/**
	 * @var \Spameri\Elastic\Entity\Property\ElasticIdInterface
	 */
	private $createdBy;

	/**
	 * @var string
	 */
	private $edited;

	/**
	 * @var \Spameri\Elastic\Entity\Property\ElasticIdInterface
	 */
	private $editedBy;


	public function __construct(
		string $created
		, \Spameri\Elastic\Entity\Property\ElasticIdInterface $createdBy
		, string $edited
		, \Spameri\Elastic\Entity\Property\ElasticIdInterface $editedBy
	)
	{
		$this->created = $created;
		$this->createdBy = $createdBy;
		$this->edited = $edited;
		$this->editedBy = $editedBy;
	}

	public function initialize(
		string $created
		, \Spameri\Elastic\Entity\Property\ElasticId $createdBy
	) : void
	{
		$this->created = $created;
		$this->createdBy = $createdBy;
	}

	public function edit(
		string $edited
		, \Spameri\Elastic\Entity\Property\ElasticId $editedBy
	) : void
	{
		$this->edited = $edited;
		$this->editedBy = $editedBy;
	}


	public function created() : string
	{
		return $this->created;
	}


	public function createdBy() : \Spameri\Elastic\Entity\Property\ElasticIdInterface
	{
		return $this->createdBy;
	}


	public function edited() : string
	{
		return $this->edited;
	}


	public function editedBy() : \Spameri\Elastic\Entity\Property\ElasticIdInterface
	{
		return $this->editedBy;
	}

}
