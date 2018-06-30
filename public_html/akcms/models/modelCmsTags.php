<?php

/**
 * cms_tags
 * Теги
 *
 * @property int     tagId ИД
 * @property string  tagText Тег
 */
class modelCmsTags extends cmsModelAbstract
{
    use SQLpgModelAdapter;
    /* static fields list */
    public static $_tagId    = 'tag_id';
    public static $_tagText  = 'tag_text';

    public static $tableName = 'cms_tags';
    protected $schemaName = 'knpzken';

    /* струкура таблицы */
    protected $struct = array (
    'table' => 'cms_tags',
    'schema' => 'knpzken',
    'comment' => 'Теги',
    'fields' => array(
      'tagId' => array(
        'COLUMN_NAME' => 'tag_id',
        'NULLABLE' => false,
        'COMMENT' => 'ИД',
        'FIELD_CLASS' => 'FieldInt',
      ),
      'tagText' => array(
        'COLUMN_NAME' => 'tag_text',
        'NULLABLE' => false,
        'COMMENT' => 'Тег',
        'FIELD_CLASS' => 'FieldText',
      ),
    ),
    'fieldsDB' => array(
      'tag_id' => 'tagId',
      'tag_text' => 'tagText',
    ),
    'primary' => 'tagId',
    'primaryDB' => 'tag_id',
    'tables' => array(
      0 => array(
        'table' => 'cms_tags',
        'primary' => 'tagId',
        'primaryDB' => 'tag_id',
        'schema' => 'knpzken',
        'prefix' => '',
      ),
    ),
  );

    /*** customer extensions ***/

}
