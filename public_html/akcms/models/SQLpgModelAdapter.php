<?php
const RAWSQL = 'rawsql';
/**
 * Class SQLpgModelAdapter
 */
trait SQLpgModelAdapter {
    private $sqlres = NULL;
    private $sqlpos = 0; // Позиция в sql
    private $recors = 0;
    private $position = 0; // Сторока для возврата
    private $datapos = -1; // Позиция текущих данных
    //protected $data = array(); //Объявлено в abstact
	private $result_type = PGSQL_ASSOC;
    /* @var pgdb */
    private $sql;
    /* SELECT SQL statement */

    public $query = '';
    private $query_fields = [];
    private $query_from = [];
    private $query_join = [];
    private $query_where = [];
    private $query_order = [];
    private $query_limit = 0;
    private $query_page = 0;
    private $query_pageSize = 50;
    private $tableSet = [];
    private $__hasData = false;

    /**
     * @return bool
     */
    public function hasData()
    {
        return $this->__hasData;
    }

    private function genFiledsDBList(){
        if (isset($this->struct['fieldsDB'])) return;
        foreach ($this->filled as $k=>$v) {
            $field = $this->struct['fields'][$k];
            $this->struct['fieldsDB'][$field['COLUMN_NAME']] = &$field;
        }
    }

    public function zzJoinData() {
        return $this->struct;
    }

    /**
     * join
     *
     * @param $way
     * @param cmsModelAbstract $join
     * @param string $prefix
     * @param array $condition
     * @return $this
     * @throws DBException
     */
    private function _join($way, cmsModelAbstract $join, $prefix = '', $condition = []) {
        $anotherStruct = $join->zzJoinData();
        $_query_join = '';

        if ($condition == [] || $condition == '') //Search mine link to another
            foreach ($this->struct['fields'] as $fieldName=>$field) {
                if (isset($field['RELATE_TO']) && $field['RELATE_TO']==$anotherStruct['table'] && $anotherStruct['primary']!='') {
                    if ($fieldName==$anotherStruct['primary'])
                        $condition = "USING ($field[COLUMN_NAME])";
                    else
                        $condition = "ON ($field[COLUMN_NAME]=".($prefix!=''?$prefix.'.':'').$anotherStruct['primaryDB'].")";
                    break;
                }
            }
        if ($condition == [] || $condition == '') //Search another link to mine
            foreach ($anotherStruct['fields'] as $fieldName=>$field) {
                foreach (array_reverse($this->struct['tables']) as $table) {
                    if (isset($field['RELATE_TO']) && $field['RELATE_TO'] == $table['table'] && $table['primary'] != '') {
                        if ($fieldName == $table['primary'])
                            $condition = "USING ($field[COLUMN_NAME])";
                        else
                            $condition = "ON (" . ($prefix != '' ? $prefix . '.' : '') . "$field[COLUMN_NAME]=" . $this->struct['primaryDB'] . ")";
                        break;
                    }
                }
            }

        $this->struct['fields'] = array_merge($this->struct['fields'],$anotherStruct['fields']);
        $this->struct['fieldsDB'] = array_merge($this->struct['fieldsDB'],$anotherStruct['fieldsDB']);

        $_query_join = "$way JOIN $anotherStruct[table] ";
        if ($prefix!='') $_query_join .= $prefix.' ';
        if (is_array($condition) && $condition != []) $_query_join .= $this->_where($condition);
        else if (is_string($condition)) $_query_join .= $condition;

        $this->query_join[] = $_query_join;
        $this->struct['tables'][] = [
            'table'=>$anotherStruct['table'],
            'primary'=>$anotherStruct['primary'],
            'primaryDB'=>$anotherStruct['primaryDB'],
            'schema'=>$anotherStruct['schema'],
            'prefix'=>'',
        ];

        return $this;
    }

    /**
     * Join Inner
     *
     * @param cmsModelAbstract $join
     * @param string $prefix
     * @param array $condition
     * @return $this|$this[]
     * @throws DBException
     */
    public function join(cmsModelAbstract $join, $prefix = '', $condition = []){
        return $this->_join('INNER',$join,$prefix,$condition);
    }

    /**
     * Join Inner
     *
     * @param cmsModelAbstract $join
     * @param string $prefix
     * @param array $condition
     * @return $this|$this[]
     * @throws DBException
     */
    public function joinInner(cmsModelAbstract $join, $prefix = '', $condition = []){
        return $this->_join('INNER',$join,$prefix,$condition);
    }

