<?php
		/* Галерея объекта */
		$effector = null;
        //$effector = new ImgEffcts_watermark('../img/t/i_288_255_p.png',0,0,0);
        //$effector = new ImgEffcts_watermark('../img/t/mw_mask.png',2,0,0);
        if ($path[0]==='s' && $path[1]==='fileStorage' && $path[2]==='s') # Превью файлохранилище !
        {
            $path[2] = 'o';
            $pathstrOrgn = implode('/',$path);
            #$pathstr = $pathstr;
            unset($path[0]);
            $metaPathStr = implode('/',$path);
            $max_width = 200;
            $max_height = 200;
            $mode = 1;
        }
        elseif ($path[0]==='img' && $path[1]==='pages' && $path[2]==='s') # админ привью раздела !
        {
            unset($path[2]);
            $pathstrOrgn = implode('/',$path);
            #$pathstr = $pathstr;
            unset($path[0]);
            $metaPathStr = implode('/',$path);
            $max_width = 30;
            $max_height = 22;
            $mode = 1;
        } elseif ($path[0]==='img' && $path[1]==='pages' && $path[2]==='nt') # Фото раздела в панели управления !
        {
            unset($path[2]);
            $pathstrOrgn = implode('/',$path);
            #$pathstr = $pathstr;
            unset($path[0]);
            $metaPathStr = implode('/',$path);
            $max_width = 108;
            $max_height = 108;
            $mode = 1;
        } elseif ($path[0]==='img' && $path[1]==='objph' && $path[2]==='s') # Для админки !
		{
			unset($path[2]);
			$pathstrOrgn = implode('/',$path);
			#$pathstr = $pathstr;
			unset($path[0]);
			$metaPathStr = implode('/',$path);
			$max_width = 51;
			$max_height = 38;
			$mode = 1;
		} elseif ($path[0]==='img' && $path[1]==='gallery' && $path[2]==='s') # Для админки !
        {
            unset($path[2]);
            $pathstrOrgn = implode('/',$path);
            #$pathstr = $pathstr;
            unset($path[0]);
            $metaPathStr = implode('/',$path);
            $max_width = 51;
            $max_height = 38;
            $mode = 1;
        }
		/* Галерея */
        elseif ($path[0]==='img' && $path[1]==='pages' && $path[2]==='m') # админ привью раздела
		{
			unset($path[2]);
			$pathstrOrgn = implode('/',$path);
			#$pathstr = $pathstr;
			unset($path[0]);
			$metaPathStr = implode('/',$path);
			$max_width = 70;
			$max_height = 70;
			$mode = 1;
		} elseif ($path[0]==='img' && $path[1]==='pages' && $path[2]==='l') # Картинки для главной и подразделов
		{
			unset($path[2]);
			$pathstrOrgn = implode('/',$path);
			#$pathstr = $pathstr;
			unset($path[0]);
			$metaPathStr = implode('/',$path);
			$max_width = 178;
			$max_height = 149;
			$mode = 1;
			$effector = null;
		} elseif ($path[0]==='img' && $path[1]==='ytb' && $path[2]==='b') # youtube 
		{
			unset($path[2]);
			$pathstrOrgn = implode('/',$path);
			if (!file_exists($prePath.$pathstrOrgn)) {
				copy('http://i.ytimg.com/vi/' . basename($path[3], '.jpg') . '/hqdefault.jpg', $prePath . $pathstrOrgn);
			}
			#$pathstr = $pathstr;
			unset($path[0]);
			$metaPathStr = implode('/',$path);
			$max_width = 160;
			$max_height = 113;
			$mode = 1;
			$effector = null;
		}

        /* Каталог */
        elseif ($path[0]==='img' && $path[1]==='cat' && $path[2]==='s') # Каталог админ привью
        {
            unset($path[2]);
            $pathstrOrgn = implode('/',$path);
            #$pathstr = $pathstr;
            unset($path[0]);
            $metaPathStr = implode('/',$path);
            $max_width = 30;
            $max_height = 22;
            $mode = 1;
            $effector = null;
        }

		else throw new Exception('imgr_wrong_path');