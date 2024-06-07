<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity\Video;

class HighLights implements \Spameri\Elastic\Entity\EntityInterface
{

	private \SpameriTests\Elastic\Data\Entity\Video\HighLights\GoofCollection $goof;

	private \SpameriTests\Elastic\Data\Entity\Video\HighLights\CrazyCreditCollection $crazyCredit;

	private \SpameriTests\Elastic\Data\Entity\Video\HighLights\QuoteCollection $quote;

	private \SpameriTests\Elastic\Data\Entity\Video\HighLights\LocationCollection $location;

	private \SpameriTests\Elastic\Data\Entity\Video\HighLights\AlternateVersionCollection $alternateVersion;

	public function __construct(
		private \SpameriTests\Elastic\Data\Entity\Video\HighLights\TriviaCollection $trivia,
		\SpameriTests\Elastic\Data\Entity\Video\HighLights\GoofCollection $goofs,
		\SpameriTests\Elastic\Data\Entity\Video\HighLights\CrazyCreditCollection $crazyCredits,
		\SpameriTests\Elastic\Data\Entity\Video\HighLights\QuoteCollection $quotes,
		\SpameriTests\Elastic\Data\Entity\Video\HighLights\LocationCollection $locations,
		\SpameriTests\Elastic\Data\Entity\Video\HighLights\AlternateVersionCollection $alternateVersions,
		private \SpameriTests\Elastic\Data\Entity\Video\HighLights\CompanyCreditCollection $companyCredit,
	)
	{
		$this->goof = $goofs;
		$this->crazyCredit = $crazyCredits;
		$this->quote = $quotes;
		$this->location = $locations;
		$this->alternateVersion = $alternateVersions;
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