    /**
     * Join Left
     *
     * @param cmsModelAbstract $join
     * @param string $prefix
     * @param array $condition
     * @return $this|$this[]
     * @throws DBException
     */
    public function joinLeft(cmsModelAbstract $join, $prefix = '', $condition = []){
        return $this->_join('LEFT',$join,$prefix,$condition);
    }

    /**
     * Join Right
     *
     * @param cmsModelAbstract $join
     * @param string $prefix
     * @param array $condition
     * @return $this|$this[]
     * @throws DBException
     */
    public function joinRight(cmsModelAbstract $join, $prefix = '', $condition = []){
        return $this->_join('RIGHT',$join,$prefix,$condition);
    }

    /**
     * Join Outer
     *
     * @param cmsModelAbstract $join
     * @param string $prefix
     * @param array $condition
     * @return $this|$this[]
     * @throws DBException
     */
    public function joinOuter(cmsModelAbstract $join, $prefix = '', $condition = []){
        return $this->_join('OUTER',$join,$prefix,$condition);
    }

    /**
     * Set query fields
     *
     * @param array|string $fields
     * @return $this|$this[]
     */
    public function fields($fields = []) {
        $this->query = '!';
        if (func_num_args()>1) $this->query_fields = func_get_args();
        else $this->query_fields = $fields;
        return $this;
    }

    /**
     * Set query from
     *
     * @param array|string $from
     * @return $this|$this[]
     */
    public function from($from = []) {
        //todo другой объект для смешанных запросов
        $this->query = '!';
        if (func_num_args()>1) $this->query_from = func_get_args();
        else $this->query_from = $from;
        return $this;
    }

    /**
     * Generate where string
     *
     * field = value
     *
     * field =ANY [values]
     *
     * field BETWEEN 1 2
     *
     * [where] AND [where] AND [...] | set of and wheres
     *
     * id
     *
     * instanceof cmsModelAbstract
     *
     * @param array $where
     * @return string
     * @throws DBException
     */
    private function _where(array $where = []) {
        if (count($where) == 3 && is_string($where[1])) {
            // field = value
            // field =ANY [values]
            $field = @$this->struct['fields'][$this->struct['fieldsDB'][$where[0]]];
            if ($field !== NULL) {
                $type = is_object($where[2]) ? get_class($where[2]) : gettype($where[2]);
                if ($type == RAWSQL) return $where[0] . ' ' . $where[1] . ' ' . $where[2];
                $FieldClass = 'CMS' . $field['FIELD_CLASS'];
                if (strcasecmp($where[1], 'IN') === 0) $where[1] = '=ANY';
                /* @var CMSFieldAbstract */
                if (preg_match('/^(\=|\<|\>)ANY$/iu', $where[1])) {
                    if (!is_array($where[2])) $where[2] = [$where[2]];
                    $f = [];
                    foreach ($where[2] as $f_) {
                        $f[] = '(' . $FieldClass::quote($this->sql, $f_) . ')';
                    }
                    return $where[0] . $where[1] . '(VALUES' . implode(',', $f) . ')';
                } else return $where[0] . ' ' . $where[1] . ' ' . $FieldClass::quote($this->sql, $where[2]);
            } else throw new DBException('Where field not found ' . $where[0]);

        } elseif (count($where) == 4 && is_string($where[1])) {
            // field BETWEEN 1 2
            $field = @$this->struct['fields'][$this->struct['fieldsDB'][$where[0]]];
            if ($field !== NULL) {
                $FieldClass = 'CMS' . $field['FIELD_CLASS'];
                /* @var CMSFieldAbstract */
                return $where[0] . ' BETWEEN ' . $FieldClass::quote($this->sql, $where[2]) . ' AND ' . $FieldClass::quote($this->sql, $where[3]);
            } else throw new DBException('Where field not found ' . $where[0]);

        } elseif (count($where) > 0 && is_array($where[0])) {
            // [where] AND [where] AND [...] | set of and wheres
            $f = [];
            foreach ($where as $w) {
                $f[] = $this->_where($w);
            }
            $where = implode(' AND ', $f);
            return $where;
        } elseif (count($where) === 1 && is_numeric($where[0])) {
            // id
            $primary = $this->struct['primary'];
            if ($primary=='') throw new DBException('No primary for '.__CLASS__);
            $this->__set($primary,$where[0]);
            $where = $this->_pr_whereID();
            //var_dump__($where);
            return $where;
        }elseif (count($where) === 1 && $where[0] instanceof cmsModelAbstract) {
            $anotherStruct = $where[0]->zzJoinData();
            // cmsModelAbstract - filter another by them primary key
            foreach ($this->struct['fields'] as $fieldName=>$field) {
                $FieldClass = 'CMS' . $field['FIELD_CLASS'];
                if (isset($field['RELATE_TO']) && $field['RELATE_TO']==$anotherStruct['table'] && $anotherStruct['primary']!='') {
                    $where = "$field[COLUMN_NAME]=".$FieldClass::quote($this->sql,$where[0]->__get($anotherStruct['primary']));
                    return $where;
                }
            }
            throw new DBException('relation '.get_class($this).' to '.get_class($where[0]).' not found');
        }
        throw new DBException('unknown where');
    }


