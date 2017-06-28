<?php

/**
 * Field text
 */
class CMSFieldManyDouble extends CMSFieldAbstract
{
    public static function quote(pgdb $sql,$v){ return $sql->a_f($v); }

}