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
}
