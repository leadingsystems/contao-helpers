<?php

namespace LeadingSystems\Helpers;

use Symfony\Component\HttpFoundation\Request;

class FlexWidget
{
	/*
	 * The following configuration array is just an example. It will be overwritten completely on instantiation.
	 */
	protected $arr_configuration = array(
		// mandatory
		'str_uniqueName' => '',

		// optional
		'bln_multipleWidgetsWithSameNameAllowed' => false,

		// optional
		'str_template' => '',

		// optional
		'arr_validationFunctions' => array(
			array(
				'str_className' => 'ExampleValidatorClassName',
				'str_methodName' => 'exampleValidatorMethodName'
			)
		),

		// optional
		'str_label' => '',

		// optional (allowed values either "get" or "post". If no value is given, both methods will be accepted)
		'str_allowedRequestMethod' => '',

		// optional
		'arr_moreData' => array(
//			'class' => 'some-class',
//			'id' => 'some-id',
//			...
		),

		// optional
		'int_minLength' => 0,

		// optional
		'int_maxLength' => 0,

		// optional
		'var_value' => null
	);

	protected $str_name = '';
	protected $str_template = 'ls_flexWidget_defaultText';
	protected $arr_validationFunctions = array();
	protected $str_label = '';
	protected $arr_moreData = array();
	protected $str_allowedRequestMethod = '';
	protected $int_minLength = 0;
	protected $int_maxLength = 0;
	protected $bln_multipleWidgetsWithSameNameAllowed = false;

	protected $bln_receivedData = false;

	public $var_value = null;
	public $arr_errors = array();
	public $str_output = '';
	public $bln_hasErrors = false;

	/**
	 * FlexWidget constructor.
	 * @param $arr_configuration array
	 * @throws \Exception
	 */
	public function __construct($arr_configuration)
	{
		if (!isset($GLOBALS['leadingSystems']['flexWidget']['arr_registeredUniqueNames'])) {
			$GLOBALS['leadingSystems']['flexWidget']['arr_registeredUniqueNames'] = array();
		}

		if (!is_array($arr_configuration)) {
			throw new \Exception('A configuration array is required');
		}

		$this->arr_configuration = $arr_configuration;

		$this->setUniqueName();
		$this->setTemplate();
		$this->setValidationFunctions();
		$this->setLabel();
		$this->setAllowedRequestMethod();
		$this->setMoreData();
		$this->setMinLength();
		$this->setMaxLength();
		$this->setValue();

		$this->getSubmittedValue();
		$this->validate();
		$this->parse();
	}

	protected function setAllowedRequestMethod()
	{
		if (!isset($this->arr_configuration['str_allowedRequestMethod'])) {
			/*
			 * It is not necessary to explicitly set an allowed request method
			 */
			return;
		}

		if (!in_array($this->arr_configuration['str_allowedRequestMethod'], array('', 'post', 'get'))) {
			throw new \Exception('Only the values "post" and "get" and the empty string are allowed');
		}

		$this->str_allowedRequestMethod = $this->arr_configuration['str_allowedRequestMethod'];
	}

	protected function setValue()
	{
		if (!isset($this->arr_configuration['var_value'])) {
			/*
			 * It is not necessary for a FlexWidget to start with a given value
			 */
			return;
		}

		$this->var_value = $this->arr_configuration['var_value'];
	}

	protected function setUniqueName()
	{
		if (!isset($this->arr_configuration['str_uniqueName']) || !$this->arr_configuration['str_uniqueName']) {
			throw new \Exception('A unique name is mandatory in the configuration array');
		}

		if (isset($this->arr_configuration['bln_multipleWidgetsWithSameNameAllowed'])) {
			$this->bln_multipleWidgetsWithSameNameAllowed = (bool) $this->arr_configuration['bln_multipleWidgetsWithSameNameAllowed'];
		}

		if (!$this->bln_multipleWidgetsWithSameNameAllowed) {
			if (in_array($this->arr_configuration['str_uniqueName'], $GLOBALS['leadingSystems']['flexWidget']['arr_registeredUniqueNames'])) {
				throw new \Exception('A FlexWidget with the name "' . $this->arr_configuration['str_uniqueName'] . '" is already registered.');
			}

			$GLOBALS['leadingSystems']['flexWidget']['arr_registeredUniqueNames'][] = $this->arr_configuration['str_uniqueName'];
		}

		$this->str_name = $this->arr_configuration['str_uniqueName'];
	}

	protected function setTemplate()
	{
		if (!isset($this->arr_configuration['str_template']) || !$this->arr_configuration['str_template']) {
			/*
			 * If no template name is given, we keep the default template
			 */
			return;
		}

		$this->str_template = $this->arr_configuration['str_template'];
	}

