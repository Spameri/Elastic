<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity\Video;

class Technical implements \Spameri\Elastic\Entity\EntityInterface
{

	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Video\Technical\Camera|NULL
	 */
	private ?\SpameriTests\Elastic\Data\Entity\Video\Technical\Camera $camera = null;

	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Video\Technical\Runtime|NULL
	 */
	private ?\SpameriTests\Elastic\Data\Entity\Video\Technical\Runtime $runtime = null;

	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Video\Technical\Color|NULL
	 */
	private ?\SpameriTests\Elastic\Data\Entity\Video\Technical\Color $color = null;

	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Video\Technical\Ratio|NULL
	 */
	private ?\SpameriTests\Elastic\Data\Entity\Video\Technical\Ratio $ratio = null;

	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Video\Technical\Laboratory|NULL
	 */
	private ?\SpameriTests\Elastic\Data\Entity\Video\Technical\Laboratory $laboratory = null;

	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Video\Technical\FilmLength|NULL
	 */
	private ?\SpameriTests\Elastic\Data\Entity\Video\Technical\FilmLength $filmLength = null;

	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Video\Technical\NegativeFormat|NULL
	 */
	private ?\SpameriTests\Elastic\Data\Entity\Video\Technical\NegativeFormat $negativeFormat = null;

	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Video\Technical\CineProcess|NULL
	 */
	private ?\SpameriTests\Elastic\Data\Entity\Video\Technical\CineProcess $cineProcess = null;

	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Video\Technical\Printed|NULL
	 */
	private ?\SpameriTests\Elastic\Data\Entity\Video\Technical\Printed $printed = null;


	public function __construct(
		\SpameriTests\Elastic\Data\Entity\Video\Technical\Camera|null $camera = null,
		\SpameriTests\Elastic\Data\Entity\Video\Technical\Runtime|null $runtime = null,
		\SpameriTests\Elastic\Data\Entity\Video\Technical\Color|null $color = null,
		\SpameriTests\Elastic\Data\Entity\Video\Technical\Ratio|null $ratio = null,
		\SpameriTests\Elastic\Data\Entity\Video\Technical\Laboratory|null $laboratory = null,
		\SpameriTests\Elastic\Data\Entity\Video\Technical\FilmLength|null $filmLength = null,
		\SpameriTests\Elastic\Data\Entity\Video\Technical\NegativeFormat|null $negativeFormat = null,
		\SpameriTests\Elastic\Data\Entity\Video\Technical\CineProcess|null $cineProcess = null,
		\SpameriTests\Elastic\Data\Entity\Video\Technical\Printed|null $printed = null,
	)
	{
		$this->camera = $camera;
		$this->runtime = $runtime;
		$this->color = $color;
		$this->ratio = $ratio;
		$this->laboratory = $laboratory;
		$this->filmLength = $filmLength;
		$this->negativeFormat = $negativeFormat;
		$this->cineProcess = $cineProcess;
		$this->printed = $printed;
	}


	/**
	 * @return array<string, mixed>
	 */
	public function entityVariables(): array
	{
		return \get_object_vars($this);
	}


	/**
	 * @throws \Exception
	 */
	public function key(): string
	{
		return (string) \spl_object_id($this);
	}


	public function camera(): \SpameriTests\Elastic\Data\Entity\Video\Technical\Camera|null
	{
		return $this->camera;
	}


	public function runtime(): \SpameriTests\Elastic\Data\Entity\Video\Technical\Runtime|null
	{
		return $this->runtime;
	}


	public function color(): \SpameriTests\Elastic\Data\Entity\Video\Technical\Color|null
	{
		return $this->color;
	}


	public function ratio(): \SpameriTests\Elastic\Data\Entity\Video\Technical\Ratio|null
	{
		return $this->ratio;
	}


	public function laboratory(): \SpameriTests\Elastic\Data\Entity\Video\Technical\Laboratory|null
	{
		return $this->laboratory;
	}


	public function filmLength(): \SpameriTests\Elastic\Data\Entity\Video\Technical\FilmLength|null
	{
		return $this->filmLength;
	}


	public function negativeFormat(): \SpameriTests\Elastic\Data\Entity\Video\Technical\NegativeFormat|null
	{
		return $this->negativeFormat;
	}


	public function cineProcess(): \SpameriTests\Elastic\Data\Entity\Video\Technical\CineProcess|null
	{
		return $this->cineProcess;
	}


	public function printed(): \SpameriTests\Elastic\Data\Entity\Video\Technical\Printed|null
	{
		return $this->printed;
	}

}
