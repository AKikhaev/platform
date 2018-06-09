<?php

/**
 * cms_gb_tags_gb
 * Теги записей гостевой
 *
 * @property int     gbId Гостевая запись
 * @property int     gbtId Тег
 */
class modelCmsGbTagsGb extends cmsModelAbstact
{
    use SQLpgModelAdapter;
    /* static fields list */
    public static $_gbId   = 'gb_id';
    public static $_gbtId  = 'gbt_id';

    public static $tableName = 'cms_gb_tags_gb';
    protected $schemaName = 'knpzken';

    /* струкура таблицы */
    protected $struct = array (
    'table' => 'cms_gb_tags_gb',
    'schema' => 'knpzken',
    'comment' => 'Теги записей гостевой',
    'fields' => array(
      'gbId' => array(
        'COLUMN_NAME' => 'gb_id',
        'NULLABLE' => false,
        'COMMENT' => 'Гостевая запись',
        'RELATE_TO' => 'cms_gb',
        'FIELD_CLASS' => 'FieldInt',
      ),
      'gbtId' => array(
        'COLUMN_NAME' => 'gbt_id',
        'NULLABLE' => false,
        'COMMENT' => 'Тег',
        'RELATE_TO' => 'cms_gb_tags',
        'FIELD_CLASS' => 'FieldInt',
      ),
    ),
    'fieldsDB' => array(
      'gb_id' => 'gbId',
      'gbt_id' => 'gbtId',
    ),
    'primary' => '',
    'primaryDB' => '',
    'tables' => array(
      0 => array(
        'table' => 'cms_gb_tags_gb',
        'primary' => '',
        'primaryDB' => '',
        'schema' => 'knpzken',
        'prefix' => '',
      ),
    ),
  );

    /*** customer extensions ***/

}
