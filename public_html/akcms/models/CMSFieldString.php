<?php

/**
 * Field text
 */
class CMSFieldString extends CMSFieldAbstract
{
    public static function quote(pgdb $sql,$v){ return $sql->t($v); }

}