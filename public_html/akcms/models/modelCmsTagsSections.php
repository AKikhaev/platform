<?php

/**
 * cms_tags_sections
 * Теги страниц
 *
 * @property int     tagId Тег
 * @property int     secId Страница
 */
class modelCmsTagsSections extends cmsModelAbstact
{
    use SQLpgModelAdapter;
    /* static fields list */
    public static $_tagId  = 'tag_id';
    public static $_secId  = 'sec_id';

    public static $tableName = 'cms_tags_sections';
    protected $schemaName = 'knpzken';

    /* струкура таблицы */
    protected $struct = array (
    'table' => 'cms_tags_sections',
    'schema' => 'knpzken',
    'comment' => 'Теги страниц',
    'fields' => array(
      'tagId' => array(
        'COLUMN_NAME' => 'tag_id',
        'NULLABLE' => false,
        'COMMENT' => 'Тег',
        'RELATE_TO' => 'cms_tags',
        'FIELD_CLASS' => 'FieldInt',
      ),
      'secId' => array(
        'COLUMN_NAME' => 'sec_id',
        'NULLABLE' => false,
        'COMMENT' => 'Страница',
        'RELATE_TO' => 'cms_sections',
        'FIELD_CLASS' => 'FieldInt',
      ),
    ),
    'fieldsDB' => array(
      'tag_id' => 'tagId',
      'sec_id' => 'secId',
    ),
    'primary' => '',
    'primaryDB' => '',
    'tables' => array(
      0 => array(
        'table' => 'cms_tags_sections',
        'primary' => '',
        'primaryDB' => '',
        'schema' => 'knpzken',
        'prefix' => '',
      ),
    ),
  );

    /*** customer extensions ***/

}
