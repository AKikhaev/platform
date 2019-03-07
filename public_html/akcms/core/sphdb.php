<?php

class sphdb
{
    /* @var mysqli $db_conn */
    private $db_conn = null;

    private function connect()
    {
        $this->db_conn = new mysqli('127.0.0.1', '', '', '', 9306);
    }

    public function close()
    {
        $this->db_conn->close();
    }

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

    function command($query)
    {
        $this->query($query);
        return $this->db_conn->affected_rows;
    }

    public function query_all($query)
    {
        $sqlres = $this->query($query);
        $res = $sqlres->fetch_all(MYSQLI_ASSOC);
        $sqlres->free();
        return $res;
    }

    function query_one($query)
    {
        $sqlres = $this->query($query);
        if ($sqlres->num_rows > 0) $res = $sqlres->fetch_row()[0]; else $res = false;
        $sqlres->free();
        return $res;
    }

    public function query_first($query)
    {
        $sqlres = $this->query($query);
        $res = $sqlres->fetch_assoc();
        $sqlres->free();

        return $res;
    }


    function t($v)
    {
        if ($this->db_conn == false) $this->connect();
        return '\'' . $this->db_conn->escape_string($v) . '\'';
    }

    /* digit */
    function d($v)
    {
        return @intval($v);
    }

    /* float */
    function f($v)
    {
        return @floatval($v);
    }

    /* boolean */
    function b($v)
    {
        return ($v == 't' or $v == 'true' or $v === true) ? 'True' : 'False';
    }
}
