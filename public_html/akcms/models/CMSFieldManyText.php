<?php

/**
 * Field text
 */
class CMSFieldManyText extends CMSFieldAbstract
{
    protected static $typeDb = 'TEXT';
    public static function quote(pgdb $sql,$v){ return $sql->a_t($v); }
    public static function quoteArray(pgdb $sql,array $a) { return self::_quoteArray($sql,$a,__CLASS__); }

}