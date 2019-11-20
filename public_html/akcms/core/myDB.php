<?php

/** MySQL database adapter
 * Class mydb
 */
class myDB extends CmsDBAbstract
{
    /* @var mysqli $db_conn */
    protected $db_conn = false;

    /**
     * @throws DBException
     */
    protected function connect()
    {
        global $cfg;
        $this->db_conn = new mysqli(
            $cfg['db'][$this->cfgNumber]['host'],
            $cfg['db'][$this->cfgNumber]['username'],
            $cfg['db'][$this->cfgNumber]['password'],
            $cfg['db'][$this->cfgNumber]['database']
        );
        if (mysqli_connect_error()) {
            throw new DBException("DB_no_data: ".mysqli_connect_error());
        }
    }

    public function close()
    {
        if ($this->db_conn) $this->db_conn->close();
    }

    /**
     * @param $query
     * @return bool|mysqli_result|resource
     * @throws DBException
     */
    public function query($query)
    {
        if ($this->db_conn == false) $this->connect();
        $sqlRes = $this->db_conn->query($query);
        if ($sqlRes == false) {
            $this->connect();
            $sqlRes = $this->db_conn->query($query);
        }
        if ($sqlRes === false) throw new DBException("DB_no_data: " . $query, mysqli_error($this->db_conn));
        return $sqlRes;
    }

    /**
     * @param $query
     * @return int
     * @throws DBException
     */
    function command($query)
    {
        $this->query($query);
        return $this->db_conn->affected_rows;
    }

    /**
     * @param $query
     * @return mixed
     * @throws DBException
     */
    public function query_all($query)
    {
        $sqlres = $this->query($query);
        $res = $sqlres->fetch_all(MYSQLI_ASSOC);
        $sqlres->free();
        return $res;
    }

    /**
     * @param $query
     * @param int $col
     * @return array|bool
     * @throws DBException
     */
    public function query_all_column($query, $col=0)
    {
        $data = [];
        $sqlres = $this->query($query);
        if ($sqlres->num_rows == 0) $data = false;
        else {
            while ($row = $sqlres->fetch_row()) {
                $data[] = $row[$col];
            }
        }
        $sqlres->free();
        return $data;
    }

    /**
     * @param $query
     * @return bool
     * @throws DBException
     */
    function query_one($query)
    {
        $sqlres = $this->query($query);
        if ($sqlres->num_rows > 0) $res = $sqlres->fetch_row()[0]; else $res = false;
        $sqlres->free();
        return $res;
    }

    /**
     * @param $query
     * @return array|null
     * @throws DBException
     */
    public function query_first($query)
    {
        $sqlres = $this->query($query);
        $res = $sqlres->fetch_assoc();
        $sqlres->free();

        return $res;
    }

    /**
     * @param $query
     * @return array|null
     * @throws DBException
     */
    public function query_first_row($query)
    {
        $sqlres = $this->query($query);
        $res = $sqlres->fetch_row();
        $sqlres->free();

        return $res;
    }

    /** escape text value
     * @param $v
     * @return mixed
     * @throws DBException
     */
    function t($v)
    {
        if ($this->db_conn == false) $this->connect();
        return '\'' . $this->db_conn->escape_string($v) . '\'';
    }

    /** filter integer value
     * @param $v
     * @return mixed
     */
    function d($v)
    {
        return @intval($v);
    }

    /** filter float value
     * @param $v
     * @return mixed
     */
    function f($v)
    {
        return @floatval($v);
    }

    /** filter boolean value
     * @param $v
     * @return mixed
     */
    function b($v)
    {
        return ($v == 't' or $v == 'true' or $v === true) ? 'True' : 'False';
    }
}
