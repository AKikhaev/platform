<?php

/**
 * Прототип поля
 *
 */
abstract class CMSFieldAbstract
{
    protected static $typeDb = 'ANY';

    public static abstract function quote(pgdb $sql,$v);

    //abstract static public function quoteOne(pgdb $sql,$v);

    public static abstract function quoteArray(pgdb $sql,array $a);

    protected static function _quoteArray(pgdb $sql,array $a,$CLASS){
        $data = [];
        foreach ($a as &$v) $v = $CLASS::quote($sql,$v);
        return 'ARRAY['.implode(',',$a).']::'.$CLASS::$typeDb.'[]';
    }


    //abstract public function jsEditor();

}