    /**
     * Set query where
     *
     * field = value
     *
     * field =ANY [values]
     *
     * field BETWEEN 1 2
     *
     * [where] AND [where] AND [...] | set of and wheres
     *
     * id
     *
     * instanceof cmsModelAbstract
     *
     * @param array|string|int $where id value may be only int. Otherwise go another way
     * @return $this|$this[]
     * @throws DBException
     */
    public function where($where = []) {
        $this->query = '!';
        $where = $this->_where(func_get_args());
        $this->query_where = $where;
        return $this;
    }

    /**
     * And where ...
     *
     * field = value
     *
     * field =ANY [values]
     *
     * field BETWEEN 1 2
     *
     * [where] AND [where] AND [...] | set of and wheres
     *
     * id
     *
     * instanceof cmsModelAbstract
     *
     * @param array $where
     * @return $this|$this[]
     * @throws DBException
     */
    public function AND_($where = []) {
        $this->query = '!';
        $where = $this->_where(func_get_args());
        $this->query_where .= ' AND ('.$where.')';
        return $this;
    }

    /**
     * Or where ...
     *
     * field = value
     *
     * field =ANY [values]
     *
     * field BETWEEN 1 2
     *
     * [where] AND [where] AND [...] | set of and wheres
     *
     * id
     *
     * instanceof cmsModelAbstract
     *
     * @param array $where
     * @return $this|$this[]
     * @throws DBException
     */
    public function OR_($where = []) {
        $this->query = '!';
        $where = $this->_where(func_get_args());
        $this->query_where .= ' OR ('.$where.')';
        return $this;
    }

    /**
     * Set query order
     *
     * @param array $order
     * @return $this|$this[]
     */
    public function order($order = []) {
        $this->query = '!';
        $this->query_order = $order;
        return $this;
    }

    /**
     * Set query limit
     *
     * @param int $limit
     * @return $this|$this[]
     */
    public function limit($limit = 0) {
        $this->query = '!';
        $this->query_limit = $limit;
        return $this;
    }

    /**
     * Set query page
     *
     * @param int $page
     * @param null $pageSize
     * @return $this|$this[]
     */
    public function page($page = 0,$pageSize=null) {
        $this->query = '!';
        $this->query_page = $page;
        $this->pageSize($pageSize);
        return $this;
    }

    /**
     * Set size of query page
     *
     * @param null $pageSize
     * @return $this|$this[]
     */
    public function pageSize($pageSize=null) {
        if (is_int($pageSize)) $this->query_pageSize = $pageSize;
        return $this;
    }

    /**
     * build query
     */
    private function buildQuery() {
        $query = 'SELECT ';

        if (is_string($this->query_fields)) $query .= $this->query_fields;
        elseif (count($this->query_fields)==0) $query .= '*';
        else $query .= implode(',',$this->query_fields);

        if (is_string($this->query_from)) $query .= ' FROM '.$this->query_from;
        elseif (count($this->query_from)==0) $query .= ' FROM '.static::$tableName;
        else $query .= ' FROM '.implode(' ',$this->query_from);

        if (is_string($this->query_join)) $query .= ' '.$this->query_join; // готовый перечень
        else if (count($this->query_join)>0) $query .= ' '.implode(' ',$this->query_join); // таблицы перечислены в массиве

        //todo Сложные условия, указание сравнения
        if (is_string($this->query_where) && $this->query_where!='') $query .= ' WHERE '.$this->query_where; // готовый query
        elseif (is_int($this->query_where)) $query .= ' WHERE '.$this->_pr_whereID($this->query_where); // Число
        elseif (is_object($this->query_where) && is_subclass_of($this->query_where, 'cmsModelAbstract')) $query .= ' WHERE '.$this->query_where->_pr_whereEQ(); //Класс самого себя
        elseif (is_array($this->query_where) && count($this->query_where)>0) $query .= ' WHERE '.implode(' AND ',$this->query_where); //набор готовых условий для склейки

        //todo указание направления сотрировки
        if (is_array($this->query_order) && count($this->query_order)>0) $query .= ' ORDER BY '.implode(',',$this->query_order); //Условия перечислены в массиве
        if (is_string($this->query_order) && $this->query_order!='') $query .= ' ORDER BY '.$this->query_order; // готовое условие

        if ($this->query_limit>0) $query .= ' LIMIT '.@(int)$this->query_limit;
        else if ($this->query_page>0)
            $query .= ' LIMIT '.$this->query_pageSize.' OFFSET '.($this->query_pageSize*($this->query_page-1));
        $this->query = $query;
    }

