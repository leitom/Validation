<?php namespace Laracasts\Validation;

use Laracasts\Validation\FactoryInterface as ValidatorFactory;
use Laracasts\Validation\ValidatorInterface as ValidatorInstance;

abstract class FormValidator {

	/**
	 * @var ValidatorFactory
	 */
	protected $validator;

	/**
	 * @var ValidatorInstance
	 */
	protected $validation;

	/**
	 * @var array
	 */
	protected $messages = [];

	/**
	 * @param ValidatorFactory $validator
	 */
	function __construct(ValidatorFactory $validator)
	{
		$this->validator = $validator;
	}

	/**
	 * Validate the form data
	 *
	 * @param  mixed $formData
     * @param  array $mappings
	 * @return mixed
	 * @throws FormValidationException
	 */
	public function validate($formData, $mappings = [])
	{
        if ( ! empty($mappings))
        {
            $this->replaceRulePlaceholdersWith($mappings);
        }

		$formData = $this->normalizeFormData($formData);

		$this->validation = $this->validator->make(
			$formData,
			$this->getValidationRules(),
			$this->getValidationMessages()
		);

		if ($this->validation->fails())
		{
			throw new FormValidationException('Validation failed', $this->getValidationErrors());
		}

		return true;
	}

	/**
	 * @return array
	 */
	public function getValidationRules()
	{
		return $this->rules;
	}

	/**
	 * @return mixed
	 */
	public function getValidationErrors()
	{
		return $this->validation->errors();
	}

	/**
	 * @return mixed
	 */
	public function getValidationMessages()
	{
		return $this->messages;
	}

	/**
	 * Normalize the provided data to an array.
	 *
	 * @param  mixed $formData
	 * @return array
	 */
	protected function normalizeFormData($formData)
	{
		// If an object was provided, maybe the user
		// is giving us something like a DTO.
		// In that case, we'll grab the public properties
		// off of it, and use that.
		if (is_object($formData))
		{
        	return get_object_vars($formData);
		}

		// Otherwise, we'll just stick with what they provided.
		return $formData;
	}

    /**
     * Go trough all the mappings
     * and inject the mappings into the rules.
     *
     * @param  array $mappings
     * @return void
     */
    protected function replaceRulePlaceholdersWith(array $mappings)
    {
        $rules = $this->getValidationRules();

        foreach ($mappings as $search => $value)
        {
            $this->injectMappingIntoRules($rules, $search, $value);
        }
    }

    /**
     * Inject a mapping into all defined rules.
     *
     * @param  array  $rules
     * @param  string $search
     * @param  mixed  $value
     * @return void
     */
    protected function injectMappingIntoRules(array $rules, $search, $value)
    {
        foreach ($rules as $field => $rule)
        {
            $this->rules[$field] = str_replace('{'.$search.'}', $value, $rule);
        }
    }
}
