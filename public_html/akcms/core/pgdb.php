<?php

class pgdbQuery implements Iterator {
    private $sqlres=false;
    private $sqlpos = 0;
    private $recors = 0;
    private $position = 0;
    private $datapos = -1;
    private $data = false;

    function __construct($sqlres) {
        $this->sqlres=$sqlres;
        $this->recors = pg_num_rows($sqlres);
    }

    function rewind() {
        pg_result_seek($this->sqlres,0);
        $this->sqlpos = 0;
        $this->position=0;
    }

    function current() {
        if ($this->datapos !== $this->position)
        {
            if ($this->sqlpos!=$this->position) pg_result_seek($this->sqlres,$this->position);
            return $this->fetch();
        }
        return $this->data;
    }

    function key() {
        return $this->position;
    }

    function next() {
        ++$this->position;
    }

    function valid() {
        return $this->position<$this->recors;
    }

    function count() {
        return $this->recors;
    }

    /* fetches */
    function fetch_r() {
        $this->datapos = $this->sqlpos;
        $res = pg_fetch_row($this->sqlres);
        if ($res!==false) $this->sqlpos++;
        $this->data = $res;
        return $res;
    }

    function fetch() {
        $this->datapos = $this->sqlpos;
        $res = pg_fetch_assoc($this->sqlres);
        if ($res!==false) $this->sqlpos++;
        $this->data = $res;
        return $res;
    }
    function fetch_a() {
        return $this->fetch();
    }
    function fetch_arr() {
        $this->datapos = $this->sqlpos;
        $res = pg_fetch_array($this->sqlres);
        if ($res!==false) $this->sqlpos++;
        $this->data = $res;
        return $res;
    }
}

class pgdb {
    private $db_conn;

    function __construct()
    {
        global $cfg;
        $dbnum = 1;
        $this->db_conn = pg_pconnect('host='.$cfg['db'][$dbnum]['host'].
            ' port=5432 dbname='.$cfg['db'][$dbnum]['database'].
            ' user='.$cfg['db'][$dbnum]['username'].
            ' password='.$cfg['db'][$dbnum]['password']);
        if ($this->db_conn===false) throw new CmsException("DB_connect");
        pg_query($this->db_conn,'SET client_encoding TO \'UTF-8\';SET search_path TO '.$cfg['db'][$dbnum]['schema'].';');
    }

    function getClientEncoding()
    {
        return pg_client_encoding();
    }

    function query($query)
    {
        $sqlres = pg_query($query); if ($sqlres===false) throw new DBException("DB_no_data: ".$query,pg_last_error());
        return $sqlres;
    }

    function queryObj($query) {
        return new pgdbQuery($this->query($query));
    }

    function command($query)
    {
        $sqlres = $this->query($query);
        return pg_affected_rows($sqlres);
    }

    function query_all($query)
    {
        $sqlres = $this->query($query);
        $res = pg_fetch_all($sqlres);
        pg_free_result($sqlres);
        return $res;
    }


    function query_all_column($query,$row=0)
    {
        $sqlres = $this->query($query);
        $res = pg_fetch_all_columns($sqlres,$row);
        pg_free_result($sqlres);

        return $res;
    }

    function query_first_assoc($query)
    {
        $sqlres = $this->query($query);
        $res = pg_fetch_assoc($sqlres);
        pg_free_result($sqlres);

        return $res;
    }

    function query_first_row($query)
    {
        $sqlres = $this->query($query);
        $res = pg_fetch_row($sqlres);
        pg_free_result($sqlres);

        return $res;
    }

    function query_one($query)
    {
        $sqlres = $this->query($query);
        if (pg_num_rows($sqlres)>0) $res = pg_fetch_result($sqlres,0,0); else $res = false;
        pg_free_result($sqlres);

        return $res;
    }

    function query_first($query)
    {
        $sqlres = $this->query($query);
        $res = pg_fetch_assoc($sqlres);
        pg_free_result($sqlres);

        return $res;
    }

    function pgf_text($v)
    {
        return '\''.pg_escape_string($v).'\'';
    }

    function pgf_array_int($v)
    {
        foreach ($v as &$i) $i = @(int)$i; # check it is digit
        return 'ARRAY['.implode(',',$v).']';
    }

    function pgf_array_float($v)
    {
        foreach ($v as &$i) $i = @(float)$i; # check it is float
        return 'ARRAY['.implode(',',$v).']';
    }

    function pgf_array_text($v)
    {
        foreach ($v as &$i) $i = '\''.pg_escape_string($i).'\'';
        return 'ARRAY['.implode(',',$v).']::text[]';
    }

    function pgf_extract_array_text($v)
    {
        $v = trim($v,'{}');
        $items = str_getcsv($v,',','"');
        return $items;
    }

    function pgf_wordarrays_text($v)
    {
        $arrNum = 0;
        $resarr = array();
        foreach ($v as &$a) {
            foreach ($a as &$i) $resarr[] = '{"'.$arrNum.'","'.pg_escape_string($i).'"}';
            $arrNum++;
        }
        return 'ARRAY['.implode(',',$resarr).']';
        //return '\'{'.implode(',',$resarr).'}\'';
    }

    function pgf_boolean($v)
    {
        return ($v=='t' or $v=='true' or $v===true)?'True':'False';
    }

    /* prepare insert */
    function pr_i($table,$fields) {
        $_f = array(); $_v = array();
        foreach ($fields as $f=>$v) {
            $_f[] = $f;
            $_v[] = $v;
        }
        return 'INSERT INTO '.$table.'('.implode(',',$_f).') VALUES ('.implode(',',$_v).')';
    }

    function pr_is($table,$fieldsList) {
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
    function pr_u($table,$fields,$where='') {
        $_f = array();
        foreach ($fields as $f=>$v) $_f[] = $f.'='.$v;
        #return 'UPDATE '.$table.' SET '.implode(',',$_f).($where==''?'':' WHERE '.$where);
        return 'UPDATE '.$table.' SET '.implode(',',$_f).(' WHERE '.$where);
    }

    function pr_d($table,$fields) {
        $_f = array();
        foreach ($fields as $f=>$v) $_f[] = $f.'='.$v;
        return 'DELETE FROM '.$table.' WHERE '.implode(' AND ',$_f);
    }

    ################################ short syntax
    /* text */
    function t($v) {return $this->pgf_text($v);}

    /* digit */
    function d($v) {return @(int)$v;}

    /* float */
    function f($v) {return @(float)$v;}

    /* boolean */
    function b($v) {return $this->pgf_boolean($v);}

    /* array of text */
    function a_t($v) {return $this->pgf_array_text($v);}

    /* array of digit */
    function a_d($v) {return $this->pgf_array_int($v);}

    /* digital array to array */
    function da_a($v) {return $v=='{}'?array():explode(',',trim($v,'{}'));}

    /* array of float */
    function a_f($v) {return $this->pgf_array_float($v);}

    function query_fa($query) {
        return $this->query_first_assoc($query);
    }

    function query_fr($query) {
        return $this->query_first_row($query);
    }
}