    /**
     * Set query
     *
     * @param array $fields
     * @param array $from
     * @param array $where
     * @param array $order
     * @param int $limit
     * @param int $page
     * @param null $pageSize
     * @return $this|$this[]
     * @throws DBException
     */
    public function select($fields = ['*'],$from = [],$where = [],$order = [],$limit = 0,$page = 0,$pageSize = NULL){
        $this->fields($fields);
        $this->from($from);
        $this->where($where);
        $this->order($order);
        $this->limit($limit);
        $this->page($page,$pageSize);
        return $this;
    }

    /* @property array struct */

    /**
     * SQLpgModelAdapter constructor.
     * @param null|int|string|resource $any  id | SQL запрос | русурс запроса pgsql
     *
     * @throws DBException
     */
    function __construct($any = NULL) {
        $this->sql = $GLOBALS['sql'];
        if (is_numeric($any)) {
            // id
            $this->where($any);
        }
        // !else
        if (is_string($any) && mb_strlen($any)>0) {
            // sql
            $this->query = $any;
        }
        elseif (is_resource($any)) {
            // resource
            $this->sqlres=$any;
            $this->recors = pg_num_rows($this->sqlres);
        }
    }

    /**
     * @param null $where
     * @return int
     * @throws DBException
     */
    public function update($where = null) {
        if ($where==null) $where=$this->_pr_whereID();
        $query = $this->pr_u($where);
        return $this->sql->command($query);
    }

    /**
     * @return int
     */
    public function insert() {
        $query = $this->pr_i();
        $primary = $this->struct['primary'];
        if ($primary=='') {
            return $this->sql->command($query);
        }
        else {
            $primaryDB = $this->struct['primaryDB'];
            $query .= ' RETURNING '.$primaryDB;
            $res = $this->sql->query_one($query);
            if ($res!=false) {
                $this->data[$primaryDB] = $res;
                return 1;
            } else return 0;
        }
    }

    /** Execute query and return the result
     * @return $this|$this[]
     * @throws DBException
     */
    public function get() {
        if ($this->query=='') throw new DBException('No query for '.__CLASS__);
        if ($this->query=='!') $this->buildQuery();
        $this->sqlres=$this->sql->query($this->query);
        $this->recors = pg_num_rows($this->sqlres);
        $this->position =0;
        $this->datapos =  -1;
        $this->sqlpos = null;
        $this->current();
        return $this;
    }

    /** return all records
     * @return $this|$this[]
     * @throws DBException
     */
    public function all() {
        return $this->where()->get();
    }

    /** return all records as array
     * @return array
     * @throws DBException
     */
    public function getAllAsArray(){
		if ($this->query=='') throw new DBException('No query for '.__CLASS__);
		if ($this->query=='!') $this->buildQuery();
    	return $this->sql->query_all($this->query);
	}

    /** return current data as array
     * @return mixed
     */
	public function asArray(){
        return $this->data;
    }

    /**
     * call as function
     * @return $this|$this[]
     * @throws DBException
     */
    public function __invoke()
    {
        return $this->get();
    }

    /**
     * @return $this|$this[]
     * @throws DBException
     */
    public function explain(){
        if ($this->query=='') throw new DBException('No query for '.__CLASS__);
        if ($this->query=='!') $this->buildQuery();
        $this->sql->query('begin');
        $res = implode("\n",$this->sql->query_all_column('EXPLAIN ANALYZE '.$this->query));
        $this->sql->query('rollback');
        CmsLogger::write($res);
        return $this;
    }

    /**
     * prepare update
     *
     * @param string $where
     * @return string
     * @throws DBException
     */
    private function pr_u($where='') {
        $_f = array();
        foreach ($this->filled as $k=>$v) if ($this->struct['primary']!=$k) {
            $field = $this->struct['fields'][$k];
            $FieldClass = 'CMS'.$field['FIELD_CLASS'];
            /* @var CMSFieldAbstract */
            $_f[] = $field['COLUMN_NAME'].'='.$FieldClass::quote($this->sql,$this->data[$field['COLUMN_NAME']]);
        }
        if (count($_f)==0) throw new DBException('No data to update for '.__CLASS__);
        return 'UPDATE '.static::$tableName.' SET '.implode(',',$_f).' WHERE '.$where;
    }

