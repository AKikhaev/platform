<?php

class FormDataValidatorPhone{
    static function valid($value) {
        $value = preg_replace('/[^0-9]/','',$value);
        return preg_match('/^\d{10,11}$/',$value)==1;
    }
}

class FormDataValidatorCaptcha{
    static function valid($value) {
        return isset($_SESSION['securityCode']) && functs::strHasValue($_SESSION['securityCode']) && $value == $_SESSION['securityCode'];
    }
}

/** Receive form data. Validate, give access
 * Class FormData
 */
class FormData
{
    static $RequiredAny = '.';
    static $Integer = '/^\d+$/';
    static $Boolean = '/^(t|f|true|false|1|0)$/iu';
    static $Float = '/^\d+(\.+\d+)?$/iu';
    static $Phone = 'FormDataValidatorPhone';
    static $Captcha = 'FormDataValidatorCaptcha';

    private $data = [];
    private $rules = [];
    private $result = [];
    private $callbackWrong = null;

    /**
     * FormData constructor.
     * @param $data
     * $_GET or $_POST data
     * @param $rules array of rules:
     *
     * [paramName,TYPE or regular expression,required or not,ignore expression,callback wrong function]:
     *
     * 0 - paramName
     *
     * 1 - type or regular expression or callable checker closure($value, &$errorText)
     *
     * 2 - required or not
     *
     * 3 - ignore expression
     *
     * 4 - callBackWrong
     */
    public function __construct($data,$rules = [])
    {
        $this->data = $data;
        $this->rules = $rules;
    }

    /**
     * @param $name
     * @param $type
     * type or regular expression or callable checker closure($value, &$errorText)
     * @param bool $required
     * @param null $excludeExpression
     * @param null $callbackWrong
     * @return $this
     */
    public function addCheck($name, $type, $required = true, $excludeExpression = null, $callbackWrong = null){
        $this->rules[] = [$name, $type, $required, $excludeExpression, $callbackWrong];
        return $this;
    }

    public function addCallbackWrong(Closure $callbackWrong){
        $this->callbackWrong = $callbackWrong;
        return $this;
    }

    public function __get($name) { return isset($this->data[$name]) ? $this->data[$name] : null; }

    public function __isset($name) { return isset($this->data[$name]); }

    /**
     * Clear validation result. Good before re-validate
     */
    public function clearResult() { $this->result = []; }

    /** Returns true when no errors
     * @return bool
     */
    public function isValid() { return count($this->result)==0; }

    /** returns error list result data as array
     * @return array
     */
    public function errors() { return $this->result; }

    /** Validate data depends constructor rules
     *
     * 0 - paramName
     *
     * 1 - type or regular expression or callable checker closure($value, &$errorText)
     *
     * 2 - required or not
     *
     * 3 - ignore expression
     *
     * 4 - callBackWrong
     * @param bool $permissionOk
     * @return FormData
     */
    public function validateData($permissionOk = true) {
        $this->result = $this->validateRules($this->rules,$permissionOk);
        return $this;
    }

    /** Validate data depends this rules
     *
     * 0 - paramName
     *
     * 1 - type or regular expression or callable checker closure($value, &$errorText)
     *
     * 2 - required or not
     *
     * 3 - ignore expression
     *
     * 4 - callBackWrong
     * @param $rules
     * @param bool $permissionOk
     * @return array
     */
    public function validateRules($rules,$permissionOk = true) {
        $result = array();
        if (!$permissionOk) $result[] = array('f'=>'!','s'=>'!');
        foreach ($rules as $rule)
        {
            $need = isset($rule[2])?$rule[2]:true;
            $isset = isset($this->data[$rule[0]]);
            if ($need && (!$isset || $this->data[$rule[0]]=='')) //required
            {
                $result[] = array('f'=>$rule[0],'s'=>'empty');

                if (isset($rule[4]) && $rule[4] instanceof Closure) $rule[4]($rule[0],'empty');
                elseif ($this->callbackWrong !== null) {
                    call_user_func($this->callbackWrong,$rule[0],'empty');
                }
                continue;
            }
            if ($isset && !$need && $this->data[$rule[0]]=='') { //not required and empty - skip
                unset($this->data[$rule[0]]);
                continue;
            }
            elseif ($isset && !empty($rule[1]) && $rule[1] instanceof Closure) { // validate function
                $errorText = 'Wrong';
                if (!$rule[1]( $this->data[$rule[0]], $errorText)) {
                    $result[] = array('f'=>$rule[0],'s'=>$errorText);

                    if (isset($rule[4]) && $rule[4] instanceof Closure) $rule[4]($rule[0],'wrong');
                    elseif ($this->callbackWrong !== null) {
                        call_user_func($this->callbackWrong,$rule[0],'wrong');
                    }
                }
            }
            elseif ($isset && !empty($rule[1]) && !in_array(mb_substr($rule[1],0,1),['/','~','.']) && class_exists($rule[1],false)) { // validate class
                $errorText = 'Wrong';
                if (!$rule[1]::valid($this->data[$rule[0]],$errorText)) {
                    $result[] = array('f'=>$rule[0],'s'=>$errorText);

                    if (isset($rule[4]) && $rule[4] instanceof Closure) $rule[4]($rule[0],'wrong');
                    elseif ($this->callbackWrong !== null) {
                        call_user_func($this->callbackWrong,$rule[0],'wrong');
                    }
                }
            }
            elseif ($isset && !empty($rule[1]) && $rule[1] != FormData::$RequiredAny) { // validate regular expressions
                if (preg_match($rule[1],$this->data[$rule[0]])!==1) {
                    $result[] = array('f'=>$rule[0],'s'=>'wrong');

                    if (isset($rule[4]) && $rule[4] instanceof Closure) $rule[4]($rule[0],'wrong');
                    elseif ($this->callbackWrong !== null) {
                        call_user_func($this->callbackWrong,$rule[0],'wrong');
                    }
                }
                elseif (isset($rule[3]) && $rule[3] !== '' && $rule[3] !== null && preg_match($rule[3],$this->data[$rule[0]])===1) {
                    $result[] = array('f' => $rule[0], 's' => 'Wrong');

                    if (isset($rule[4]) && $rule[4] instanceof Closure) $rule[4]($rule[0],'wrong');
                    elseif ($this->callbackWrong !== null) {
                        call_user_func($this->callbackWrong,$rule[0],'wrong');
                    }
                }
            }
        }
        return $result;
    }

    /** when not true add [f:!,s:!]
     * @param $trueOrNot
     * @param string $errorResult
     */
    public function validateRights($trueOrNot, $errorResult = '!') {
        if (!$trueOrNot)
        {
            array_unshift($this->result,array('f'=>'!','s'=>$errorResult));
        }
    }

    /** when not true add [f:$field,s:error]
     * @param $trueOrNot
     * @param $field
     * @param $error
     * @return bool
     */
    public function addToErrors($trueOrNot, $field, $error) {
        if (!$trueOrNot)
        {
            $this->result[] = array('f'=>$field,'s'=>$error);
        }
        return $trueOrNot;
    }


    /** return data as array
     * @return array
     */
    public function asArray()
    {
        return $this->data;
    }
}