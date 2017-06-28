<?php

/**
 * Field text
 */
class CMSFieldText extends CMSFieldAbstract
{
    public static function quote(pgdb $sql,$v){ return $sql->t($v); }

}