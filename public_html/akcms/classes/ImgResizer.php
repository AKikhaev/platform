<?php

class ImgMetaData {
	static function getMetaByName($fileName,$prePath = '../') {
		$fileName = str_replace('..', '_', $fileName);	
		$pathstrMeta = $prePath.'s/_metadata/'.$fileName.'.dat';
		if (file_exists($pathstrMeta)) {
			try {
				$fileDataStr = file_get_contents($pathstrMeta);
				$fileData = explode('|',$fileDataStr);
				return $fileData;
			} catch (Exception $e) {
				 return false;
			}			
		} else return false;
	}
}

/* Alexander Kikhaev, 2012
Modes:
*/
class ImgResizer {
	public $res_width;
	public $res_height;
	public $res_isoriginal;
	public $rezip = false;
	public $imginfo;


    /** Простое масштабирование
     * @param $pathstrOrgn
     * путь к оргиналу
     * @param $max_width
     * максимальная ширина
     * @param $max_height
     * максимальна высота
     * @param $mode
     * режим масштабирования:
     *
     *  0 - Пропорционально масштабировать. Изображение стрнет указанных размеров или меньше по одной из сторон.
     *
     *  1 - Пропорционально привести к размеру, излишки ОТРЕЗАТЬ. Изображение странет указанных размеров

     * -1 - Пропорционально привести к размеру, изображение стрнет указанных размеров (ВНУТРИ). Излишки заполнены белым.
     *
     *  2 - Не пропорционально масштабировать
     *
     *  3 - Пропорционально масштабировать. Изображение станет указанных размеров или больше по одной из сторон
     * @param null $effector
     * объект эффекта с методом ->apply($dst,$this->res_width,$this->res_height)
     * @param int $cntrX
     * центральная точка по x
     * @param int $cntrY
     * центральная точка по y
     * @return resource
     * Возвращает ссылку на изображение GD
     * @throws Exception
     */
	public function simpleResize($pathstrOrgn,$max_width,$max_height,$mode,$effector = null,$cntrX = -1,$cntrY = -1) {
		$this->res_isoriginal = false;
		$isflv = strpos($pathstrOrgn,'.flv')==strlen($pathstrOrgn)-4;
		$this->imginfo = array();
		if (file_exists($pathstrOrgn)) 
		{
			if ($isflv) {
				$movie = new ffmpeg_movie($pathstrOrgn, false);
				$frame = $movie->getNextKeyFrame();
				$this->imginfo[0] = $frame->getWidth();
				$this->imginfo[1] = $frame->getHeight();
				$this->imginfo['mime']='image/jpeg';
			} else $this->imginfo = @getimagesize($pathstrOrgn);
			if ($this->imginfo===false) throw new Exception('imgr_no_info');
		}
		else throw new Exception('imgr_orgn_not_exist');

		$width = $this->imginfo[0];
		$height = $this->imginfo[1];
		if ($max_width==-1 && $max_height==-1) {
			$max_width = $width;
			$max_height = $height;
		}
		if (empty($width) or empty($height) or empty($max_width) or empty($max_height)) throw new Exception('imgr_wrong_sizes');
		if ($this->imginfo['mime']==='image/jpeg') {
			if ($isflv) {
				$src0 = $frame->toGDImage();
				if ($width>=119 && $height>=89) {
					$src = imagecreatetruecolor(119,89);
					imagecopy($src, $src0, 0, 0, 0, 0, 119, 89);
					$width = 119; $height = 89;
				} else $src = $src0;
			}
			else $src = @imagecreatefromjpeg($pathstrOrgn);
			$this->imginfo['newmime']='image/jpeg';
		}
		elseif ($this->imginfo['mime']==='image/png') {
			$src = @imagecreatefrompng($pathstrOrgn);
			imagealphablending($src, false);
			imagesavealpha($src, true);
			$this->imginfo['newmime']='image/jpeg';
		}		
		elseif ($this->imginfo['mime']==='image/gif') {
			$src = @imagecreatefromgif($pathstrOrgn);
			imagealphablending($src, false);
			imagesavealpha($src, true);
			$this->imginfo['newmime']='image/jpeg';
		}		
		else throw new Exception('imgr_wrong_mime');
		$this->imginfo['newmime'] = $this->imginfo['mime'];

        $x_ratio = $max_width / $width;
        $y_ratio = $max_height / $height;

		if ($mode === 0) {
			if ( ($width <= $max_width) && ($height <= $max_height) ) {
				$tn_width = $width;
				$tn_height = $height;
			}
			else if (($x_ratio * $height) < $max_height) {
				$tn_height = ceil($x_ratio * $height);
				$tn_width = $max_width;
			}
			else {
				$tn_width = ceil($y_ratio * $width);
				$tn_height = $max_height;
			}  
			if ($max_width===$width && $max_height===$height && $this->imginfo['newmime']===$this->imginfo['mime']) { $dst=$src; $this->res_isoriginal = true; }
			else 
			{
				$dst = imagecreatetruecolor($tn_width,$tn_height);
				//imagefilledrectangle($dst,0,0,$tn_width,$tn_height,imagecolorallocate($dst, 255, 255, 255));
				imagecopyresampled($dst,$src,0,0,0,0,$tn_width,$tn_height,$width,$height);
			}
			$this->res_width = $tn_width;
			$this->res_height = $tn_height;
		} elseif ($mode === 3) {
            if ( ($width <= $max_width) && ($height <= $max_height) ) {
                $tn_width = $width;
                $tn_height = $height;
            }
            else if (($x_ratio * $height) >= $max_height) {
                $tn_height = ceil($x_ratio * $height);
                $tn_width = $max_width;
            }
            else {
                $tn_width = ceil($y_ratio * $width);
                $tn_height = $max_height;
            }
            if ($max_width===$width && $max_height===$height && $this->imginfo['newmime']===$this->imginfo['mime']) { $dst=$src; $this->res_isoriginal = true; }
            else
            {
                $dst = imagecreatetruecolor($tn_width,$tn_height);
                //imagefilledrectangle($dst,0,0,$tn_width,$tn_height,imagecolorallocate($dst, 255, 255, 255));
                imagecopyresampled($dst,$src,0,0,0,0,$tn_width,$tn_height,$width,$height);
            }
            $this->res_width = $tn_width;
            $this->res_height = $tn_height;
        } elseif ($mode === -1) {
			if ( ($width <= $max_width) && ($height <= $max_height) ) {
				$tn_width = $width;
				$tn_height = $height;
			}
			else if (($x_ratio * $height) < $max_height) {
				$tn_height = ceil($x_ratio * $height);
				$tn_width = $max_width;
			}
			else {
				$tn_width = ceil($y_ratio * $width);
				$tn_height = $max_height;
			}  
			if ($max_width===$width && $max_height===$height && $this->imginfo['newmime']===$this->imginfo['mime']) { $dst=$src; $this->res_isoriginal = true; }
			else 
			{
				$dst = imagecreatetruecolor($max_width, $max_height);
				imagefilledrectangle($dst,0,0,$max_width,$max_height,imagecolorallocate($dst, 255, 255, 255));
				imagecopyresampled($dst,$src,($max_width-$tn_width)/2, ($max_height-$tn_height)/2,0,0,$tn_width,$tn_height,$width,$height);
			}
			$this->res_width = $tn_width;
			$this->res_height = $tn_height;
		} elseif ($mode === 1) {
			if ($cntrX === -1) $cntrX = $width/2;
			if ($cntrY === -1) $cntrY = $height/2;

			$tn_width = ceil($y_ratio * $width);
			$tn_height = $max_height;
			$tn_cntrX = $y_ratio*$cntrX;
			$tn_cntrY = $y_ratio*$cntrY;
			$putX = $max_width/2-$tn_cntrX;
			$putY = 0;
			if ($putX>0) $putX = 0;
			if ($putX<$max_width-$tn_width) $putX = $max_width-$tn_width;
			$lngst=1;

			if ($tn_width < $max_width) {
				$tn_height = ceil($x_ratio * $height);
				$tn_width = $max_width;
				$tn_cntrX = $x_ratio*$cntrX;
				$tn_cntrY = $x_ratio*$cntrY;
				$putX = 0;
				$putY = $max_height/2-$tn_cntrY;
				if ($putY>0) $putY = 0;
				if ($putY<$max_height-$tn_height) $putY = $max_height-$tn_height;
				$lngst=2;
			}
			
			#$putX = ($max_width-$tn_width)/2;
			#$putY = ($max_height-$tn_height)/2;
            /*
			{
				echo '<pre>';
				print_r(array(
					'width'=>$width,
					'height'=>$height,
					'max_width'=>$max_width,
					'max_height'=>$max_height,
					'x_ratio'=>$x_ratio,
					'y_ratio'=>$y_ratio,
					'tn_width'=>$tn_width,
					'tn_height'=>$tn_height,
					'tn_cntrX'=>$tn_cntrX,
					'tn_cntrY'=>$tn_cntrY,
					'putX'=>$putX,
					'putY'=>$putY,
					'lngst'=>$lngst,
				));
				echo '</pre>';			
				exit();
			}
            */

			//if ($max_width==$width && $max_height==$height && $this->imginfo['newmime']==$this->imginfo['mime']) { $dst=$src; $this->res_isoriginal = true; } else
			{
				$dst = imagecreatetruecolor($max_width, $max_height);
				//imagefilledrectangle($dst,0,0,$max_width,$max_height,imagecolorallocate($dst, 255, 255, 255));
				#imagecopyresampled($dst,$src,($max_width-$tn_width)/2, ($max_height-$tn_height)/2,0,0,$tn_width,$tn_height,$width,$height);
				imagecopyresampled($dst,$src,$putX,$putY,0,0,$tn_width,$tn_height,$width,$height);
			}
			$this->res_width = $max_width;
			$this->res_height = $max_height;
		} elseif ($mode === 2) {
			$tn_width = $max_width;
			$tn_height = $max_height;  
			
			if ($tn_width==$width and $tn_height==$height) { $dst=$src; $this->res_isoriginal = true; }
			else {
				$dst = imagecreatetruecolor($tn_width,$tn_height);
				imagecopyresampled($dst,$src,0,0,0,0,$tn_width,$tn_height,$width,$height);
			}
			$this->res_width = $tn_width;
			$this->res_height = $tn_height;
		}
		if ($dst!==$src) imagedestroy($src);

		#effect
		if ($effector!=null) {
			$this->res_isoriginal = false;
			$effector->apply($dst,$this->res_width,$this->res_height);
		}
		if ($this->rezip) $this->res_isoriginal = false;
		
		return $dst;		
	}

