<?php # Галлереи раздела

class ObjGallery extends PgUnitAbstract {
	public $imgglrpath = 'img/objph/';
	
	function initAjx()
	{
		global $page;
		return array(		
		'_glrilst' => array(
			'func' => 'ajxGlrCopList',
			'object' => $this),
		'_glriupl' => array(
			'func' => 'ajxGlrIUpload',
			'object' => $this),
		'_glrcpgdrp' => array(
			'func' => 'ajxGlrCpgDrp',
			'object' => $this),
        '_glrcpgnmupd' => array(
			'func' => 'ajxGlrCpgNameUpd',
			'object' => $this),
		);
	}
  
	function _rigthList()
	{
		return array(
		);
	}

	function initAcl()
	{
		return array(
		'admin'=>true,
		'owner'=>true,
		'default'=>null
		);
	}
	
	public static function getCopList($obj,$obj_id)
	{
		global $sql;
		$query = sprintf ('SELECT id_cop,cop_name,cop_file,cop_hdr FROM cms_obj_photos WHERE cop_obj=%s AND cop_obj_id=%d',
			$sql->t($obj),
			$obj_id);
		$dataset = $sql->query_all($query);
		return $dataset!==false?$dataset:array();  
	}	

	function ajxGlrCopList()
	{
		global $sql;
		$checkRule = array();
		$checkRule[] = array('obj'  , '.');
		$checkRule[] = array('obj_id'  , '/^\d+/');
		$checkResult = checkForm($_POST,$checkRule,$this->hasRight());
		if (count($checkResult)==0)
		{
			$query = sprintf ('SELECT id_cop,cop_name,cop_file,cop_hdr FROM cms_obj_photos WHERE cop_obj=%s AND cop_obj_id=%d',
				$sql->t($_POST['obj']),
				$_POST['obj_id']);
			$dataset = $sql->query_all($query);
			return json_encode(array('r'=>'t','d'=>$dataset!==false?$dataset:array()));
		}
		return json_encode(array('error'=>$checkResult));   
	}	
	
	function ajxGlrCpgNameUpd()
	{
		global $sql;
		$checkRule = array();
		$checkRule[] = array('id_cop'  , '/^\d+/');
		$checkRule[] = array('cop_name', '');
		$checkResult = checkForm($_POST,$checkRule,$this->hasRight());
		if (count($checkResult)==0)
		{
			$query = sprintf ('UPDATE cms_obj_photos SET cop_name = %s WHERE id_cop = %d;', 
			$sql->t($_POST['cop_name']),
			$_POST['id_cop']);
			$res_count = $sql->command($query);
			return json_encode($res_count>0?'t':'f');
		}
		return json_encode(array('error'=>$checkResult));   
	}	

	function ajxGlrCpgDrp()
	{
		global $sql;
		$checkRule = array();
		$checkRule[] = array('id_cop', '/^\d+/');
		$checkResult = checkForm($_POST,$checkRule,$this->hasRight());
		if (count($checkResult)==0)
		{
			$query =  sprintf('DELETE FROM cms_obj_photos WHERE id_cop = %d RETURNING cop_file;',
				$_POST['id_cop']);
			$result = $sql->query_first_row($query);
			if ($result!=false) {
				$filename = $result[0];
				$filenameext = '';
				$path_parts = pathinfo($this->imgglrpath.$filename);
				if ($path_parts['extension']!='jpg' && $path_parts['extension']!='png' && $path_parts['extension']!='gif') $filenameext = '.jpg';
				@unlink($this->imgglrpath.$filename);
				@array_map('unlink',glob($this->imgglrpath.'*/'.$filename.$filenameext));
				$res = 't';
				return json_encode($res);
			} else $checkResult['db'] = 'mstk';
		}
		return json_encode(array('error'=>$checkResult));   
	}  
	
	function ajxGlrIUpload() 
	{
		global $sql,$page;
		$res_msg = ''; $res_id_cop = 0; $res_cop_file = '';
		$checkRule = array();
		$checkRule[] = array('obj'  , '.');
		$checkRule[] = array('obj_id'  , '/^\d+/');
		$checkResult = checkForm($_POST,$checkRule,$this->hasRight());
		if (count($checkResult)==0)
		{
			if (isset($_FILES['file'])?$_FILES['file']['tmp_name']:false)
			{
				$upl = $_FILES['file'];
				if (is_uploaded_file($upl['tmp_name']))
				{
					if ($upl['size']>0)
					{
						$file_name = mb_strtolower($upl['name']);
						$file_ext = str_replace('.','',mb_substr($file_name,mb_strrpos($file_name,'.')));

                        $max_width = 1200;
                        $max_height = 1200;
                        $imginfo = false;

						if ($file_ext=='jpg' || $file_ext=='gif' || $file_ext=='png')
						{

                            if ($file_ext=='jpg' || $file_ext=='png')
							try {
								$imgRszr = new ImgResizer();
								$dst = $imgRszr->simpleResize($upl['tmp_name'],$max_width,$max_height,0);
								$imginfo = $imgRszr->imginfo;
							} catch(Exception $e) {}

                            if ($file_ext=='gif') {
                                $imginfo = @getimagesize($upl['tmp_name']);
                            }

							if ($imginfo!==false)
							{
								$query = sprintf ('INSERT INTO cms_obj_photos(cop_obj, cop_obj_id) VALUES(%s,%d) RETURNING id_cop;',
									$sql->t($_POST['obj']),
									$_POST['obj_id']);
								$result = $sql->query_first_row($query); $res = $result[0];
								if ($res>0)
								{
									$id_cop = $res;
									$copfile = $id_cop.'.'.$file_ext;
									$pathstr = $this->imgglrpath.$copfile;
									$dirpath = dirname($pathstr);
									if (!file_exists($dirpath)) mkdir($dirpath,0755,true);
									try {
                                        if ($file_ext=='jpg') {
                                            imagejpeg($dst,$pathstr,90);
                                            imagedestroy($dst);
                                        }
                                        if ($file_ext=='png') {
                                            imagepng($dst,$pathstr,9);
                                            imagedestroy($dst);
                                        }
                                        if ($file_ext=='gif') {
                                            rename($upl['tmp_name'],$pathstr);
                                            //ImagePng($dst,$pathstr,9);
                                            //imagedestroy($dst);
                                        }
                                    } catch (Exception $e) {
										$res_msg = 'Не удалось преобразовать фото!';
									}

									$query = sprintf ('UPDATE cms_obj_photos SET cop_file = %s WHERE id_cop = %d;', 
										$sql->t($copfile),
										$id_cop);
									$res_count = $sql->command($query);					
									$res_id_cop = $id_cop;
									$res_cop_file = $copfile;
								} else $res_msg = 'Не удалось сохранить фото!';


							} else
							{
								$res_msg = 'Неверный формат файла!';
							}             

						}		
						else $res_msg = 'Неверный формат файла! Поддерживаются форматы: .jpg';
					} else $res_msg = 'Файл пуст!';
				} else $res_msg = 'Не тот файл!';
			} else $res_msg = 'Файл не передан!';
		} else $res_msg = 'Неверные значения!';

		return json_encode(array(
		'status'=> $res_msg==''?0:-1,
		'msg'   => $res_msg,
		'id_cop'=> $res_id_cop,
		'cop_file'=> $res_cop_file
		));
	}
  
}
