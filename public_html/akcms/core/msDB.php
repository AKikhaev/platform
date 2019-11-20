<?php

/** MsSQL database adapter
 * Class msdb
 */
class msDB extends CmsDBAbstract
{
    /**
     * @throws DBException
     */
    private function connect()
    {
        global $cfg;

        $this->db_conn = sqlsrv_connect(
            $cfg['db'][$this->cfgNumber]['host'],
            [
                "Database" => $cfg['db'][$this->cfgNumber]['database'],
                "Uid" => $cfg['db'][$this->cfgNumber]['username'],
                "PWD" => $cfg['db'][$this->cfgNumber]['password']
            ]
        );

        if ($this->db_conn===false) {
            throw new DBException("DB_no_data: ".sqlsrv_errors()['message']);
        }
    }

    /**
     * close connection
     */
    public function close()
    {
        if ($this->db_conn) sqlsrv_close($this->db_conn);
    }

    /**
     * @param $query
     * @return resource|bool
     * @throws DBException
     */
    public function query($query)
    {
        if ($this->db_conn === false) $this->connect();
        $sqlRes = sqlsrv_query($this->db_conn,$query);
        if ($sqlRes === false) throw new DBException("DB_no_data: " . $query, sqlsrv_errors()[0]['message']);
        return $sqlRes;
    }

    /**
     * @param $query
     * @return mixed
     * @throws DBException
     */
    function command($query)
    {
        return sqlsrv_rows_affected($this->query($query));
    }

    /**
     * @param $query
     * @return array|bool
     * @throws DBException
     */
    public function query_all($query)
    {
        $sqlres = $this->query($query);
        $res = [];
        if (sqlsrv_has_rows($sqlres)) {
            while ($row = sqlsrv_fetch_array($sqlres, SQLSRV_FETCH_ASSOC)) {
                $res[] = $row;
            }
        } else $res = false;
        sqlsrv_free_stmt($sqlres);
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
        $sqlres = $this->query($query);
        $res = [];
        if (sqlsrv_has_rows($sqlres)) {
            while (sqlsrv_fetch($sqlres))
                $res[] = sqlsrv_get_field( $sqlres, $col);
        } else $res = false;
        sqlsrv_free_stmt($sqlres);
        return $res;
    }

    /**
     * @param $query
     * @return bool|mixed
     * @throws DBException
     */
    function query_one($query)
    {
        $sqlres = $this->query($query);
        if (sqlsrv_has_rows($sqlres)) {
            if (sqlsrv_fetch($sqlres))
                $res = sqlsrv_get_field( $sqlres, 0);
            else throw new DBException("DB_no_data: " . $query, sqlsrv_errors()[0]['message']);
        } else $res = false;
        sqlsrv_free_stmt($sqlres);
        return $res;
    }

    /**
     * @param $query
     * @return mixed
     * @throws DBException
     */
    public function query_first($query)
    {
        $sqlres = $this->query($query);
        $res = sqlsrv_fetch_array($sqlres, SQLSRV_FETCH_ASSOC);
        if ($res === false) throw new DBException("DB_no_data: " . $query, sqlsrv_errors()[0]['message']);
        sqlsrv_free_stmt($sqlres);
        return $res;
    }

    function t($v)
    {
        $non_displayables = array(
            '/%0[0-8bcef]/',            // url encoded 00-08, 11, 12, 14, 15
            '/%1[0-9a-f]/',             // url encoded 16-31
            '/[\x00-\x08]/',            // 00-08
            '/\x0b/',                   // 11
            '/\x0c/',                   // 12
            '/[\x0e-\x1f]/'             // 14-31
        );
        foreach ( $non_displayables as $regex )
            $v = preg_replace( $regex, '', $v );
        $v = str_replace("'", "''", $v );
        return '\''.$v.'\'';
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
