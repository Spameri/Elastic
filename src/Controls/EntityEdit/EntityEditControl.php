<?php declare(strict_types = 1);

namespace Spameri\Elastic\Controls\EntityEdit;


class EntityEditControl extends \Spameri\Elastic\Controls\BaseControl
{

	/**
	 * @var \Spameri\Elastic\Entity\IEntity
	 */
	public $entity;

	/**
	 * @var \Spameri\Elastic\Controls\EntityEdit\EntitySettingsProvider
	 */
	protected $entitySettingsProvider;

	/**
	 * @var \Spameri\Elastic\Model\ServiceLocator
	 */
	private $serviceLocator;


	public function __construct(
		\Spameri\Elastic\Entity\IElasticEntity $entity
		, \Spameri\Elastic\Controls\EntityEdit\EntitySettingsProvider $entitySettingsProvider
		, \Spameri\Elastic\Model\ServiceLocator $serviceLocator
	)
	{
		parent::__construct();
		$this->entity = $entity;
		$this->entitySettingsProvider = $entitySettingsProvider;
		$this->serviceLocator = $serviceLocator;
	}


	public function render()
	{
		$this->getTemplate()->setFile(__DIR__ . '/EntityEditControl.latte');

		$this->getTemplate()->render();
	}


	public function createComponentEditEntityForm()
	{
		$form = new EditEntityForm();

		$end = explode('\\', get_class($this->getEntity()));
		$form->setEntitySettings($this->entitySettingsProvider->getEntitySettings(end($end)));

		$form->setEntity($this->getEntity()->toArray());

		$form->setUpForm();

		$form->onSuccess[] = [$this, 'insert'];

		return $form;
	}


	public function insert(EditEntityForm $form)
	{
		$entityName = get_class($this->getEntity());
		$entity = new $entityName($form->getValues(TRUE));

		$id = $this->serviceLocator->locate($entity)->insert($entity);

		$action = $this->getPresenter()->getAction(TRUE);

		$this->getPresenter()->redirect($action, $id);
	}


	public function getEntity() : \Spameri\Elastic\Entity\IElasticEntity
	{
		return $this->entity;
	}


	public function setEntity(
		\Spameri\Elastic\Entity\IElasticEntity $entity
	) : void
	{
		$this->entity = $entity;
	}
}
