<?php

/**
 * Field int
 */
class CMSFieldInt extends CMSFieldAbstract
{
    public static function quote(pgdb $sql,$v){ return $sql->d($v); }

}