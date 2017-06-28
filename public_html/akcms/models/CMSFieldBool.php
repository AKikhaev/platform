<?php

/**
 * Field int
 */
class CMSFieldBool extends CMSFieldAbstract
{
    public static function quote(pgdb $sql,$v){ return $sql->b($v); }

}