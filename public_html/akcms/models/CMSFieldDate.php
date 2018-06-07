<?php

/**
 * Field text
 */
class CMSFieldDate extends CMSFieldAbstract
{
    public static function quote(pgdb $sql,$v){
        $dt = $v;
        if (gettype($v)=='object' && get_class($v)=='DateTime') $dt = $v->format('Y-m-d O');
            if (is_int($v)) $dt = date('Y-m-d O',$v);
        return $sql->t($dt);
    }

}