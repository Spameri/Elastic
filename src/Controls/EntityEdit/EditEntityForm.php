<?php declare(strict_types = 1);

namespace Spameri\Elastic\Controls\EntityEdit;


class EditEntityForm extends \Spameri\CoreModule\BaseForm\BaseForm
{
	public const BANNED_PROPERTIES = [
		'id',
		'created',
		'edited',
		'editedBy',
		'createdBy',
	];
	public const HIDDEN_PROPERTIES = [
		'id'
	];
	public const INTEGER = 'integer';
	public const DATE = 'date';

	/**
	 * @var array
	 */
	protected $entity;

	/**
	 * @var array
	 */
	protected $entitySettings;


	public function setUpForm()
	{
		/**
		 * @var $IFormRenderer \Nette\Forms\Rendering\DefaultFormRenderer
		 */
		$IFormRenderer = $this->getRenderer();
		$IFormRenderer->wrappers['controls']['container'] = 'table class=table';

		$this->addFormField(NULL, $this->getEntitySettings()['properties']);

		$this->setDefaults($this->getEntity());

		$this->addSubmit('Save', 'Save')
			->setAttribute('class', 'btn btn-success');
	}


	/**
	 * @param string $key
	 * @param string|int $type
	 */
	public function addFormComponent($key, $type)
	{
		if (in_array($key, EditEntityForm::BANNED_PROPERTIES, TRUE)) {
			if (in_array($key, EditEntityForm::HIDDEN_PROPERTIES, TRUE)) {
				$this->addHidden($key);
			}
			return;
		}

		switch ($type) {
			case EditEntityForm::INTEGER:
				$this->addInteger($key, $key);
				break;

			case EditEntityForm::DATE:
				$this->addText($key, $key)
					->setType('date');
				break;

			default:
				$this->addText($key, $key);
				break;
		}
	}


	/**
	 * @param string $key
	 * @param array|string|int $field
	 */
	public function addFormField($key, $field)
	{
		if (is_array($field)) {
			foreach ($field as $subKey => $value) {
				if (is_array($value)) {
					$this->addFormField($subKey, $value);

				} else {
					$this->addFormComponent($key, $value);
				}
			}
		} else {
			$this->addFormComponent($key, $field);
		}
	}


	public function getEntity() : array
	{
		return $this->entity;
	}


	public function setEntity(array $entity)
	{
		$this->entity = $entity;
	}


	public function getEntitySettings() : array
	{
		return $this->entitySettings;
	}


	public function setEntitySettings(array $entitySettings)
	{
		$this->entitySettings = $entitySettings;
	}

}