    /** prepare insert */
    private function pr_i() {
        $_f = array(); $_v = array();
        foreach ($this->filled as $k=>$v) if ($this->struct['primary']!=$k) {
            $field = $this->struct['fields'][$k];
            $FloatClass = 'CMS'.$field['FIELD_CLASS'];
            $_f[] = $field['COLUMN_NAME'];
            $_v[] = $FloatClass::quote($this->sql,$this->data[$field['COLUMN_NAME']]);
        }
        return 'INSERT INTO '.static::$tableName.'('.implode(',',$_f).') VALUES ('.implode(',',$_v).')';
    }

    /**
     * Prepare where part by ID
     * @param null $id
     * @return string
     * @throws DBException
     */
    private function _pr_whereID($id = null)
    {
        $primary = $this->struct['primary'];
        $field = $this->struct['fields'][$primary];
        if ($primary=='') throw new DBException('No primary for '.__CLASS__);
        $FieldClass = 'CMS'.$field['FIELD_CLASS'];
        if ($id === null) {
            if (!isset($this->data[$field['COLUMN_NAME']])) throw new DBException('Primary is unset for '.__CLASS__);
            return $field['COLUMN_NAME'].'='.$FieldClass::quote($this->sql,$this->data[$field['COLUMN_NAME']]);
        }
        else return $field['COLUMN_NAME'].'='.$FieldClass::quote($this->sql,$id);
    }

    /**
     * Prepare where part like filled
     * @return string
     * @throws DBException
     */
    public function _pr_whereEQ()
    {
        $_f = array();
        foreach ($this->filled as $k=>$v) if ($this->struct['primary']!=$k) {
            $field = $this->struct['fields'][$k];
            $FieldClass = 'CMS'.$field['FIELD_CLASS'];
            /* @var CMSFieldAbstract */
            $_f[] = $field['COLUMN_NAME'].'='.$FieldClass::quote($this->sql,$this->data[$field['COLUMN_NAME']]);
        }
        if (count($_f)==0 ) throw new DBException('No filled field for '.__CLASS__);
        return implode(' AND ',$_f);
    }

    function __destruct() {
        if ($this->sqlres) pg_free_result($this->sqlres);
    }

    function current() {
        if ($this->datapos !== $this->position)
        {
            if ($this->sqlpos!=$this->position) {
                pg_result_seek($this->sqlres,$this->position);
                $this->sqlpos = $this->position;
            }
            $this->fetch();
            $this->filled = array();
        }
        return $this;
    }
    function count() {return $this->recors;}
    function valid() {return $this->position<$this->recors;}
    function key() {return $this->position;}

    function rewind() {
        if ($this->sqlres) pg_result_seek($this->sqlres,0);
        $this->sqlpos = 0;
        $this->position=0;
    }
    function seek($position) {
        if ($this->sqlres) pg_result_seek($this->sqlres,$position);
        $this->sqlpos = $position;
        $this->position=$position;
    }
    function next() {++$this->position;}

    /* fetches */
    private function fetch_r() {
        $this->datapos = $this->sqlpos;
        $res = pg_fetch_row($this->sqlres);
        $this->__hasData = $res!==false;
        if ($this->__hasData) $this->sqlpos++;
        $this->data = $res;
        return $res;
    }

    private function fetch_a() {
        $this->datapos = $this->sqlpos;
        $res = pg_fetch_assoc($this->sqlres);
        $this->__hasData = $res!==false;
        if ($this->__hasData) $this->sqlpos++;
        $this->data = $res;
        return $res;
    }

    function fetch() {
        $this->datapos = $this->sqlpos;
        $res = pg_fetch_array($this->sqlres,null,$this->result_type);
        $this->__hasData = $res!==false;
        if ($this->__hasData) $this->sqlpos++;
        $this->data = $res;
        return $res;
    }

    private function __debugInfo(){return ['data'=>$this->data,'query'=>$this->query,'count'=>$this->count(),'datapos'=>$this->datapos,'sqlpos'=>$this->sqlpos,'position'=>$this->position];}

}

class rawsql{
    private $string = '';
    public function __construct($rawquery){
        $this->string = $rawquery;
    }

    public function __toString(){
        return $this->string;
    }
}