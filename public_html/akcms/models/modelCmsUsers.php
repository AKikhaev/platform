<?php

/**
 * cms_users
 * Пользователи
 *
 * @property int     idUsr ИД
 * @property string  usrLogin Логин
 * @property string  usrEmail Почта
 * @property string  usrPhone Телефон
 * @property string  usrPasswordMd5 Хеш
 * @property bool    usrEnabled Разрешен
 * @property string  usrAutohash Хеш автовхода
 * @property string  usrLastLogin Последний вход
 * @property array   usrGrp Группа
 * @property string  usrName Имя
 * @property string  usrSoname Фамилия
 * @property bool    usrAdmin Админ
 * @property string  usrActcode Код активации
 * @property bool    usrActivated Активирован
 * @property string  usrLostcode Код восстановления
 * @property string  usrRegisteredStamp Дата создания
 * @property string  usrRegisteredIp IP адрес регистрации
 */
class modelCmsUsers extends cmsModelAbstract
{
    use SQLpgModelAdapter;
    /* static fields list */
    public static $_idUsr               = 'id_usr';
    public static $_usrLogin            = 'usr_login';
    public static $_usrEmail            = 'usr_email';
    public static $_usrPhone            = 'usr_phone';
    public static $_usrPasswordMd5      = 'usr_password_md5';
    public static $_usrEnabled          = 'usr_enabled';
    public static $_usrAutohash         = 'usr_autohash';
    public static $_usrLastLogin        = 'usr_last_login';
    public static $_usrGrp              = 'usr_grp';
    public static $_usrName             = 'usr_name';
    public static $_usrSoname           = 'usr_soname';
    public static $_usrAdmin            = 'usr_admin';
    public static $_usrActcode          = 'usr_actcode';
    public static $_usrActivated        = 'usr_activated';
    public static $_usrLostcode         = 'usr_lostcode';
    public static $_usrRegisteredStamp  = 'usr_registered_stamp';
    public static $_usrRegisteredIp     = 'usr_registered_ip';

    public static $tableName = 'cms_users';
    protected $schemaName = '{default}';

    /* струкура таблицы */
    protected $struct = array (
    'table' => 'cms_users',
    'schema' => '{default}',
    'comment' => 'Пользователи',
    'fields' => array(
      'idUsr' => array(
        'COLUMN_NAME' => 'id_usr',
        'IDENTITY' => true,
        'COMMENT' => 'ИД',
        'FIELD_CLASS' => 'FieldInt',
      ),
      'usrLogin' => array(
        'COLUMN_NAME' => 'usr_login',
        'COMMENT' => 'Логин',
        'FIELD_CLASS' => 'FieldText',
      ),
      'usrEmail' => array(
        'COLUMN_NAME' => 'usr_email',
        'COMMENT' => 'Почта',
        'FIELD_CLASS' => 'FieldText',
      ),
      'usrPhone' => array(
        'COLUMN_NAME' => 'usr_phone',
        'COMMENT' => 'Телефон',
        'FIELD_CLASS' => 'FieldText',
      ),
      'usrPasswordMd5' => array(
        'COLUMN_NAME' => 'usr_password_md5',
        'LENGTH' => '64',
        'COMMENT' => 'Хеш',
        'FIELD_CLASS' => 'FieldString',
      ),
      'usrEnabled' => array(
        'COLUMN_NAME' => 'usr_enabled',
        'COMMENT' => 'Разрешен',
        'FIELD_CLASS' => 'FieldBool',
      ),
      'usrAutohash' => array(
        'COLUMN_NAME' => 'usr_autohash',
        'LENGTH' => '64',
        'COMMENT' => 'Хеш автовхода',
        'FIELD_CLASS' => 'FieldString',
      ),
      'usrLastLogin' => array(
        'COLUMN_NAME' => 'usr_last_login',
        'NULLABLE' => true,
        'COMMENT' => 'Последний вход',
        'FIELD_CLASS' => 'FieldDateTime',
      ),
      'usrGrp' => array(
        'COLUMN_NAME' => 'usr_grp',
        'COMMENT' => 'Группа',
        'QUOTE_FUNCTION' => 'a_d',
        'FIELD_CLASS' => 'FieldManyInt',
      ),
      'usrName' => array(
        'COLUMN_NAME' => 'usr_name',
        'COMMENT' => 'Имя',
        'FIELD_CLASS' => 'FieldText',
      ),
      'usrSoname' => array(
        'COLUMN_NAME' => 'usr_soname',
        'COMMENT' => 'Фамилия',
        'FIELD_CLASS' => 'FieldText',
      ),
      'usrAdmin' => array(
        'COLUMN_NAME' => 'usr_admin',
        'COMMENT' => 'Админ',
        'FIELD_CLASS' => 'FieldBool',
      ),
      'usrActcode' => array(
        'COLUMN_NAME' => 'usr_actcode',
        'COMMENT' => 'Код активации',
        'FIELD_CLASS' => 'FieldText',
      ),
      'usrActivated' => array(
        'COLUMN_NAME' => 'usr_activated',
        'COMMENT' => 'Активирован',
        'FIELD_CLASS' => 'FieldBool',
      ),
      'usrLostcode' => array(
        'COLUMN_NAME' => 'usr_lostcode',
        'COMMENT' => 'Код восстановления',
        'FIELD_CLASS' => 'FieldText',
      ),
      'usrRegisteredStamp' => array(
        'COLUMN_NAME' => 'usr_registered_stamp',
        'COMMENT' => 'Дата создания',
        'FIELD_CLASS' => 'FieldDateTime',
      ),
      'usrRegisteredIp' => array(
        'COLUMN_NAME' => 'usr_registered_ip',
        'NULLABLE' => true,
        'LENGTH' => '46',
        'COMMENT' => 'IP адрес регистрации',
        'FIELD_CLASS' => 'FieldString',
      ),
    ),
    'fieldsDB' => array(
      'id_usr' => 'idUsr',
      'usr_login' => 'usrLogin',
      'usr_email' => 'usrEmail',
      'usr_phone' => 'usrPhone',
      'usr_password_md5' => 'usrPasswordMd5',
      'usr_enabled' => 'usrEnabled',
      'usr_autohash' => 'usrAutohash',
      'usr_last_login' => 'usrLastLogin',
      'usr_grp' => 'usrGrp',
      'usr_name' => 'usrName',
      'usr_soname' => 'usrSoname',
      'usr_admin' => 'usrAdmin',
      'usr_actcode' => 'usrActcode',
      'usr_activated' => 'usrActivated',
      'usr_lostcode' => 'usrLostcode',
      'usr_registered_stamp' => 'usrRegisteredStamp',
      'usr_registered_ip' => 'usrRegisteredIp',
    ),
    'primary' => 'idUsr',
    'primaryDB' => 'id_usr',
    'tables' => array(
      0 => array(
        'table' => 'cms_users',
        'primary' => 'idUsr',
        'primaryDB' => 'id_usr',
        'schema' => '{default}',
        'prefix' => '',
      ),
    ),
  );

    /*** customer extensions ***/

}