	protected function setValidationFunctions()
	{
		if (!isset($this->arr_configuration['arr_validationFunctions'])) {
			/*
			 * It is not necessary for a FlexWidget to have validation functions
			 */
			return;
		}

		if (!is_array($this->arr_configuration['arr_validationFunctions'])) {
			throw new \Exception('the validation functions must be passed as an multi dimensional array');
		}

		foreach ($this->arr_configuration['arr_validationFunctions'] as $arr_validationFunction) {
			$this->addValidationFunction($arr_validationFunction);
		}
	}

	protected function addValidationFunction($arr_validationFunction)
	{
		if (
			!is_array($arr_validationFunction)
			|| !isset($arr_validationFunction['str_className'])
			|| !$arr_validationFunction['str_className']
			|| !isset($arr_validationFunction['str_methodName'])
			|| !$arr_validationFunction['str_methodName']
		) {
			dump('$arr_validationFunction', $arr_validationFunction);
			throw new \Exception('Each validation function must be given as an array holding the class name (key "str_className") and the method name ("str_methodName")');
		}

		$this->arr_validationFunctions[$arr_validationFunction['str_className']] = $arr_validationFunction['str_methodName'];
	}

	protected function setLabel()
	{
		$this->str_label = isset($this->arr_configuration['str_label']) ? $this->arr_configuration['str_label'] : $this->str_label;
	}

	protected function setMinLength()
	{
		$this->int_minLength = isset($this->arr_configuration['int_minLength']) ? $this->arr_configuration['int_minLength'] : $this->int_minLength;
	}

	protected function setMaxLength()
	{
		$this->int_maxLength = isset($this->arr_configuration['int_maxLength']) ? $this->arr_configuration['int_maxLength'] : $this->int_maxLength;
	}

	protected function setMoreData()
	{
		if (!isset($this->arr_configuration['arr_moreData'])) {
			return;
		}

		if (!is_array($this->arr_configuration['arr_moreData'])) {
			throw new \Exception('An array is expected');
		}

		$this->arr_moreData = $this->arr_configuration['arr_moreData'];
	}

    protected function getSubmittedValue()
    {
        if (
            (
                !$this->str_allowedRequestMethod
                || $this->str_allowedRequestMethod === 'post'
            )
            && \Input::post($this->str_name) !== null
        ) {
            /*
             * Get the value from POST data if there's either no restriction regarding the allowed request methods
             * or the POST method is set explicitly
             */
            $this->var_value = \Input::post($this->str_name);
            $this->bln_receivedData = true;
            return;
        }

        if (
            (
                !$this->str_allowedRequestMethod
                || $this->str_allowedRequestMethod === 'get'
            )
            && \Input::get($this->str_name) !== null
        ) {
            /*
             * Get the value from GET data if there's either no restriction regarding the allowed request methods
             * or the GET method is set explicitly
             */
            $this->var_value = \Input::get($this->str_name);
            $this->bln_receivedData = true;
            return;
        }
    }

	protected function validate()
	{
		if ($this->bln_receivedData) {
			if (!$this->check_minLengthOkay()) {
				$this->arr_errors[] = sprintf($GLOBALS['TL_LANG']['MSC']['ls_flexWidget']['errors']['minLength'], $this->int_minLength);
			}

			if (!$this->check_maxLengthOkay()) {
				$this->arr_errors[] = sprintf($GLOBALS['TL_LANG']['MSC']['ls_flexWidget']['errors']['maxLength'], $this->int_maxLength);
			}

			if (!count($this->arr_errors)) {
				foreach ($this->arr_validationFunctions as $str_className => $str_methodName) {
					try {
						call_user_func_array(array($str_className, $str_methodName), array($this));
					} catch (\Exception $exception) {
						$this->arr_errors[] = $exception->getMessage();
					}
				}
			}
		}

		$this->bln_hasErrors = (bool)count($this->arr_errors);
	}

	protected function check_minLengthOkay() {
		if (!$this->int_minLength) {
			return true;
		}
		return !is_string($this->var_value) || strlen($this->var_value) >= $this->int_minLength;
	}

	protected function check_maxLengthOkay() {
		if (!$this->int_maxLength) {
			return true;
		}
		return !is_string($this->var_value) || strlen($this->var_value) <= $this->int_maxLength;
	}

	protected function parse()
	{
		if (!$this->str_name) {
			throw new \Exception('Flex widget cannot be parsed because no id has been set yet.');
		}

		$obj_template = new \FrontendTemplate($this->str_template);
		$obj_template->__set('str_name', $this->str_name);
		$obj_template->__set('str_label', $this->str_label);
		$obj_template->__set('var_value', $this->var_value);
		$obj_template->__set('arr_moreData', $this->arr_moreData);
		$obj_template->__set('arr_errors', $this->arr_errors);

		$this->str_output = $obj_template->parse();
	}

	public function getLabel() {
		return $this->str_label;
	}

	public function getValue() {
		return $this->var_value;
	}

	public function getOutput() {
		return $this->str_output;
	}

	public function getMinLength() {
		return $this->int_minLength;
	}

	public function getMaxLength() {
		return $this->int_maxLength;
	}
}