<?php

/**
 * Прототип поля
 *
 */
abstract class CMSFieldAbstract
{
    abstract static public function quote(pgdb $sql,$v);
    //abstract static public function quoteOne(pgdb $sql,$v);
    //abstract static public function quoteMany(pgdb $sql,array $v);

    //abstract public function jsEditor();

}