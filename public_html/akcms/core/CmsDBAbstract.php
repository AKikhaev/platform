<?php

abstract class CmsDBAbstract
{
    protected $cfgNumber = 0;
    /* @var resource|bool $db_conn */
    protected $db_conn = false;

    /**
     * constructor.
     * @param int $cfgNumber
     */
    public function __construct($cfgNumber = 0)
    {
        $this->cfgNumber = $cfgNumber;
    }

    public function __destruct()
    {
        $this->close();
    }

    /**
     * close connection
     */
    abstract public function close();

    /**
     * @param $query
     * @return resource|bool
     * @throws DBException
     */
    abstract public function query($query);

    /**
     * @param $query
     * @return mixed
     * @throws DBException
     */
    abstract public function command($query);

    /**
     * @param $query
     * @return array|bool
     * @throws DBException
     */
    abstract public function query_all($query);


    /**
     * @param $query
     * @param int $col
     * @return array|bool
     * @throws DBException
     */
    abstract public function query_all_column($query, $col = 0);

    /**
     * @param $query
     * @return bool|mixed
     * @throws DBException
     */
    abstract public function query_one($query);

    /**
     * @param $query
     * @return mixed
     * @throws DBException
     */
    abstract public function query_first($query);

    /** escape text value
     * @param $v
     * @return mixed
     */
    abstract public function t($v);

    /** filter integer value
     * @param $v
     * @return mixed
     */
    abstract public function d($v);

    /** filter float value
     * @param $v
     * @return mixed
     */
    abstract public function f($v);

    /** filter boolean value
     * @param $v
     * @return mixed
     */
    abstract public function b($v);

    /* DATETIME */
    public function dtFromDateTime(DateTime $v) {return $this->t($v->format('Y-m-d H:i:s'));}
    public function dtFromInt($v) {return $this->t( date('Y-m-d H:i:s',$v));}

    /** prepare insert row
     * @param $table
     * @param $fields
     * @return string
     */
    public function pr_i($table, $fields) {
        $_f = array(); $_v = array();
        foreach ($fields as $f=>$v) {
            $_f[] = $f;
            $_v[] = $v;
        }
        return 'INSERT INTO '.$table.'('.implode(',',$_f).') VALUES ('.implode(',',$_v).')';
    }

    /** prepare insert multi rows
     * @param $table
     * @param $fieldsList
     * @return string
     */
    public function pr_is($table, $fieldsList) {
        $fields = '';
        $values = array();
        foreach ($fieldsList as  $item) {
            $_f = array();
            $_v = array();
            foreach ($item as $f => $v) {
                $_f[] = $f;
                $_v[] = $v;
            }
            if ($fields=='') $fields = '('.implode(',',$_f).')';
            $values[] = '('.implode(',',$_v).')';
        }
        return 'INSERT INTO '.$table.$fields.' VALUES '.implode(',',$values);
    }

    /** prepare update
     * @param $table
     * @param $fields
     * @param string $where
     * @return string
     */
    public function pr_u($table, $fields, $where='') {
        $_f = array();
        foreach ($fields as $f=>$v) $_f[] = $f.'='.$v;
        #return 'UPDATE '.$table.' SET '.implode(',',$_f).($where==''?'':' WHERE '.$where);
        return 'UPDATE '.$table.' SET '.implode(',',$_f).(' WHERE '.$where);
    }

    /** prepare delete
     * @param $table
     * @param $fields
     * @return string
     */
    public function pr_d($table, $fields) {
        $_f = array();
        foreach ($fields as $f=>$v) $_f[] = $f.'='.$v;
        return 'DELETE FROM '.$table.' WHERE '.implode(' AND ',$_f);
    }

}
