<?php

class pgdbQuery implements Iterator {
    private $sqlres = NULL;
    private $sqlpos = 0;
    private $recors = 0;
    private $position = 0;
    private $datapos = -1;
    private $data = false;
    public $result_type = PGSQL_ASSOC;

    public function __construct($sqlres) {
        $this->sqlres=$sqlres;
        $this->recors = pg_num_rows($sqlres);
    }

    public function rewind() {
        pg_result_seek($this->sqlres,0);
        $this->sqlpos = 0;
        $this->position=0;
    }

    public function current() {
        if ($this->datapos !== $this->position)
        {
            if ($this->sqlpos!=$this->position) pg_result_seek($this->sqlres,$this->position);
            return $this->fetch();
        }
        return $this->data;
    }

    public function key() {
        return $this->position;
    }

    public function next() {
        ++$this->position;
    }

    public function valid() {
        return $this->position<$this->recors;
    }

    public function count() {
        return $this->recors;
    }

    /* fetches */
    public function fetch_r() {
        $this->datapos = $this->sqlpos;
        $res = pg_fetch_row($this->sqlres);
        if ($res!==false) $this->sqlpos++;
        $this->data = $res;
        return $res;
    }

    public function fetch() {
        $this->datapos = $this->sqlpos;
        $res = pg_fetch_array($this->sqlres,null,$this->result_type);
        if ($res!==false) $this->sqlpos++;
        $this->data = $res;
        return $res;
    }
    public function fetch_a() {
        return $this->fetch();
    }
    public function fetch_arr() {
        $this->datapos = $this->sqlpos;
        $res = pg_fetch_array($this->sqlres);
        if ($res!==false) $this->sqlpos++;
        $this->data = $res;
        return $res;
    }
}

/** Postgres database adapter
 * Class pgdb
 */
class pgDB extends CmsDBAbstract
{
    protected $debug = false;

    /**
     * @throws DBException
     */
    protected function connect()
    {
        global $cfg;
        $this->db_conn = pg_pconnect('host='.$cfg['db'][$this->cfgNumber]['host'].
            ' port=5432 dbname='.$cfg['db'][$this->cfgNumber]['database'].
            ' user='.$cfg['db'][$this->cfgNumber]['username'].
            ' password='.$cfg['db'][$this->cfgNumber]['password']);
        if ($this->db_conn===false) throw new DBException('DB_connect');
        pg_query($this->db_conn,'SET client_encoding TO \'UTF-8\';SET search_path TO '.$cfg['db'][$this->cfgNumber]['schema'].';');
        if ($this->debug) call_user_func(profiler::$sql_logger,'connected');
    }


    /**
     * close connection
     */
    public function close()
    {
        if ($this->db_conn) pg_close($this->db_conn);
    }

    /**
     * @param $query
     * @return bool|resource
     * @throws DBException
     */
    public function query($query)
    {
        if (!$this->db_conn) $this->connect();
        $sqlres = pg_query($this->db_conn,$query); if ($sqlres===false) throw new DBException('DB_no_data: ' .$query,pg_last_error());
        if ($this->debug) {
            ++profiler::$sql_quries;
            call_user_func(profiler::$sql_logger,$query);
        }
        return $sqlres;
    }

    /**
     * @param $query
     * @return int|mixed
     * @throws DBException
     */
    public function command($query)
    {
        if (!$this->db_conn) $this->connect();
        $sqlres = $this->query($query);
        if ($this->debug) {
            --profiler::$sql_quries;
            ++profiler::$sql_commands;
        }
        return pg_affected_rows($sqlres);
    }

    /**
     * @param $query
     * @return array|bool
     * @throws DBException
     */
    public function query_all($query)
    {
        $sqlres = $this->query($query);
        $res = pg_fetch_all($sqlres);
        pg_free_result($sqlres);
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
        $res = pg_fetch_all_columns($sqlres,$col);
        pg_free_result($sqlres);

        return $res;
    }

    /**
     * @param $query
     * @return bool|mixed|string
     * @throws DBException
     */
    public function query_one($query)
    {
        $sqlres = $this->query($query);
        if (pg_num_rows($sqlres)>0) $res = pg_fetch_result($sqlres,0,0); else $res = false;
        pg_free_result($sqlres);

        return $res;
    }

    /**
     * @param $query
     * @return array|mixed
     * @throws DBException
     */
    public function query_first($query)
    {
        $sqlres = $this->query($query);
        $res = pg_fetch_assoc($sqlres);
        pg_free_result($sqlres);

        return $res;
    }

    public function query_first_row($query)
    {
        $sqlres = $this->query($query);
        $res = pg_fetch_row($sqlres);
        pg_free_result($sqlres);

        return $res;
    }


    public function pgf_array_text($v)
    {
        foreach ($v as &$i) $i = '\''.pg_escape_string($i).'\'';
        return 'ARRAY['.implode(',',$v).']::text[]';
    }

