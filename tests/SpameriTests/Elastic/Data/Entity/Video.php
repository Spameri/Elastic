<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity;

class Video implements \Spameri\Elastic\Entity\ElasticEntityInterface
{

	/**
	 * @var \Spameri\Elastic\Entity\Property\ElasticIdInterface
	 */
	public $id;

	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Video\Identification
	 */
	private $identification;

	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Property\Name
	 */
	private $name;

	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Property\Year
	 */
	private $year;

	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Video\Technical
	 */
	private $technical;

	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Video\Story
	 */
	private $story;

	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Video\Details
	 */
	private $details;

	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Video\HighLights
	 */
	private $highLights;

	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Video\Connections
	 */
	private $connections;

	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Video\SeasonCollection
	 */
	private $season;

	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Video\People
	 */
	private $people;


	public function __construct(
		\Spameri\Elastic\Entity\Property\ElasticIdInterface $id,
		\SpameriTests\Elastic\Data\Entity\Video\Identification $identification,
		\SpameriTests\Elastic\Data\Entity\Property\Name $name,
		\SpameriTests\Elastic\Data\Entity\Property\Year $year,
		\SpameriTests\Elastic\Data\Entity\Video\Technical $technical,
		\SpameriTests\Elastic\Data\Entity\Video\Story $story,
		\SpameriTests\Elastic\Data\Entity\Video\Details $details,
		\SpameriTests\Elastic\Data\Entity\Video\HighLights $highLights,
		\SpameriTests\Elastic\Data\Entity\Video\Connections $connections,
		\SpameriTests\Elastic\Data\Entity\Video\People $people,
		\SpameriTests\Elastic\Data\Entity\Video\SeasonCollection $season = NULL,
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
			$season = new \SpameriTests\Elastic\Data\Entity\Video\SeasonCollection();
		}
		$this->season = $season;
		$this->people = $people;
	}


	public function entityVariables(): array
	{
		return \get_object_vars($this);
	}


	public function id(): \Spameri\Elastic\Entity\Property\ElasticIdInterface
	{
		return $this->id;
	}


	public function identification(): \SpameriTests\Elastic\Data\Entity\Video\Identification
	{
		return $this->identification;
	}


	public function name(): \SpameriTests\Elastic\Data\Entity\Property\Name
	{
		return $this->name;
	}


	public function rename(\SpameriTests\Elastic\Data\Entity\Property\Name $name): void
	{
		$this->name = $name;
	}


	public function year(): \SpameriTests\Elastic\Data\Entity\Property\Year
	{
		return $this->year;
	}


	public function setYear(\SpameriTests\Elastic\Data\Entity\Property\Year $year): void
	{
		$this->year = $year;
	}


	public function technical(): \SpameriTests\Elastic\Data\Entity\Video\Technical
	{
		return $this->technical;
	}


	public function setTechnicalFromImdb(\SpameriTests\Elastic\Data\Entity\Video\Technical $technical): void
	{
		$this->technical = $technical;
	}


	public function story(): \SpameriTests\Elastic\Data\Entity\Video\Story
	{
		return $this->story;
	}


	public function details(): \SpameriTests\Elastic\Data\Entity\Video\Details
	{
		return $this->details;
	}


	public function highLights(): \SpameriTests\Elastic\Data\Entity\Video\HighLights
	{
		return $this->highLights;
	}


	public function connections(): \SpameriTests\Elastic\Data\Entity\Video\Connections
	{
		return $this->connections;
	}


	public function season(): \SpameriTests\Elastic\Data\Entity\Video\SeasonCollection
	{
		return $this->season;
	}


	public function people(): \SpameriTests\Elastic\Data\Entity\Video\People
	{
		return $this->people;
	}

}
