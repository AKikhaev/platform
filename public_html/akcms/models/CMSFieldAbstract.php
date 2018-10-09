<?php

/**
 * Прототип поля
 *
 */
abstract class CMSFieldAbstract
{
    protected static $typeDb = 'ANY';

    /** return db type
     * @return string
     */
    public static abstract function dbType();

    /** quote singe value into sql string
     * @param pgdb $sql
     * @param $v
     * @return mixed
     */
    public static abstract function quote(pgdb $sql,$v);

    /** pack and quote array into sql string
     * @param pgdb $sql
     * @param array $a
     * @return string
     */
    public static abstract function quoteArray(pgdb $sql,array $a);
    protected static function _quoteArray(pgdb $sql,array $a,$CLASS){
        $data = [];
        foreach ($a as &$v) $v = $CLASS::quote($sql,$v);
        return 'ARRAY['.implode(',',$a).']::'.$CLASS::$typeDb.'[]';
    }

    //abstract public function jsEditor();

}