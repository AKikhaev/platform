<?php

/** Makes key value dictionary from any table with primary key
 * Class tableDictionary
 */
class tableDictionary{
    protected $dictionary = [];
    public function __construct(cmsModelAbstract $table,$textField)
    {
        $primary = $table->zzJoinData()['primary'];
        foreach ($table as $record) {
            $this->dictionary[$table->__get($primary)] = $table->__get($textField);
        }
    }

    /** returns value associated with key or just key
     * @param $key
     * @return mixed
     */
    public function text($key){
        if (isset($this->dictionary[$key])) return $this->dictionary[$key];
        else return $key;
    }
}

/**
 * шаблон для автогенерации модели данных
 *
 * {#properties#}
 */
abstract class cmsModelAbstract implements SeekableIterator
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
        }
        else {
            $fieldName = $name;
            $name = @$this->struct['fieldsDB'][$fieldName];
            if ($name!==NULL) {
                $this->data[$fieldName] = $value;
                $this->filled[$name] = true;
            } else throw new DBException('fieldDB not found '.$name);
        }
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

    public function __getFieldDescription($fieldName){
        $name = @$this->struct['fieldsDB'][$fieldName];
        if ($name!==NULL) {
            $fieldComment = @$this->struct['fields'][$name]['COMMENT'];
            if ($fieldComment!==null) return $fieldComment;
        }
        return $fieldName;
    }
    /*-------------------*/




}