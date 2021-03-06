<?php

/**
 * Field int
 */
class CMSFieldBool extends CMSFieldAbstract
{
    protected static $typeDb = 'BOOLEAN';
    public static function dbType() { return self::$typeDb; }
    public static function quote(pgdb $sql,$v){ return $sql->b($v); }
    public static function quoteArray(pgdb $sql,array $a) { return self::_quoteArray($sql,$a,__CLASS__); }
}