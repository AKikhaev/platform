<?php

/**
 * {#tableName#}
 * {#tablecomment#}
 *
{#properties#}
 */
class modelTemplate extends modelAbstact
{
    use SQLpgModelAdapter;
    /* static fields list */
    //{#staticfields#}

    public static $tableName = '{#tableName#}';
    protected $schemaName = '{#schemaName#}';

    /* струкура таблицы */
    protected $struct = array();

    /*** customer extensions ***/

}
