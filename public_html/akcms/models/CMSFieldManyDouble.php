<?php

/**
 * Field text
 */
class CMSFieldManyDouble extends CMSFieldAbstract
{
    protected static $typeDb = 'DOUBLE PRECISION';
    public static function dbType() { return self::$typeDb; }
    public static function quote(pgdb $sql,$v){ return $sql->a_f($v); }
    public static function quoteArray(pgdb $sql,array $a) { return self::_quoteArray($sql,$a,__CLASS__); }

}