    public function pgf_extract_array_text($v)
    {
        $v = trim($v,'{}');
        return str_getcsv($v,',','"');
    }

    public function pgf_wordarrays_text($v)
    {
        $arrNum = 0;
        $resarr = array();
        foreach ($v as &$a) {
            foreach ($a as &$i) $resarr[] = '{"'.$arrNum.'","'.pg_escape_string($i).'"}';
            $arrNum++;
        }
        unset($i);
        return 'ARRAY['.implode(',',$resarr).']';
        //return '\'{'.implode(',',$resarr).'}\'';
    }


    /* prepare insert */
    public function pr_i($table, $fields) {
        $_f = array(); $_v = array();
        foreach ($fields as $f=>$v) {
            $_f[] = $f;
            $_v[] = $v;
        }
        return 'INSERT INTO '.$table.'('.implode(',',$_f).') VALUES ('.implode(',',$_v).')';
    }

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

    /* prepare update */
    public function pr_u($table, $fields, $where='') {
        $_f = array();
        foreach ($fields as $f=>$v) $_f[] = $f.'='.$v;
        #return 'UPDATE '.$table.' SET '.implode(',',$_f).($where==''?'':' WHERE '.$where);
        return 'UPDATE '.$table.' SET '.implode(',',$_f).(' WHERE '.$where);
    }

    public function pr_d($table, $fields) {
        $_f = array();
        foreach ($fields as $f=>$v) $_f[] = $f.'='.$v;
        return 'DELETE FROM '.$table.' WHERE '.implode(' AND ',$_f);
    }

    ################################ short syntax
    /** escape text value
     * @param $v
     * @return mixed
     */
    public function t($v)
    {
        return '\''.pg_escape_string($v).'\'';
    }

    /** filter integer value
     * @param $v
     * @return mixed
     */
    public function d($v) {
        return @(int)$v;
    }

    /** filter float value
     * @param $v
     * @return mixed
     */
    public function f($v) {
        return @(float)$v;
    }

    /** filter boolean value
     * @param $v
     * @return mixed
     */
    public function b($v) {
        return ($v=='t' or $v=='true' or $v===true)?'True':'False';
    }

    /** array to base text array
     * @param $v
     * @return string
     */
    function a_t($v) {
        foreach ($v as &$i) $i = '\''.pg_escape_string($i).'\'';
        return 'ARRAY['.implode(',',$v).']::text[]';
    }

    /** array to base int array
     * @param $v
     * @return string
     */
    public function a_d($v) {
        foreach ($v as &$i) $i = @(int)$i; # check it is digit
        return 'ARRAY['.implode(',',$v).']::bigint[]';
    }
    /** array to base float array
     * @param $v
     * @return string
     */
    public function a_f($v) {
        foreach ($v as &$i) $i = @(float)$i; # check it is float
        return 'ARRAY['.implode(',',$v).']';
    }

    /*** base array to normal array
     * @param $v
     * @return array
     */
    public function ad_a($v) {
        $v = str_replace(['{','}','ARRAY[',']'],'',$v);
        return $v==''?array():explode(',',$v);
    }

    /*** text array to array
     * @param $v
     * @return array
     */
    function at_a($v)
    {
        $v = trim($v,'{}');
        $items = str_getcsv($v,',','"');
        return $items;
    }

    /* DATETIME */
    public function dtFromDateTime(DateTime $v) {return $this->t($v->format('Y-m-d H:i:s T'));}
    public function dtFromInt($v) {return $this->t( date('Y-m-d H:i:s T',$v));}

    /**
     * @param bool $debug
     */
    public function zzzSetDebug($debug=true)
    {
        $this->debug = $debug;
    }

    /** Экранирует данные данные и приводят их к указанным типам полей
     * @param $data
     * @param $fieldTypes
     * @return mixed
     */
    function dataEscape($data,$fieldTypes) {
        foreach ($data as $k=>&$v) {
            if (isset($fieldTypes[$k])) {
                switch ($fieldTypes[$k]) {
                    case 'BIGSERIAL':
                    case 'BIGINT': $v = $this->d($v); break;
                    case 'DOUBLE PRECISION': $v = $this->f($v); break;
                    case 'BOOLEAN': $v = $this->b($v); break;
                    case 'TIMESTAMP': $v = $this->dtFromDateTime($v); break;
                    default: $v = $this->t($v);
                }
            } else $v = $this->t($v);
        }

        return $data;
    }

    public function queryObj($query) {
        return new pgdbQuery($this->query($query));
    }

    /**
     * @param $query
     * @return array
     * @throws DBException
     */
    public function query_dict($query)
    {
        $dict = [];
        $data = $this->query_all($query);
        if ($data!==false) foreach ($data as $k=>$item){
            $dict[current($item)] = $item;
        }
        return $dict;
    }
}
