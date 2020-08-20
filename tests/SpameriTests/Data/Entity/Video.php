<?php declare(strict_types = 1);

namespace SpameriTests\Data\Entity;


class Video implements \Spameri\Elastic\Entity\ElasticEntityInterface
{

	/**
	 * @var \Spameri\Elastic\Entity\Property\ElasticIdInterface
	 */
	private $id;

	/**
	 * @var \SpameriTests\Data\Entity\Video\Identification
	 */
	private $identification;

	/**
	 * @var \SpameriTests\Data\Entity\Property\Name
	 */
	private $name;

	/**
	 * @var \SpameriTests\Data\Entity\Property\Year
	 */
	private $year;

	/**
	 * @var \SpameriTests\Data\Entity\Video\Technical
	 */
	private $technical;

	/**
	 * @var \SpameriTests\Data\Entity\Video\Story
	 */
	private $story;

	/**
	 * @var \SpameriTests\Data\Entity\Video\Details
	 */
	private $details;

	/**
	 * @var \SpameriTests\Data\Entity\Video\HighLights
	 */
	private $highLights;

	/**
	 * @var \SpameriTests\Data\Entity\Video\Connections
	 */
	private $connections;

	/**
	 * @var \SpameriTests\Data\Entity\Video\SeasonCollection
	 */
	private $season;

	/**
	 * @var \SpameriTests\Data\Entity\Video\People
	 */
	private $people;


	public function __construct(
		\Spameri\Elastic\Entity\Property\ElasticIdInterface $id
		, \SpameriTests\Data\Entity\Video\Identification $identification
		, \SpameriTests\Data\Entity\Property\Name $name
		, \SpameriTests\Data\Entity\Property\Year $year
		, \SpameriTests\Data\Entity\Video\Technical $technical
		, \SpameriTests\Data\Entity\Video\Story $story
		, \SpameriTests\Data\Entity\Video\Details $details
		, \SpameriTests\Data\Entity\Video\HighLights $highLights
		, \SpameriTests\Data\Entity\Video\Connections $connections
		, \SpameriTests\Data\Entity\Video\People $people
		, \SpameriTests\Data\Entity\Video\SeasonCollection $season = NULL
	)
	{
		$this->id = $id;
		$this->identification = $identification;
		$this->name = $name;
		$this->year = $year;
		$this->technical = $technical;
		$this->story = $story;
		$this->details = $details;
		$this->highLights = $highLights;
		$this->connections = $connections;

		if ($season === NULL) {
			$season = new \SpameriTests\Data\Entity\Video\SeasonCollection();
		}
		$this->season = $season;
		$this->people = $people;
	}


	public function entityVariables() : array
	{
		return \get_object_vars($this);
	}


	public function id() : \Spameri\Elastic\Entity\Property\ElasticIdInterface
	{
		return $this->id;
	}


	public function identification() : \SpameriTests\Data\Entity\Video\Identification
	{
		return $this->identification;
	}


	public function name() : \SpameriTests\Data\Entity\Property\Name
	{
		return $this->name;
	}


	public function rename(\SpameriTests\Data\Entity\Property\Name $name) : void
	{
		$this->name = $name;
	}


	public function year() : \SpameriTests\Data\Entity\Property\Year
	{
		return $this->year;
	}


	public function setYear(\SpameriTests\Data\Entity\Property\Year $year) : void
	{
		$this->year = $year;
	}


	public function technical() : \SpameriTests\Data\Entity\Video\Technical
	{
		return $this->technical;
	}


	public function setTechnicalFromImdb(\SpameriTests\Data\Entity\Video\Technical $technical) : void
	{
		$this->technical = $technical;
	}


	public function story() : \SpameriTests\Data\Entity\Video\Story
	{
		return $this->story;
	}


	public function details() : \SpameriTests\Data\Entity\Video\Details
	{
		return $this->details;
	}


	public function highLights() : \SpameriTests\Data\Entity\Video\HighLights
	{
		return $this->highLights;
	}


	public function connections() : \SpameriTests\Data\Entity\Video\Connections
	{
		return $this->connections;
	}


	public function season() : \SpameriTests\Data\Entity\Video\SeasonCollection
	{
		return $this->season;
	}


	public function people() : \SpameriTests\Data\Entity\Video\People
	{
		return $this->people;
	}
}
