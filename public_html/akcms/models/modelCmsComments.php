<?php

/**
 * cms_comments
 * Коментарии
 *
 * @property int     cmntId ИД
 * @property int     cmntSecId Страница
 * @property string  cmntDate Дата
 * @property string  cmntName Имя
 * @property string  cmntEmail Почта
 * @property string  cmntMessage Сообщение
 * @property bool    cmntEnabled Показывать
 */
class modelCmsComments extends cmsModelAbstract
{
    use SQLpgModelAdapter;
    /* static fields list */
    public static $_cmntId       = 'cmnt_id';
    public static $_cmntSecId    = 'cmnt_sec_id';
    public static $_cmntDate     = 'cmnt_date';
    public static $_cmntName     = 'cmnt_name';
    public static $_cmntEmail    = 'cmnt_email';
    public static $_cmntMessage  = 'cmnt_message';
    public static $_cmntEnabled  = 'cmnt_enabled';

    public static $tableName = 'cms_comments';
    protected $schemaName = 'knpzken';

    /* струкура таблицы */
    protected $struct = array (
    'table' => 'cms_comments',
    'schema' => 'knpzken',
    'comment' => 'Коментарии',
    'fields' => array(
      'cmntId' => array(
        'COLUMN_NAME' => 'cmnt_id',
        'NULLABLE' => false,
        'COMMENT' => 'ИД',
        'FIELD_CLASS' => 'FieldInt',
      ),
      'cmntSecId' => array(
        'COLUMN_NAME' => 'cmnt_sec_id',
        'NULLABLE' => true,
        'COMMENT' => 'Страница',
        'RELATE_TO' => 'cms_sections',
        'FIELD_CLASS' => 'FieldInt',
      ),
      'cmntDate' => array(
        'COLUMN_NAME' => 'cmnt_date',
        'NULLABLE' => false,
        'COMMENT' => 'Дата',
        'FIELD_CLASS' => 'FieldDateTime',
      ),
      'cmntName' => array(
        'COLUMN_NAME' => 'cmnt_name',
        'NULLABLE' => false,
        'COMMENT' => 'Имя',
        'FIELD_CLASS' => 'FieldText',
      ),
      'cmntEmail' => array(
        'COLUMN_NAME' => 'cmnt_email',
        'NULLABLE' => false,
        'COMMENT' => 'Почта',
        'FIELD_CLASS' => 'FieldText',
      ),
      'cmntMessage' => array(
        'COLUMN_NAME' => 'cmnt_message',
        'NULLABLE' => false,
        'COMMENT' => 'Сообщение',
        'FIELD_CLASS' => 'FieldText',
      ),
      'cmntEnabled' => array(
        'COLUMN_NAME' => 'cmnt_enabled',
        'NULLABLE' => false,
        'COMMENT' => 'Показывать',
        'FIELD_CLASS' => 'FieldBool',
      ),
    ),
    'fieldsDB' => array(
      'cmnt_id' => 'cmntId',
      'cmnt_sec_id' => 'cmntSecId',
      'cmnt_date' => 'cmntDate',
      'cmnt_name' => 'cmntName',
      'cmnt_email' => 'cmntEmail',
      'cmnt_message' => 'cmntMessage',
      'cmnt_enabled' => 'cmntEnabled',
    ),
    'primary' => 'cmntId',
    'primaryDB' => 'cmnt_id',
    'tables' => array(
      0 => array(
        'table' => 'cms_comments',
        'primary' => 'cmntId',
        'primaryDB' => 'cmnt_id',
        'schema' => 'knpzken',
        'prefix' => '',
      ),
    ),
  );

    /*** customer extensions ***/

}
