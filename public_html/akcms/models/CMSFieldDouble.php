<?php

/**
 * Field double
 */
class CMSFieldDouble extends CMSFieldAbstract
{
    public static function quote(pgdb $sql,$v){ return $sql->f($v); }

}