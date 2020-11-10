<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity\Video;


class HighLights implements \Spameri\Elastic\Entity\EntityInterface
{

	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Video\HighLights\TriviaCollection
	 */
	private $trivia;
	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Video\HighLights\GoofCollection
	 */
	private $goof;
	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Video\HighLights\CrazyCreditCollection
	 */
	private $crazyCredit;
	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Video\HighLights\QuoteCollection
	 */
	private $quote;
	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Video\HighLights\LocationCollection
	 */
	private $location;

	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Video\HighLights\AlternateVersionCollection
	 */
	private $alternateVersion;

	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Video\HighLights\CompanyCreditCollection
	 */
	private $companyCredit;


	public function __construct(
		\SpameriTests\Elastic\Data\Entity\Video\HighLights\TriviaCollection $trivia
		, \SpameriTests\Elastic\Data\Entity\Video\HighLights\GoofCollection $goofs
		, \SpameriTests\Elastic\Data\Entity\Video\HighLights\CrazyCreditCollection $crazyCredits
		, \SpameriTests\Elastic\Data\Entity\Video\HighLights\QuoteCollection $quotes
		, \SpameriTests\Elastic\Data\Entity\Video\HighLights\LocationCollection $locations
		, \SpameriTests\Elastic\Data\Entity\Video\HighLights\AlternateVersionCollection $alternateVersions
		, \SpameriTests\Elastic\Data\Entity\Video\HighLights\CompanyCreditCollection $companyCredit
	)
	{
		$this->trivia = $trivia;
		$this->goof = $goofs;
		$this->crazyCredit = $crazyCredits;
		$this->quote = $quotes;
		$this->location = $locations;
		$this->alternateVersion = $alternateVersions;
		$this->companyCredit = $companyCredit;
	}


	public function entityVariables(): array
	{
		return \get_object_vars($this);
	}


	public function key(): string
	{
		return (string) \spl_object_id($this);
	}


	public function trivia(): \SpameriTests\Elastic\Data\Entity\Video\HighLights\TriviaCollection
	{
		return $this->trivia;
	}


	public function goof(): \SpameriTests\Elastic\Data\Entity\Video\HighLights\GoofCollection
	{
		return $this->goof;
	}


	public function crazyCredit(): \SpameriTests\Elastic\Data\Entity\Video\HighLights\CrazyCreditCollection
	{
		return $this->crazyCredit;
	}


	public function quote(): \SpameriTests\Elastic\Data\Entity\Video\HighLights\QuoteCollection
	{
		return $this->quote;
	}


	public function location(): \SpameriTests\Elastic\Data\Entity\Video\HighLights\LocationCollection
	{
		return $this->location;
	}


	public function alternateVersion(): \SpameriTests\Elastic\Data\Entity\Video\HighLights\AlternateVersionCollection
	{
		return $this->alternateVersion;
	}


	public function companyCredit(): \SpameriTests\Elastic\Data\Entity\Video\HighLights\CompanyCreditCollection
	{
		return $this->companyCredit;
	}

}
