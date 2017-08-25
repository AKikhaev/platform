<?php # Галлереи раздела

class Pg_Gallery extends PgUnitAbstract {
	public $imgglrpath = 'img/gallery/';
	
	public function initAjx()
	{
		global $page;
		return array(
            '_glrdrp' => array(
                'func' => 'ajxGlrDrp',
                'object' => $this),
            '_glrnew' => array(
                'func' => 'ajxGlrNew',
                'object' => $this),
            '_glrupl' => array(
                'func' => 'ajxGlrUpload',
                'object' => $this),
            '_glriupl' => array(
                'func' => 'ajxGlrIUpload',
                'object' => $this),
            '_glrcpghdr' => array(
                'func' => 'ajxGlrChHdr',
                'object' => $this),
            '_glrcpgdrp' => array(
                'func' => 'ajxGlrCpgDrp',
                'object' => $this),
            '_glrnameupd' => array(
                'func' => 'ajxGlrNameUpd',
                'object' => $this),
            '_glrdescupd' => array(
                'func' => 'ajxGlrDescUpd',
                'object' => $this),
            '_glrcpgnmupd' => array(
                'func' => 'ajxGlrCpgNameUpd',
                'object' => $this),
            '_glrlist' => array(
                'func' => 'ajxGlrList',
                'object' => $this),
            '_glrsetordr' => array(
                'func' => 'ajxGlrSetOrder',
                'object' => $this),
            '_tst' => array(
                'func' => 'ajxTst',
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

	public static function getGalleryPhotos($glr_id){
		global $sql;
		$query = sprintf ('select id_cgp,cgp_name,cgp_file from cms_gallery_photos where cgp_enabled and cgp_glr_id=%d order by id_cgp', 
		$glr_id);
        $res = $sql->query_all($query);
		return $res?:array();
	}

	public static function getLastGalleryPhotos() // Фото последней галлереи, в которой >3 фото
	{
		global $sql;
		$res = array('p'=>array());
		$query = 'SELECT z.* FROM cms_galeries z WHERE z.id_glr=(SELECT a.id_glr FROM cms_galeries a inner join cms_gallery_photos b on (a.id_glr=b.cgp_glr_id) where a.glr_enabled and b.cgp_enabled group by a.glr_sort,a.id_glr HAVING count(*)>3 order by a.glr_sort desc LIMIT 1)';
		$gallery = $sql->query_first_assoc($query);
		if ($gallery!==false) {
			$query = sprintf ('select cgp_name,cgp_file from cms_gallery_photos where cgp_enabled and cgp_glr_id=%d', 
			$gallery['id_glr']);
			$dataset = $sql->query_all($query);
			if ($dataset!=false) $res['p'] = $dataset;
			$res['g']=$gallery;
		}
		return $res;
	}

	public static function getSecGalleries() // Фото привязанной галлереи
	{
		global $sql,$page;
		$res = array('g'=>array(),'p'=>array());
		$query = sprintf('SELECT z.* FROM cms_galeries z INNER JOIN cms_gallery_sec s ON (z.id_glr=s.glr_id) WHERE z.glr_enabled AND s.sec_id=%d ORDER BY z.glr_sort',
			$page->page['section_id']
		);
		$galleries = $sql->query_all($query);
		if ($galleries!==false) {
			$res['g'] = $galleries;
			foreach ($galleries as $gallery) {
				$query = sprintf ('select id_cgp,cgp_name,cgp_file from cms_gallery_photos where cgp_enabled and cgp_glr_id=%d order by id_cgp', 
				$gallery['id_glr']);
				$dataset = $sql->query_all($query);
				if ($dataset!=false) $res['p'][$gallery['id_glr']] = $dataset;
			}
		}
		return $res;
	}

	public function ajxGlrDrp()
	{
		global $sql;
		$checkRule = array();
		$checkRule[] = array('id_glr'  , '/^\d+/');
		$checkResult = checkForm($_POST,$checkRule,$this->hasRight());
		if (count($checkResult)==0)
		{
			$query = sprintf('SELECT glr_sys FROM cms_galeries WHERE id_glr = %d;',
				$_POST['id_glr']);
			$glrSys = $sql->query_first_assoc($query);
			if ($glrSys===false || ($glrSys!==false?$glrSys['glr_sys'] === 'f':false)) {
				$query = sprintf ('SELECT cgp_file as file FROM cms_gallery_photos WHERE cgp_glr_id = %d;',
				$_POST['id_glr']);
				$photolist  = $sql->query_all($query);
				$query = sprintf('DELETE FROM cms_galeries WHERE id_glr = %d; UPDATE cms_sections SET sec_glr_id = 0 WHERE sec_glr_id = %d; DELETE FROM cms_gallery_photos WHERE cgp_glr_id = %d',
				$_POST['id_glr'], $_POST['id_glr'], $_POST['id_glr']);
				$res_count = $sql->command($query);
				if ($res_count>0) foreach ($photolist as $photo) 
				{
					$filename = $photo['file'];
					$filenameext = '';
					$path_parts = pathinfo($this->imgglrpath.$filename);
					if ($path_parts['extension'] !== 'jpg' && $path_parts['extension'] !== 'png') $filenameext = '.jpg';
					@unlink($this->imgglrpath.$filename);
					@array_map('unlink',glob($this->imgglrpath.'*/'.$filename));
				}
				return json_encode('t');
			} else 
				return json_encode(array('error'=>array('f'=>'sys','s'=>'t')));
		}
		return json_encode(array('error'=>$checkResult));
	}

	public function ajxGlrChHdr()
	{
		global $sql;
		$checkRule = array();
		$checkRule[] = array('id_glr'  , '/^\d+/');
		$checkRule[] = array('id_cgp', '/^\d+/');
		$checkResult = checkForm($_POST,$checkRule,$this->hasRight());
		if (count($checkResult)==0)
		{
			$query = sprintf ('UPDATE cms_galeries SET glr_file = (SELECT cgp_file FROM cms_gallery_photos WHERE id_cgp=%d) WHERE id_glr = %d;', 
				$_POST['id_cgp'],
				$_POST['id_glr']);
			$res_count = $sql->command($query);	
			return json_encode($res_count>0?'t':'f');
		}
		return json_encode(array('error'=>$checkResult));   
	}

	public function ajxGlrList()
	{
		global $sql;
		$checkRule = array();
		$checkResult = checkForm($_POST,$checkRule,$this->hasRight());
		if (count($checkResult)==0)
		{
			$query = sprintf ('SELECT id_glr as k,glr_name as v FROM cms_galeries WHERE NOT glr_sys AND glr_enabled ORDER BY glr_sort');
			$dataset = $sql->query_all($query);
			return json_encode($dataset!==false?array('r'=>'t','d'=>$dataset):'f');
		}
		return json_encode(array('error'=>$checkResult));   
	}
	
	public function ajxGlrSetOrder()
	{
		global $sql;
		$checkRule = array();
		$checkRule[] = array('glr_order', '.');
		$checkResult = checkForm($_POST,$checkRule,$this->hasRight());
		if (count($checkResult)==0)
		{
			$i = 0; $f = true;
			foreach($_POST['glr_order'] as $id_glr) {
				$query = sprintf ('UPDATE cms_galeries SET glr_sort=%d WHERE id_glr = %d;', 
					++$i,
					@(int)$id_glr);
				$f = $sql->command($query) && $f;
			}
			return json_encode($f>0?'t':'f');
		}
		return json_encode(array('error'=>$checkResult));   
	}	
	
	public function ajxGlrNew()
	{
		global $sql;
		$checkRule = array();
		$checkRule[] = array('glr_name', '.');
		$checkRule[] = array('glr_type', '/^\d+/');
		$checkResult = checkForm($_POST,$checkRule,$this->hasRight());
		if (count($checkResult)==0)
		{
			$query = sprintf ('INSERT INTO cms_galeries(glr_sec_id,glr_name,glr_type)VALUES(%d,%s,%d) RETURNING id_glr;',
				(isset($this->Injected)?$page->page['section_id']:0),
				$sql->t($_POST['glr_name']),
				$_POST['glr_type']);
			$result = $sql->query_first_row($query); $res = $result[0]; 
			return json_encode($res>0?$res:'f');
		}
		return json_encode(array('error'=>$checkResult));   
	}
	
	public function ajxGlrNameUpd()
	{
		global $sql;
		$checkRule = array();
		$checkRule[] = array('id_glr'  , '/^\d+/');
		$checkRule[] = array('glr_name', '.');
		$checkResult = checkForm($_POST,$checkRule,$this->hasRight());
		if (count($checkResult)==0)
		{
			$query = sprintf ('UPDATE cms_galeries SET glr_name = %s WHERE id_glr = %d;', 
			  $sql->t($_POST['glr_name']),
			  $_POST['id_glr']
			  );
			$res_count = $sql->command($query);

			return json_encode($res_count>0?'t':'f');
		}
		return json_encode(array('error'=>$checkResult));   
	}

    public function ajxGlrDescUpd()
    {
        global $sql;
        $checkRule = array();
        $checkRule[] = array('id_glr'  , '/^\d+/');
        $checkRule[] = array('glr_desc', '');
        $checkResult = checkForm($_POST,$checkRule,$this->hasRight());
        if (count($checkResult)==0)
        {
            $query = sprintf ('UPDATE cms_galeries SET glr_desc = %s WHERE id_glr = %d;',
                $sql->t($_POST['glr_desc']),
                $_POST['id_glr']);
            $res_count = $sql->command($query);
            return json_encode($res_count>0?'t':'f');
        }
        return json_encode(array('error'=>$checkResult));
    }

	public function ajxGlrCpgNameUpd()
	{
		global $sql;
		$checkRule = array();
		$checkRule[] = array('id_cgp'  , '/^\d+/');
		$checkRule[] = array('cgp_name', '');
		$checkResult = checkForm($_POST,$checkRule,$this->hasRight());
		if (count($checkResult)==0)
		{
			$query = sprintf ('UPDATE cms_gallery_photos SET cgp_name = %s WHERE id_cgp = %d;', 
			$sql->t($_POST['cgp_name']),
			$_POST['id_cgp']);
			$res_count = $sql->command($query);
			return json_encode($res_count>0?'t':'f');
		}
		return json_encode(array('error'=>$checkResult));   
	}	

	public function ajxGlrCpgDrp()
	{
		global $sql;
		$checkRule = array();
		$checkRule[] = array('id_cgp', '/^\d+/');
		$checkResult = checkForm($_POST,$checkRule,$this->hasRight());
		if (count($checkResult)==0)
		{
			$query =  sprintf('DELETE FROM cms_gallery_photos WHERE id_cgp = %d AND (SELECT count(*) FROM cms_gallery_photos WHERE cgp_glr_id=cgp_glr_id)>1 RETURNING cgp_file;',
				$_POST['id_cgp']);
			$result = $sql->query_first_row($query);
			if ($result!=false) {
				$filename = $result[0];
				$filenameext = '';
				$path_parts = pathinfo($this->imgglrpath.$filename);
				if ($path_parts['extension'] !== 'jpg' && $path_parts['extension'] !== 'png') $filenameext = '.jpg';
				@unlink($this->imgglrpath.$filename);
				@array_map('unlink',glob($this->imgglrpath.'*/'.$filename.$filenameext));
				$res = 't';
				return json_encode($res);
			} else $checkResult['db'] = 'mstk';
		}
		return json_encode(array('error'=>$checkResult));   
	}  
	
	public function ajxTst() {
		if (!$this->hasRight()) exit;
		// $srcdir = '/home/p/parablru/sf/public_html/tmp/';
		// $num = 0;
		// foreach (scandir($srcdir) as $filename) if ($filename!='.' && $filename!='..' && $num++<30) {
			// $this->FileToGallery(1,$srcdir.$filename);
			// @unlink($srcdir.$filename);
			// echo $filename.'<br/>';
		// }
	}
	
	public function FileToGallery($id_glr, $filepath) {
		global $sql;
		$rstat = false; $res_msg = '';

		$file_name = mb_strtolower($filepath);
		$file_ext = str_replace('.','',mb_substr($file_name,mb_strrpos($file_name,'.')));
		if ($file_ext === 'jpg')
		{
			$res_stat = 1;
			$max_width = 1200;
			$max_height = 1200;
			$imginfo = false;
			
			try {
				$imgRszr = new ImgResizer();
				$dst = $imgRszr->simpleResize($filepath,$max_width,$max_height,0);
				$imginfo = $imgRszr->imginfo;						
			} catch(Exception $e) {}									

			if ($imginfo!==false)
			{
				$res_stat = 3; 
				$query = sprintf ('INSERT INTO cms_gallery_photos(cgp_glr_id, cgp_name) VALUES(%d,%s) RETURNING id_cgp;',
				$id_glr,
				$sql->t(''));
				$result = $sql->query_first_row($query); $res = $result[0];
				if ($res>0)
				{
					$id_cgp = $res;
					$cgpfile = $id_cgp.'.jpg';
					$pathstr = $this->imgglrpath.$cgpfile;
					$dirpath = dirname($pathstr);
					if (!file_exists($dirpath)) mkdir($dirpath,0755,true);        
					imagejpeg($dst,$pathstr,90);

					$query = sprintf ('UPDATE cms_gallery_photos SET cgp_file = %s WHERE id_cgp = %d;', 
						$sql->t($cgpfile),
						$id_cgp);
					$res_count = $sql->command($query);				
					$rstat = $res_count>0;
				} else $res_msg = 'Не удалось сохранить фото!';

				imagedestroy($dst);               
				/**/
						
			} else $res_msg = 'Неверный формат файла!';   
		} else $res_msg = 'Неподдерживаемый формат файла!';


		return $rstat;	
	}
	
	public function ajxGlrIUpload()
	{
		global $sql,$page;
		$res_msg = ''; $res_stat = 0; $res_id_glr = 0; $res_id_cgp = 0; $res_cgp_file = '';
		$checkRule = array();
		$checkRule[] = array('glr'  , '/^\d+/');
		$checkResult = checkForm($_GET,$checkRule,$this->hasRight());
		if (count($checkResult)==0)
		{
			if (isset($_FILES['file'])?$_FILES['file']['tmp_name']:false)
			{
				$upl = $_FILES['file'];
				if (is_uploaded_file($upl['tmp_name']))
				{
					if ($upl['size']>0)
					{
						if ($this->hasRight()) 
						{
							$file_name = mb_strtolower($upl['name']);
							$file_ext = str_replace('.','',mb_substr($file_name,mb_strrpos($file_name,'.')));
							$id_glr = $_GET['glr'];
							
							if ($file_ext === 'jpg')
							{
								$res_stat = 1;
								$max_width = 1200;
								$max_height = 1200;
								$imginfo = false;
								
								try {
									$imgRszr = new ImgResizer();
									$dst = $imgRszr->simpleResize($upl['tmp_name'],$max_width,$max_height,0);
									$imginfo = $imgRszr->imginfo;						
								} catch(Exception $e) {}									

								if ($imginfo!==false)
								{
									$res_stat = 2;

									if ($id_glr>0)
									{
										$res_stat = 3; 
										$query = sprintf ('INSERT INTO cms_gallery_photos(cgp_glr_id, cgp_name) VALUES(%d,%s) RETURNING id_cgp;',
											$id_glr,
											$sql->t(''));
										$result = $sql->query_first_row($query); $res = $result[0];
										if ($res>0)
										{
											$res_stat = 4;
											$id_cgp = $res;
											$cgpfile = $id_cgp.'.jpg';
											$pathstr = $this->imgglrpath.$cgpfile;
											$dirpath = dirname($pathstr);
											if (!file_exists($dirpath)) mkdir($dirpath,0755,true);    
											imagejpeg($dst,$pathstr,90);

											$query = sprintf ('UPDATE cms_gallery_photos SET cgp_file = %s WHERE id_cgp = %d;', 
												$sql->t($cgpfile),
												$id_cgp);
											$res_count = $sql->command($query);					
											$res_id_glr = $id_glr;
											$res_id_cgp = $id_cgp;
											$res_cgp_file = $cgpfile;
										} else $res_msg = 'Не удалось сохранить фото!';
									} else $res_msg = 'Не удалось сохранить галерею!';

									imagedestroy($dst);               
									/**/
											
								} else
								{
									$res_msg = 'Неверный формат файла!';
								}             

							}
							elseif ($file_ext === 'flv' || $file_ext === 'mp4' || $file_ext === 'mp3')
							{
								#$res_stat = 1;
								$res_stat = 2;

								if ($id_glr>0)
								{
									$res_stat = 3; 
									$query = sprintf ('INSERT INTO cms_gallery_photos(cgp_glr_id, cgp_name) VALUES(%d,%s) RETURNING id_cgp;',
									$id_glr,
									$sql->t(''));
									$result = $sql->query_first_row($query); $res = $result[0];
									if ($res>0) 
									{
										$res_stat = 4;
										$id_cgp = $res;
										$cgpfile = $id_cgp.'.'.$file_ext;
										$pathstr = $this->imgglrpath.$cgpfile;
										$dirpath = dirname($pathstr);
										if (!file_exists($dirpath)) mkdir($dirpath,0755,true);    
										#var_dump_($dirpath);     exit;    
										copy($upl['tmp_name'],$pathstr); 

										$query = sprintf ('UPDATE cms_gallery_photos SET cgp_file = %s WHERE id_cgp = %d;', 
											$sql->t($cgpfile),
											$id_cgp);
										$res_count = $sql->command($query);				
										if ($_POST['id_glr']==0) {   
											$query = sprintf ('UPDATE cms_galeries SET glr_file = %s WHERE id_glr = %d;', 
												$sql->t($cgpfile),
												$id_glr);
											$res_count = $sql->command($query);
										}
										$res_id_glr = $id_glr;
										$res_id_cgp = $id_cgp;
										$res_cgp_file = $cgpfile;
									} else $res_msg = 'Не удалось сохранить фото!';
								} else $res_msg = 'Не удалось сохранить галерею!';

							}			
							else $res_msg = 'Неверный формат файла! Поддерживаются форматы: .jpg';
						} else $res_msg = 'У вас нет прав!';
					} else $res_msg = 'Файл пуст!';
				} else $res_msg = 'Не тот файл!';
			} else $res_msg = 'Файл не передан!';
		} else $res_msg = 'Неверные значения!';
		return json_encode(array(
		'status'=> $res_stat,
		'msg'   => $res_msg,
		'id_glr'=> $res_id_glr,
		'id_cgp'=> $res_id_cgp,
		'cgp_file'=> $res_cgp_file
		));
	}
	
	public function ajxGlrUpload()
	{
		global $sql,$page;
		$res_msg = ''; $res_stat = 0; $res_id_glr = 0; $res_id_cgp = 0; $res_cgp_file = '';
		$JsHttpRequest = new JsHttpRequest('UTF-8');
		$checkRule = array();
		$checkRule[] = array('id_glr'  , '/^\d+/');
		$checkRule[] = array('glr_type', '/^\d+/');
		$checkRule[] = array('glrnameinp', '.');
		$checkResult = checkForm($_POST,$checkRule,$this->hasRight());
		if (count($checkResult)==0)
		{
			if (isset($_FILES['uplfile'])?$_FILES['uplfile']['tmp_name']:false)
			{
				$upl = $_FILES['uplfile'];
				if (is_uploaded_file($upl['tmp_name']))
				{
					if ($upl['size']>0)
					{
						if ($this->hasRight()) {
							$file_name = mb_strtolower($upl['name']);
							$file_ext = str_replace('.','',mb_substr($file_name,mb_strrpos($file_name,'.')));
							if ($file_ext === 'jpg')
							{
								$res_stat = 1;
								$max_width = 1200;
								$max_height = 1200;
								$imginfo = false;
								
								try {
									$imgRszr = new ImgResizer();
									$dst = $imgRszr->simpleResize($upl['tmp_name'],$max_width,$max_height,0);
									$imginfo = $imgRszr->imginfo;						
								} catch(Exception $e) {}									

								if ($imginfo!==false)
								{
									$res_stat = 2;
									$id_glr = $_POST['id_glr'];

									if ($_POST['id_glr']==0) {
										$query = sprintf ('INSERT INTO cms_galeries(glr_sec_id,glr_name,glr_type)VALUES(%d,%s,%d) RETURNING id_glr;',
											(isset($this->Injected)?$page->page['section_id']:0),
											$sql->t($_POST['glrnameinp']),
											$_POST['glr_type']);
										$result = $sql->query_first_row($query); $res = $result[0];
										if ($res>0) $id_glr = $res; 
									} else {
										$query = sprintf ('UPDATE cms_galeries SET glr_name = %s WHERE id_glr = %d;', 
										  $sql->t($_POST['glrnameinp']),
										  $_POST['id_glr']
										  );
										$res_count = $sql->command($query);
									}
									if ($id_glr>0)
									{
										$res_stat = 3; 
										$query = sprintf ('INSERT INTO cms_gallery_photos(cgp_glr_id, cgp_name) VALUES(%d,%s) RETURNING id_cgp;',
											$id_glr,
											$sql->t(''));
										$result = $sql->query_first_row($query); $res = $result[0];
										if ($res>0) 
										{
											$res_stat = 4;
											$id_cgp = $res;
											$cgpfile = $id_cgp.'.jpg';
											$pathstr = $this->imgglrpath.$cgpfile;
											$dirpath = dirname($pathstr);
											if (!file_exists($dirpath)) mkdir($dirpath,0755,true);    
											#var_dump_($dirpath);     exit;    
											imagejpeg($dst,$pathstr,90);

											$query = sprintf ('UPDATE cms_gallery_photos SET cgp_file = %s WHERE id_cgp = %d;', 
												$sql->t($cgpfile),
												$id_cgp);
											$res_count = $sql->command($query);					
											if ($_POST['id_glr']==0) {   
												$query = sprintf ('UPDATE cms_galeries SET glr_file = %s WHERE id_glr = %d;', 
													$sql->t($cgpfile),
													$id_glr);
												$res_count = $sql->command($query);
											}
											$res_id_glr = $id_glr;
											$res_id_cgp = $id_cgp;
											$res_cgp_file = $cgpfile;
										} else $res_msg = 'Не удалось сохранить фото!';
									} else $res_msg = 'Не удалось сохранить галерею!';

									imagedestroy($dst);               
									/**/
											
								} else
								{
									$res_msg = 'Неверный формат файла!';
								}             

							}
							elseif ($file_ext === 'flv' || $file_ext === 'mp4' || $file_ext === 'mp3')
							{
								#$res_stat = 1;
								$res_stat = 2;
								$id_glr = $_POST['id_glr'];

								if ($_POST['id_glr']==0) {
								$query = sprintf ('INSERT INTO cms_galeries(glr_sec_id,glr_name,glr_type)VALUES(%d,%s,%d) RETURNING id_glr;',
									$page->page['section_id'],
									$sql->t($_POST['glrnameinp']),
									$_POST['glr_type']);
								$result = $sql->query_first_row($query); $res = $result[0];
								if ($res>0) $id_glr = $res; 
								} else {
								$query = sprintf ('UPDATE cms_galeries SET glr_name = %s WHERE id_glr = %d;', 
									$sql->t($_POST['glrnameinp']),
									$_POST['id_glr']);
								$res_count = $sql->command($query);
								}
								if ($id_glr>0)
								{
									$res_stat = 3; 
									$query = sprintf ('INSERT INTO cms_gallery_photos(cgp_glr_id, cgp_name) VALUES(%d,%s) RETURNING id_cgp;',
									$id_glr,
									$sql->t(''));
									$result = $sql->query_first_row($query); $res = $result[0];
									if ($res>0) 
									{
										$res_stat = 4;
										$id_cgp = $res;
										$cgpfile = $id_cgp.'.'.$file_ext;
										$pathstr = $this->imgglrpath.$cgpfile;
										$dirpath = dirname($pathstr);
										if (!file_exists($dirpath)) mkdir($dirpath,0755,true);    
										#var_dump_($dirpath);     exit;    
										copy($upl['tmp_name'],$pathstr); 

										$query = sprintf ('UPDATE cms_gallery_photos SET cgp_file = %s WHERE id_cgp = %d;', 
											$sql->t($cgpfile),
											$id_cgp);
										$res_count = $sql->command($query);				
										if ($_POST['id_glr']==0) {   
											$query = sprintf ('UPDATE cms_galeries SET glr_file = %s WHERE id_glr = %d;', 
												$sql->t($cgpfile),
												$id_glr);
											$res_count = $sql->command($query);
										}
										$res_id_glr = $id_glr;
										$res_id_cgp = $id_cgp;
										$res_cgp_file = $cgpfile;
									} else $res_msg = 'Не удалось сохранить фото!';
								} else $res_msg = 'Не удалось сохранить галерею!';

							}			
							else $res_msg = 'Неверный формат файла! Поддерживаются форматы: .jpg';
						} else $res_msg = 'У вас нет прав!';
					} else $res_msg = 'Файл пуст!';
				} else $res_msg = 'Не тот файл!';
			} else $res_msg = 'Файл не передан!';
		} else $res_msg = 'Неверные значения!';
		$GLOBALS['_RESULT'] = array(
		'status'=> $res_stat,
		'msg'   => $res_msg,
		'id_glr'=> $res_id_glr,
		'id_cgp'=> $res_id_cgp,
		'cgp_file'=> $res_cgp_file
		);
		return $JsHttpRequest->_obHandler('');
	}
	
	public static function buildLevelSiteMap(&$putInto,$parentId,$parentUrlFull,$showHidden = false,$Injected = false) {
		return; // Не надо на Georghram
		global $sql;
		$query = sprintf('select -1 as section_id,glr_sec_id as sec_parent_id,%s || \'g\' || id_glr || \'/\' as sec_url_full,\'g\' || id_glr as sec_url,glr_name as sec_nameshort,%s || glr_name as sec_namefull,\'\' as sec_imgfile, true as sec_showinmenu,false as sec_openfirst,false as sec_to_news,true as sec_enabled,\'\' as sec_keywords,\'\' as sec_description, \'\' as sec_units, false as sec_hidden from cms_galeries where '.($Injected?'glr_sec_id='.$parentId.' and':'').' not glr_sys order by glr_sort;',
			$sql->t($parentUrlFull),
			$sql->t('Фото: ')
			);
		$dataset = $sql->query_all($query);
		if ($dataset!==false) foreach ($dataset as $dataitem) $putInto[] = $dataitem;
	}
	
	public function render()
	{
		global $sql,$page;
		$res = '';
		$editMode = $this->hasRight() && core::$inEdit;
		$pageLinkUri = ($editMode?'_/':'').$page->pageMainUri;
		if (isset($this->Injected) && $page->pageUri!==$page->pageMainUri) $page->page['sec_content'] = '<!-- -->';
		if (count($this->unitParam)==1?preg_match('/^g\d{1,3}$/',$this->unitParam[0])==1:false)
		{
			$glrId = substr($this->unitParam[0],1);
			$query_where = $editMode?(isset($this->Injected)?'glr_sec_id='.$page->page['section_id'].' and':''):' glr_enabled and not glr_sys and'.(isset($this->Injected)?' glr_sec_id='.$page->page['section_id'].' and':'');
			$query = sprintf ('select * from cms_galeries where '.$query_where.' id_glr=%d;', 
			$glrId);
			$dataset = $sql->query_all($query);
			if (count($dataset)==0 || $dataset===false) throw new CmsException('page_not_found');
			$glrItem = $dataset[0];
			$query = sprintf ('select * from cms_gallery_photos where '.($editMode?'':'cgp_enabled and').' cgp_glr_id=%d order by id_cgp;', 
			$glrId);
			$glrPhotos = $sql->query_all($query);
			
			require $this->view('item');
			
			if ($editMode) $res .= '<script type="text/javascript">var glra='.json_encode(array(
				'glrs'=>array($glrItem),
				'glr_ph'=>array($glrItem['id_glr']=>$glrPhotos)
			)).';</script><script type="text/javascript" src="/akcms/js/v1/pg_glr_ed.js"></script>';
		} elseif ((count($this->unitParam)==0) || (count($this->unitParam)==1?preg_match('/^\d{1,3}$/',$this->unitParam[0])==1:false))
		{
			$pgNum = 1;	if (count($this->unitParam)==1) $pgNum = $this->unitParam[0]>0?$this->unitParam[0]:1;
			$pgSize = 12;
			$query_where = $editMode?(isset($this->Injected)?'where glr_sec_id='.$page->page['section_id']:''):'where glr_enabled and not glr_sys and glr_file<>\'\''.(isset($this->Injected)?' and glr_sec_id='.$page->page['section_id']:'');
			$query = 'select count(*) as totalrecords from cms_galeries '.$query_where;
			$totalset = $sql->query_first_assoc($query); $countRecords = $totalset['totalrecords'];
			$query = sprintf ('select * from cms_galeries '.$query_where.' order by glr_sort desc LIMIT %d OFFSET (%d-1)*%d;',
				$pgSize,
				$pgNum,
				$pgSize);
			$glrItms = $sql->query_all($query);
			$pgNums = ceil($countRecords/$pgSize);
			if ($glrItms!==false && ($pgNum<1 || $pgNum>$pgNums))
				throw new CmsException('page_not_found');
				
			require $this->view('list');

            /*
			if ($pgNums>1)
			$res .= '<div class="pager">'.makePager($countRecords, $pgSize, $pgNum, '/'.$pageLinkUri.'{pg}/').'</div>';
            */

            if ($pgNums>1) {
                $pgNumPrev = $pgNum-1;
                $pgNumNext = $pgNum+1;
                $res .= '<div id="pag">';
                if ($pgNumPrev>=1)
                    $res .= '<div id="pagl"><a href="/'.$pageLinkUri.($pgNumPrev==1?'':$pgNumPrev).'">< Предыдущая страница</a></div>';
                if ($pgNumNext<=$pgNums)
                    $res .= '<div id="pagr"><a href="/'.$pageLinkUri.$pgNumNext.'">Следующая страница ></a></div>';
                $res .= '<div class="cb"></div></div>';
            }

			if ($editMode) $res .= '<script type="text/javascript">var glra={\'glrs\':[],\'glr_ph\':[]};</script><script type="text/javascript" src="/akcms/js/v1/pg_glr_ed.js"></script>';
		} else throw new CmsException('page_not_found');
		return $res;
	}
  
}
