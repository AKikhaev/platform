<?php

/**
 * Field double
 */
class CMSFieldDouble extends CMSFieldAbstract
{
    protected static $typeDb = 'DOUBLE PRECISION';
    public static function quote(pgdb $sql,$v){ return $sql->f($v); }
    public static function quoteArray(pgdb $sql,array $a) { return self::_quoteArray($sql,$a,__CLASS__); }

}