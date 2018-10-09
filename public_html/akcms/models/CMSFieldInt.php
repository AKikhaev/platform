<?php

/**
 * Field int
 */
class CMSFieldInt extends CMSFieldAbstract
{
    protected static $typeDb = 'BIGINT';
    public static function dbType() { return self::$typeDb; }
    public static function quote(pgdb $sql,$v){ return $sql->d($v); }
    public static function quoteArray(pgdb $sql,array $a) { return self::_quoteArray($sql,$a,__CLASS__); }

}