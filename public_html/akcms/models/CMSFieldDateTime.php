<?php

/**
 * Field text
 */
class CMSFieldDateTime extends CMSFieldAbstract
{
    protected static $typeDb = 'TIMESTAMP';
    public static function quote(pgdb $sql,$v){
        $dt = $v;
        if (gettype($v)=='object' && get_class($v)=='DateTime') $dt = $v->format('Y-m-d H:i:s.uO');
        elseif (gettype($v)=='string') $v = strtotime($v);
        elseif (is_int($v)) $dt = date('Y-m-d H:i:s.uO',$v);
        return $v===null?'null':$sql->t($dt);
    }
    public static function quoteArray(pgdb $sql,array $a) { return self::_quoteArray($sql,$a,__CLASS__); }

}