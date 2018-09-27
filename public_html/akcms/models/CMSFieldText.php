<?php

/**
 * Field text
 */
class CMSFieldText extends CMSFieldAbstract
{
    protected static $typeDb = 'TEXT';
    public static function quote(pgdb $sql,$v){ return $sql->t($v); }
    public static function quoteArray(pgdb $sql,array $a) { return self::_quoteArray($sql,$a,__CLASS__); }

}