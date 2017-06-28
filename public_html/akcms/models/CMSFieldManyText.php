<?php

/**
 * Field text
 */
class CMSFieldManyText extends CMSFieldAbstract
{
    public static function quote(pgdb $sql,$v){ return $sql->a_t($v); }

}