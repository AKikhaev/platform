<?php # Галлереи раздела

class SecStrEdit extends PgUnitAbstract {
	public $imgglrpath = 'img/objph/';
	
	public function initAjx()
	{
		global $page;
		return array(		
		'_sse' => array(
			'func' => 'ajxLoad',
			'object' => $this),
		'_sse_save' => array(
			'func' => 'ajxSave',
			'object' => $this),
		);
	}
  
	public function _rigthList()
	{
		return array(
		);
	}

	public function initAcl()
	{
		return array(
		'admin'=>true,
		'owner'=>true,
		'default'=>null
		);
	}

	public function ajxLoad()
	{
		global $sql;
		$checkRule = array();
		$checkRule[] = array('id'  , '/^\d+/');
		$checkResult = checkForm($_GET,$checkRule,$this->hasRight());
		if (count($checkResult)==0)
		{
            $data = $_GET;
            /* @var $sql pgdb */
            $sqldata = $sql->query_first('SELECT secs_str,secs_multiline FROM cms_sections_string WHERE secs_id='.$sql->d($data['id']));
            $data['value'] = $sqldata['secs_str'];
            $data['multiline'] = $sqldata['secs_multiline'];
			return GetShape('pages/ss_edit',$data);
		}
		return json_encode(array('error'=>$checkResult));
	}

	public function ajxSave()
    {
        global $sql;
        $checkRule = array();
        $checkRule[] = array('id', '/^\d+/');
        $checkRule[] = array('data', '');
        $checkResult = checkForm($_POST, $checkRule, $this->hasRight());
        if (count($checkResult) == 0) {
            $query = sprintf('UPDATE cms_sections_string SET secs_str = %s WHERE secs_id = %d;',
                $sql->t($_POST['data']),
                $_POST['id']);
            $res_count = $sql->command($query);
            return json_encode($res_count > 0 ? 't' : 'f');
        }
        return json_encode(array('error' => $checkResult));
    }
  
}
