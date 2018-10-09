<?php

/**
 * Field text
 */
class CMSFieldManyInt extends CMSFieldAbstract
{
    protected static $typeDb = 'BIGINT';
    public static function dbType() { return self::$typeDb; }
    public static function quote(pgdb $sql,$v){ return $sql->a_d($v); }
    public static function quoteArray(pgdb $sql,array $a) { return self::_quoteArray($sql,$a,__CLASS__); }

}