<?php

/**
 * Field text
 */
class CMSFieldDate extends CMSFieldAbstract
{
    protected static $typeDb = 'DATE';
    public static function dbType() { return self::$typeDb; }
    public static function quote(pgdb $sql,$v){
        $dt = $v;
        if (gettype($v)=='object' && get_class($v)=='DateTime') $dt = $v->format('Y-m-d O');
        elseif (gettype($v)=='string') $v = strtotime($v);
        elseif (is_int($v)) $dt = date('Y-m-d O',$v);
        return $v===null?'null':$sql->t($dt);
    }
    public static function quoteArray(pgdb $sql,array $a) { return self::_quoteArray($sql,$a,__CLASS__); }

}