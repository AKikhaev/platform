<?php

/**
 * {#tableName#}
 * {#tablecomment#}
 *
{#properties#}
 */
class cmsModelTemplate extends cmsModelAbstract
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
