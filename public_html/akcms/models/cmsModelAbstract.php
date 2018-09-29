<?php

/** Makes key value dictionary from any table with primary key
 * Class tableDictionary
 */
class tableKeyValueDictionary{
    protected $data = [];

    /** tableKeyValueDictionary constructor.
     * @param cmsModelAbstract $table
     * table object
     * @param $textField
     * text field name as in object
     * @throws DBException
     */
    public function __construct(cmsModelAbstract $table,$textField)
    {
        $primary = $table->zzJoinData()['primary'];
        foreach ($table as $record) {
            $this->data[$record->__get($primary)] = $record->__get($textField);
        }
    }

    /** returns value associated with key or just key
     * @param $key
     * @return mixed
     */
    public function value($key){
        if (isset($this->data[$key])) return $this->data[$key];
        else return $key;
    }
}

class tableKeyData {
    protected $data = [];
    public function __construct(cmsModelAbstract $table)
    {
        $table->get();
        $primary = $table->zzJoinData()['primary'];
        foreach ($table as $record) {
            $this->dictionary[$record->__get($primary)] = $record->asArray();
        }
    }

    /** returns value associated with key or just key
     * @param $key
     * @return mixed
     */
    public function data($key){
        if (isset($this->data[$key])) return $this->data[$key];
        else return false;
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

    /**
     * set filled array for non empty date values
     */
    public function __reFill(){
        foreach ($this->data as $key=>$datum) {
            $name = @$this->struct['fieldsDB'][$key];
            if ($name!==null) $this->filled[$name] = true;
        }
    }

    /** get Description field text as it stored in db
     * @param $fieldName
     * @return mixed
     */
    public function __getFieldDescription($fieldName){
        $name = @$this->struct['fieldsDB'][$fieldName];
        if ($name!==NULL) {
            $fieldComment = @$this->struct['fields'][$name]['COMMENT'];
            if ($fieldComment!==null) return $fieldComment;
        }
        return $fieldName;
    }

    /** return current data as array
     * @return mixed
     */
    public function asArray(){
        return $this->data;
    }

    /*-------------------*/




}