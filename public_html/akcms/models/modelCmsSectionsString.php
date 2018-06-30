<?php

/**
 * cms_sections_string
 * Текстовые блоки страниц
 *
 * @property int     secsId ИД
 * @property int     secId Страница
 * @property string  secsCode Код блока
 * @property string  secsStr Текст
 * @property bool    secsMultiline Многострочный
 */
class modelCmsSectionsString extends cmsModelAbstract
{
    use SQLpgModelAdapter;
    /* static fields list */
    public static $_secsId         = 'secs_id';
    public static $_secId          = 'sec_id';
    public static $_secsCode       = 'secs_code';
    public static $_secsStr        = 'secs_str';
    public static $_secsMultiline  = 'secs_multiline';

    public static $tableName = 'cms_sections_string';
    protected $schemaName = 'knpzken';

    /* струкура таблицы */
    protected $struct = array (
    'table' => 'cms_sections_string',
    'schema' => 'knpzken',
    'comment' => 'Текстовые блоки страниц',
    'fields' => array(
      'secsId' => array(
        'COLUMN_NAME' => 'secs_id',
        'NULLABLE' => false,
        'COMMENT' => 'ИД',
        'FIELD_CLASS' => 'FieldInt',
      ),
      'secId' => array(
        'COLUMN_NAME' => 'sec_id',
        'NULLABLE' => false,
        'COMMENT' => 'Страница',
        'RELATE_TO' => 'cms_sections',
        'FIELD_CLASS' => 'FieldInt',
      ),
      'secsCode' => array(
        'COLUMN_NAME' => 'secs_code',
        'NULLABLE' => false,
        'LENGTH' => '20',
        'COMMENT' => 'Код блока',
        'FIELD_CLASS' => 'FieldString',
      ),
      'secsStr' => array(
        'COLUMN_NAME' => 'secs_str',
        'NULLABLE' => false,
        'COMMENT' => 'Текст',
        'FIELD_CLASS' => 'FieldText',
      ),
      'secsMultiline' => array(
        'COLUMN_NAME' => 'secs_multiline',
        'NULLABLE' => false,
        'COMMENT' => 'Многострочный',
        'FIELD_CLASS' => 'FieldBool',
      ),
    ),
    'fieldsDB' => array(
      'secs_id' => 'secsId',
      'sec_id' => 'secId',
      'secs_code' => 'secsCode',
      'secs_str' => 'secsStr',
      'secs_multiline' => 'secsMultiline',
    ),
    'primary' => 'secsId',
    'primaryDB' => 'secs_id',
    'tables' => array(
      0 => array(
        'table' => 'cms_sections_string',
        'primary' => 'secsId',
        'primaryDB' => 'secs_id',
        'schema' => 'knpzken',
        'prefix' => '',
      ),
    ),
  );

    /*** customer extensions ***/

}
