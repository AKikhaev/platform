<?php

/**
 * Field text
 */
class CMSFieldDateTime extends CMSFieldAbstract
{
    public static function quote(pgdb $sql,$v){
        $dt = $v;
        if (gettype($v)=='object' && get_class($v)=='DateTime') $dt = $v->format('Y-m-d H:i:s.uO');
            if (is_int($v)) $dt = date('Y-m-d H:i:s.uO',$v);
        return $sql->t($dt);
    }

}