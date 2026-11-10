<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity\Video;

class HighLights implements \Spameri\Elastic\Entity\EntityInterface
{

	/**
	 * @param \SpameriTests\Elastic\Data\Entity\Video\HighLights\TriviaCollection<\SpameriTests\Elastic\Data\Entity\Video\HighLights\Trivia> $trivia
	 * @param \SpameriTests\Elastic\Data\Entity\Video\HighLights\GoofCollection<\SpameriTests\Elastic\Data\Entity\Video\HighLights\Goof> $goofs
	 * @param \SpameriTests\Elastic\Data\Entity\Video\HighLights\CrazyCreditCollection<\SpameriTests\Elastic\Data\Entity\Video\HighLights\CrazyCredit> $crazyCredits
	 * @param \SpameriTests\Elastic\Data\Entity\Video\HighLights\QuoteCollection<\SpameriTests\Elastic\Data\Entity\Video\HighLights\Quote> $quotes
	 * @param \SpameriTests\Elastic\Data\Entity\Video\HighLights\LocationCollection<\SpameriTests\Elastic\Data\Entity\Video\HighLights\Location> $locations
	 * @param \SpameriTests\Elastic\Data\Entity\Video\HighLights\AlternateVersionCollection<\SpameriTests\Elastic\Data\Entity\Video\HighLights\AlternateVersion> $alternateVersions
	 * @param \SpameriTests\Elastic\Data\Entity\Video\HighLights\CompanyCreditCollection<\SpameriTests\Elastic\Data\Entity\Video\HighLights\CompanyCredit> $companyCredit
	 */
	public function __construct(
		public \SpameriTests\Elastic\Data\Entity\Video\HighLights\TriviaCollection $trivia,
		public \SpameriTests\Elastic\Data\Entity\Video\HighLights\GoofCollection $goofs,
		public \SpameriTests\Elastic\Data\Entity\Video\HighLights\CrazyCreditCollection $crazyCredits,
		public \SpameriTests\Elastic\Data\Entity\Video\HighLights\QuoteCollection $quotes,
		public \SpameriTests\Elastic\Data\Entity\Video\HighLights\LocationCollection $locations,
		public \SpameriTests\Elastic\Data\Entity\Video\HighLights\AlternateVersionCollection $alternateVersions,
		public \SpameriTests\Elastic\Data\Entity\Video\HighLights\CompanyCreditCollection $companyCredit,
	)
	{
	}


	public function entityVariables(): array
	{
		return \get_object_vars($this);
	}


	public function key(): string
	{
		return (string) \spl_object_id($this);
	}

}
