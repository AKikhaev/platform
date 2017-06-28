<?php

/**
 * Field text
 */
class CMSFieldManyInt extends CMSFieldAbstract
{
    public static function quote(pgdb $sql,$v){ return $sql->a_d($v); }

}