<?php

/**
 * шаблон для автогенерации модели данных
 *
 * {#properties#}
 */
abstract class cmsModelAbstact implements SeekableIterator
{
    public static $tableName = '{#tableName#}';
    protected $schemaName = '{#schemaName#}';

    /* заполненные поля */
    protected $filled = array();

    /* струкура таблицы */
    protected $struct = array();

    /* последние полученнные данные */
    protected $data = array();

    /*
     * Пустой конструктор для заполнения нового объекта
    function __construct($query = NULL)
    {
    }
     */

    public function __set($name, $value)
    {
        $fieldName = @$this->struct['fields'][$name]['COLUMN_NAME'];
        if ($fieldName!==NULL) {
            $this->data[$fieldName] = $value;
            $this->filled[$name] = true;
        } else throw new DBException('Field not found '.$name);
    }

    public function __get($name)
    {
        $fieldName = @$this->struct['fields'][$name]['COLUMN_NAME'];
        if ($fieldName!==NULL) {
            return @$this->data[$fieldName];
        }
        elseif (isset($this->data[$name]))
            return $this->data[$name];
        else throw new DBException('Field not found '.$name);
    }

    public function __unset($name)
    {
        $fieldName = @$this->struct['fields'][$name]['COLUMN_NAME'];
        if ($fieldName!==NULL) {
            unset($this->data[$fieldName]);
        } else throw new DBException('Field not found '.$name);
    }

    public function __isset($name)
    {
        $fieldName = @$this->struct['fields'][$name]['COLUMN_NAME'];
        if ($fieldName!==NULL) {
            return isset($this->data[$fieldName]);
        } else throw new DBException('Field not found '.$name);
    }

    /*-------------------*/




}