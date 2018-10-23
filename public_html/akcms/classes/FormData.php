<?php

/** Receive form data. Validate, give access
 * Class FormData
 */
class FormData
{
    static $REQUIRED = '.';
    static $INTEGER = '/^\\d+$/';
    static $BOOLEAN = '/^(t|f|true|false|1|0)$/iu';
    static $FLOAT = '/^\\d(.\\d)?$/iu';
    static $FLOAT_OR_EMPTY = '/^(\\d(.\\d)?|)$/iu';
    static $PHONE = '/^\\d{10,11}/u';

    private $data = [];
    private $rules = [];

    /**
     * FormData constructor.
     * @param $data
     * $_GET or $_POST data
     * @param $rules
        [varName,rules,ignores],
        ['pid' ,'/^\\d{1,}$/'],
        ['type','/^(1|2)$/']
     */
    public function __construct($data,$rules = [])
    {
        $this->data = $data;
        $this->rules = $rules;
    }

    public function __get($name)
    {
        return $this->data[$name];
    }

    public function __isset($name)
    {
        return isset($this->data[$name]);
    }

    public function validResult($permissionOk = true) {
        $result = array();
        if (!$permissionOk) $result[] = array('f'=>'!','s'=>'!');
        foreach ($this->rules as $rule)
        {
            if (!isset($this->data[$rule[0]]) or ($rule[1]=='.' and isset($this->data[$rule[0]])?$this->data[$rule[0]]=='':false))
            {
                $result[] = array('f'=>$rule[0],'s'=>'empty');
            }
            elseif (!empty($rule[1]) && $rule[1]!='.')
            {
                ##var_dump('>',$rule[0],$rule[1],$rule[2]);
                ##(isset($rule[2])) var_dump(preg_match($rule[2],$this->data[$rule[0]]));
                if (preg_match($rule[1],$this->data[$rule[0]])!==1) $result[] = array('f'=>$rule[0],'s'=>'wrong');
                elseif (isset($rule[2])?$rule[2]!=''?preg_match($rule[2],$this->data[$rule[0]])===1:false:false)
                    $result[] = array('f'=>$rule[0],'s'=>'Wrong');
            }
        }
        return $result;
    }
}