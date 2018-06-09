<?php

/**
 * cms_gb_tags
 * Теги гостевой книги
 *
 * @property int     gbtId ИД
 * @property string  gbtText Тег
 */
class modelCmsGbTags extends cmsModelAbstact
{
    use SQLpgModelAdapter;
    /* static fields list */
    public static $_gbtId    = 'gbt_id';
    public static $_gbtText  = 'gbt_text';

    public static $tableName = 'cms_gb_tags';
    protected $schemaName = 'knpzken';

    /* струкура таблицы */
    protected $struct = array (
    'table' => 'cms_gb_tags',
    'schema' => 'knpzken',
    'comment' => 'Теги гостевой книги',
    'fields' => array(
      'gbtId' => array(
        'COLUMN_NAME' => 'gbt_id',
        'NULLABLE' => false,
        'COMMENT' => 'ИД',
        'FIELD_CLASS' => 'FieldInt',
      ),
      'gbtText' => array(
        'COLUMN_NAME' => 'gbt_text',
        'NULLABLE' => false,
        'COMMENT' => 'Тег',
        'FIELD_CLASS' => 'FieldText',
      ),
    ),
    'fieldsDB' => array(
      'gbt_id' => 'gbtId',
      'gbt_text' => 'gbtText',
    ),
    'primary' => 'gbtId',
    'primaryDB' => 'gbt_id',
    'tables' => array(
      0 => array(
        'table' => 'cms_gb_tags',
        'primary' => 'gbtId',
        'primaryDB' => 'gbt_id',
        'schema' => 'knpzken',
        'prefix' => '',
      ),
    ),
  );

    /*** customer extensions ***/

}
