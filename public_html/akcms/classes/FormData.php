<?php

class FormDataValidatorPhone{
    static function valid($value) {
        $value = preg_replace('/[^0-9]/','',$value);
        return preg_match('/^\d{10,11}$/',$value)==1;
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

    private $data = [];
    private $rules = [];
    private $result = [];

    /**
     * FormData constructor.
     * @param $data
     * $_GET or $_POST data
     * @param $rules
        [varName,TYPE or regular expression,required or not,ignore expression],
        ['pid' ,'/^\\d{1,}$/'],
        ['type','/^(1|2)$/']
     */
    public function __construct($data,$rules = [])
    {
        $this->data = $data;
        $this->rules = $rules;
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

    /** Validate data
     * @param bool $permissionOk
     * @return array
     */
    public function validateData($permissionOk = true) {
        // 0 - varName
        // 1 - regular expression
        // 2 - required or not
        // 3 - ignore expression
        $this->result = array();
        if (!$permissionOk) $this->result[] = array('f'=>'!','s'=>'!');
        foreach ($this->rules as $rule)
        {
            $need = isset($rule[2])?$rule[2]:true;
            $isset = isset($this->data[$rule[0]]);
            if ($need && (!$isset || $this->data[$rule[0]]==''))
            {
                $this->result[] = array('f'=>$rule[0],'s'=>'empty');
            }
            elseif ($isset && !empty($rule[1]) && !in_array(mb_substr($rule[1],0,1),['/','~','.'])) {
                $class = $rule[1];
                if (!$class::valid($this->data[$rule[0]])) $this->result[] = array('f'=>$rule[0],'s'=>'Wrong');
            }
            elseif ($isset && !empty($rule[1]) && $rule[1] != FormData::$RequiredAny)
            {
                if (preg_match($rule[1],$this->data[$rule[0]])!==1) $this->result[] = array('f'=>$rule[0],'s'=>'wrong');
                elseif (isset($rule[3])?$rule[3]!=''?preg_match($rule[3],$this->data[$rule[0]])===1:false:false)
                    $this->result[] = array('f'=>$rule[0],'s'=>'Wrong');
            }
        }
        return $this->result;
    }

    /** when not true add [f:!,s:!]
     * @param $trueOrNot
     * @param string $errorResult
     */
    public function validateRights($trueOrNot, $errorResult = '!') {
        if (!$trueOrNot)
        {
            $this->result[] = array('f'=>'!','s'=>$errorResult);
        }
    }

    /** when not true add [f:$field,s:error]
     * @param $trueOrNot
     * @param $field
     * @param $error
     */
    public function addToErrors($trueOrNot, $field, $error) {
        if (!$trueOrNot)
        {
            $this->result[] = array('f'=>$field,'s'=>$error);
        }
    }

}