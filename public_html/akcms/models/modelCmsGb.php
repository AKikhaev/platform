<?php

/**
 * cms_gb
 * Гостевая книга
 *
 * @property int     gbId ИД
 * @property string  gbDate Дата
 * @property string  gbName Имя
 * @property string  gbEmail Почта
 * @property string  gbMessage Сообщение
 * @property bool    gbEnabled Отображать
 * @property string  gbAnswer Ответ
 * @property int     gbAnswererId ИД отвечающего
 * @property string  gbPhone Телефон
 */
class modelCmsGb extends cmsModelAbstact
{
    use SQLpgModelAdapter;
    /* static fields list */
    public static $_gbId          = 'gb_id';
    public static $_gbDate        = 'gb_date';
    public static $_gbName        = 'gb_name';
    public static $_gbEmail       = 'gb_email';
    public static $_gbMessage     = 'gb_message';
    public static $_gbEnabled     = 'gb_enabled';
    public static $_gbAnswer      = 'gb_answer';
    public static $_gbAnswererId  = 'gb_answerer_id';
    public static $_gbPhone       = 'gb_phone';

    public static $tableName = 'cms_gb';
    protected $schemaName = 'knpzken';

    /* струкура таблицы */
    protected $struct = array (
    'table' => 'cms_gb',
    'schema' => 'knpzken',
    'comment' => 'Гостевая книга',
    'fields' => array(
      'gbId' => array(
        'COLUMN_NAME' => 'gb_id',
        'NULLABLE' => false,
        'COMMENT' => 'ИД',
        'FIELD_CLASS' => 'FieldInt',
      ),
      'gbDate' => array(
        'COLUMN_NAME' => 'gb_date',
        'NULLABLE' => false,
        'COMMENT' => 'Дата',
        'FIELD_CLASS' => 'FieldDateTime',
      ),
      'gbName' => array(
        'COLUMN_NAME' => 'gb_name',
        'NULLABLE' => false,
        'COMMENT' => 'Имя',
        'FIELD_CLASS' => 'FieldText',
      ),
      'gbEmail' => array(
        'COLUMN_NAME' => 'gb_email',
        'NULLABLE' => false,
        'COMMENT' => 'Почта',
        'FIELD_CLASS' => 'FieldText',
      ),
      'gbMessage' => array(
        'COLUMN_NAME' => 'gb_message',
        'NULLABLE' => false,
        'COMMENT' => 'Сообщение',
        'FIELD_CLASS' => 'FieldText',
      ),
      'gbEnabled' => array(
        'COLUMN_NAME' => 'gb_enabled',
        'NULLABLE' => false,
        'COMMENT' => 'Отображать',
        'FIELD_CLASS' => 'FieldBool',
      ),
      'gbAnswer' => array(
        'COLUMN_NAME' => 'gb_answer',
        'NULLABLE' => false,
        'COMMENT' => 'Ответ',
        'FIELD_CLASS' => 'FieldText',
      ),
      'gbAnswererId' => array(
        'COLUMN_NAME' => 'gb_answerer_id',
        'NULLABLE' => false,
        'COMMENT' => 'ИД отвечающего',
        'FIELD_CLASS' => 'FieldInt',
      ),
      'gbPhone' => array(
        'COLUMN_NAME' => 'gb_phone',
        'NULLABLE' => false,
        'COMMENT' => 'Телефон',
        'FIELD_CLASS' => 'FieldText',
      ),
    ),
    'fieldsDB' => array(
      'gb_id' => 'gbId',
      'gb_date' => 'gbDate',
      'gb_name' => 'gbName',
      'gb_email' => 'gbEmail',
      'gb_message' => 'gbMessage',
      'gb_enabled' => 'gbEnabled',
      'gb_answer' => 'gbAnswer',
      'gb_answerer_id' => 'gbAnswererId',
      'gb_phone' => 'gbPhone',
    ),
    'primary' => 'gbId',
    'primaryDB' => 'gb_id',
    'tables' => array(
      0 => array(
        'table' => 'cms_gb',
        'primary' => 'gbId',
        'primaryDB' => 'gb_id',
        'schema' => 'knpzken',
        'prefix' => '',
      ),
    ),
  );

    /*** customer extensions ***/

}