    /**
     * @param $pathstrOrgn
     * путь к оргиналу
     * @param $pathstr
     * путь назначения
     * @param $max_width
     * максимальная ширина
     * @param $max_height
     * максимальна высота
     * @param $mode
     * режим масштабирования:
     *
     *  0 - Пропорционально масштабировать. Изображение стрнет указанных размеров или меньше по одной из сторон.
     *
     *  1 - Пропорционально привести к размеру, излишки ОТРЕЗАТЬ. Изображение странет указанных размеров

     * -1 - Пропорционально привести к размеру, изображение стрнет указанных размеров (ВНУТРИ). Излишки заполнены белым.
     *
     *  2 - Не пропорционально масштабировать
     *
     *  3 - Пропорционально масштабировать. Изображение станет указанных размеров или больше по одной из сторон
     * @param bool $output
     * вывод в браузер
     *
     * 1    - только вывод в браузер без сохранения. Прерывает работу после вывода
     *
     * true - сохранение в указанную директорию и вывод. Прерывает работу после вывода
     *
     * false - только сохранение без вывода
     * @param null $effector
     * объект эффекта с методом ->apply($dst,$this->res_width,$this->res_height)
     * @param int $cntrX
     * центральная точка по x
     * @param int $cntrY
     * центральная точка по y
     * @return bool|null
     * @throws Exception
     */
	public function ResizeSave($pathstrOrgn,$pathstr,$max_width,$max_height,$mode,$output=false,$effector=null,$cntrX = -1,$cntrY = -1) {
		$result = null;
		$dst = $this->simpleResize($pathstrOrgn,$max_width,$max_height,$mode,$effector,$cntrX,$cntrY);

		$path_info = pathinfo($pathstr);
	
		switch ($path_info['extension']) {
			case 'gif' : $this->imginfo['newmime'] = 'image/gif'; break;
			case 'png' : $this->imginfo['newmime'] = 'image/png'; break;
			case 'jpg' : $this->imginfo['newmime'] = 'image/jpeg'; break;
		}
		
		if ($output===1) {
			header('Content-type: '.$this->imginfo['mime']); 
			header('X-Powered-By: itTeka.ru');
			imageinterlace($dst,1);
			imagejpeg($dst,null,90);
			exit();
		}

		if ($this->imginfo['newmime']==='image/jpeg')
		{
			imageinterlace($dst,1);
			$dirpath = dirname($pathstr);
			if (!file_exists(dirname($pathstr))) mkdir($dirpath,0777,true);
			if ($this->res_isoriginal && $this->imginfo['newmime']==$this->imginfo['mime']) {
				if ($pathstrOrgn==$pathstr) $result = true;
				else $result = copy($pathstrOrgn,$pathstr);#
			}
			else $result = imagejpeg($dst,$pathstr,90);#
		}
		elseif ($this->imginfo['newmime']=='image/png') 
		{
			imageinterlace($dst,1);
			$dirpath = dirname($pathstr);
			if (!file_exists(dirname($pathstr))) mkdir($dirpath,0777,true);
			if ($this->res_isoriginal && $this->imginfo['newmime']==$this->imginfo['mime']) {
				if ($pathstrOrgn==$pathstr) $result = true;
				else $result = copy($pathstrOrgn,$pathstr);#
			}
			else $result = imagepng($dst,$pathstr,9);#
		}
		else throw new Exception('imgr_wrong_newmime');


		if ($output===true) {
			header('Content-type: '.$this->imginfo['newmime']);
			header('X-Powered-By: itTeka.ru');
			@readfile($pathstr);#
			#ImageJpeg($dst,null,90);#
			exit();
		}
		imagedestroy($dst);
		return $result;
	}
}