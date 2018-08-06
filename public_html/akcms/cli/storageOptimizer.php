<?php // Storage optimizer

class CmsPageOptimizer {
    public $images = [];
    public $files = [];
    private $path_prefix;
    public $saved = 0;
    public $removed = 0;

    private $section;
    public function __construct(modelCmsSections $section)
    {
        $this->section = $section;
        $this->path_prefix = floor($this->section->sectionId / 1000) . '/' . ($this->section->sectionId % 1000);
    }

    private function optimizeHTML($html) {
        //Изображения
        $html = preg_replace('~(<img.*? src=")(\.\.\/){1,}([^<\'"\s]*?)"~ui','\1/\3',$html); // Заменяем любое количество начальных ../../../ на /
        $html = preg_replace('~(<img.*? src=")(?!\/|http:|https:)([^<\'"\s]*?)"~ui','\1/\2',$html); // Если это наш сервер и нет / в начале - добавляем /
        $html = preg_replace_callback('~\<img.+?\>~iu',function($imgTag){
            preg_match('/(?<=width="|width=\'|width=)(\d+)(?="|\'|)/iu',$imgTag[0],$img_width); $img_width = @$img_width[0];
            preg_match('/(?<=height="|height=\'|height=)(\d+)(?="|\'|)/iu',$imgTag[0],$img_height); $img_height = @$img_height[0];
            $imgTag[0] = preg_replace_callback('~(?<=src="|src=\')((http:|https:)[^<\'"\s]+?\.(jpg|png))(?="|\')~iu',function($imgSrc) use ($img_width,$img_height){
                $img_src = $imgSrc[0];
                $dirpath = 's/images/'.$this->path_prefix.'/';
                if(!file_exists($dirpath)) mkdir($dirpath,0755,true);

                $path_prts = pathinfo(mb_strtolower($img_src));
                $path_prts['filename'] = basename(Title2Uri($path_prts['filename']),'.'.$path_prts['extension']);

                //if ($path_prts['extension'] === 'jpeg') $path_prts['extension'] = 'jpg';
                //if ($path_prts['extension'] === 'png') $path_prts['extension'] = 'jpg';
                $pathnew = $dirpath.$path_prts['filename'].'.'.$path_prts['extension'];
                // Уникальное имя
                for ($i=1;$i<=11;$i++) if (file_exists($pathnew)) {
                    $pathnew = $dirpath.$path_prts['filename'].'_'.$i.'.'.$path_prts['extension'];
                } else break;
                $result = _getUrlContent($img_src);
                if ($result['code']==200) {
                    file_put_contents($pathnew,$result['data']);
                    if (core::$IS_CLI) toLogInfo($img_src.' => '.$pathnew.' '.(filesize($pathnew)/1024).' кб');
                    return '/'.$pathnew;
                }
                return $img_src;
            },$imgTag[0]);

            $imgTag[0] = preg_replace_callback('~(?<=src="/|src=\'/)([^<\'"\s]+?\.(jpg|png))(?="|\')~iu',function($imgSrc) use ($img_width,$img_height){
                $img_src = $imgSrc[0];
                if ($img_width!=null && $img_height!=null) {
                    $imginfo = @getimagesize($img_src);
                    $imginfo_width = @$imginfo[0];
                    $imginfo_height = @$imginfo[1];
                    if ($imginfo!==false && ($imginfo_width>$img_width*2 || $imginfo_height>$img_height*2)) {
                        $size = @filesize($img_src);
                        $ir = new ImgResizer();
                        $ir->ResizeSave($img_src,$img_src,$img_width*2,$img_height*2,0);
                        clearstatcache();
                        $newSize = @filesize($img_src);
                        $this->saved += $size - $newSize;

                        if (core::$IS_CLI) toLogInfo("$img_src $img_width $img_height ".($this->saved/1024).' кб');
                    }
                }
                $this->images[] = $img_src;
                return $img_src;
            },$imgTag[0]);
            return $imgTag[0];
        },$html);

        //Файлы
        $html = preg_replace_callback('~\<a.+?\>~iu',function($aTag){
            $aTag[0] = preg_replace_callback('~(?<=href="/|href=\'/)([^<\'"\s]+?\.(xls|xlsx|doc|docx|7z))(?="|\')~iu',function($fileSrc){
                $file_src = $fileSrc[0];
                $size = @filesize($file_src);
                $path_prts = pathinfo($file_src);
                if ($size>30000 && in_array(mb_strtolower($path_prts['extension']),['xls','xlsx','doc','docx'])) {
                    $file_src_7z = "$path_prts[dirname]/$path_prts[filename].$path_prts[extension].7z";
                    $command = "7z a -t7z -m0=lzma -mx=9 -mfb=64 -md=32m -ms=on -bd " .
                        escapeshellarg($file_src_7z) . ' ' .
                        escapeshellarg("$_SERVER[DOCUMENT_ROOT]/$file_src");
                    exec($command,$out,$return_var);
                    if ($return_var===0) {
                        $newSize = @filesize($file_src_7z);
                        $this->saved += $size - $newSize;
                        //unlink($file_src);
                        if (core::$IS_CLI) toLogInfo($file_src.' => '.$file_src_7z.' '.($this->saved/1024).' кб');
                        $this->files[] = $file_src_7z;
                        return $file_src_7z;
                    }
                }
                $this->files[] = $file_src;
                return $file_src;
            },$aTag[0]);
            return $aTag[0];
        },$html);


        //var_log_terminal($html);
        //var_log_terminal__($this->images);
        //var_log_terminal__($this->files);
        return $html;
    }

    private function cleanImages(){
        $folderFiles = glob('s/images/'.$this->path_prefix.'/*.{[jJ][pP][gG],[pP][nN][gG]}',GLOB_BRACE);
        foreach ($folderFiles as $folderFile) if (!in_array($folderFile,$this->images)) {
            $this->saved += @filesize($folderFile);
            $this->removed += 1;
            unlink($folderFile);
            if (core::$IS_CLI) toLogError($folderFile);
        }
    }

    private function cleanFiles(){
        $folderFiles = glob('s/files/'.$this->path_prefix.'/*.{[xX][lL][sS],[xX][lL][sS][xX],[dD][oO][cC],[dD][oO][cC][xX],7[zZ]}',GLOB_BRACE);
        foreach ($folderFiles as $folderFile) if (!in_array($folderFile,$this->files)) {
            $this->saved += @filesize($folderFile);
            $this->removed += 1;
            unlink($folderFile);
            if (core::$IS_CLI) toLogError($folderFile);
        }
    }

    /**
     * @throws DBException
     */
    public function optimize(){
        $oldHTML = $this->section->secContent;
        $HTML = $this->optimizeHTML($oldHTML);
        if ($HTML!=$oldHTML) {
            $this->section->secContent = $HTML;
            $this->section->update();
        }

        $secStrs = (new modelCmsSectionsString())->where($this->section)->get();
        foreach ($secStrs as $secStr) {
            $oldHTML = $secStr->secsStr;
            $HTML = $this->optimizeHTML($oldHTML);
            if ($HTML!=$oldHTML) {
                $secStr->secsStr = $HTML;
                $secStr->update();
            }
        }

        $this->cleanImages();
        $this->cleanFiles();
    }

}


/**
 * storageOptimizer - оптимизация всех ресурсов
 */
class storageOptimizer extends cliUnit {
    private $saved = 0;
    private $removed = 0;

    private function rrmdir($dir) {
        $removed = 0;
        foreach(glob($dir . '/*') as $file) {
            if(is_dir($file))
                $removed += rrmdir($file);
            else {
                $removed += @filesize($file);
                unlink($file);
            }
        }
        rmdir($dir);
    }

    /**
     * Полная оптимизация
     */
    public function runAction(){
        $this->dropOldFoldersAction();
        $this->packAllAction();
    }

    /**
     * Удаление старых каталогов
     */
    public function dropOldFoldersAction(){
        global $sql;
        $ids_all = $sql->query_all_column('SELECT section_id FROM cms_sections');

        toLogInfo('Удаление ненужных каталогов ресурсов');
        $dirs = glob('s/{images,files}/*/*',GLOB_BRACE | GLOB_ONLYDIR);
        foreach ($dirs as $dir) {
            if (preg_match('~(\\d++/\\d++)$~',$dir,$match)) {
                $id = (string)(int)str_replace('/','',$match[1]);
                if (!in_array($id,$ids_all)) {
                    $this->removed += $this->rrmdir($dir);
                    toLogError('Папка без раздела: ' . $dir);
                }
            }
        }

        toLogInfo('Удаление ненужных картинок разделов');
        $files = glob('img/pages/{*,*/*}',GLOB_BRACE);
        foreach ($files as $file) if (is_file($file)) {
            $id = basename($file,'.jpg');
            if (!in_array($id,$ids_all)) {
                $this->removed += @filesize($file);
                unlink($file);
                toLogError('Файл без раздела: ' . $file);
            }
        }
    }

    /**
     * Скачивание и оптимизация изображений и архивация файлов
     * @throws DBException
     */
    public function packAllAction(){
        //profiler::showOverallTimeToTerminal();

        $cmsSections = (new modelCmsSections())
            ->fields('section_id,sec_content,sec_url_full')
            ->order('1 desc')
            ->get();

        $remain = new remainCalc();
        $remain->init($cmsSections->count(),'processing',0);
        toLogInfo('Оптимизация разделов: '.$cmsSections->count());

        foreach ($cmsSections as $cmsSection) {
            $optimizer = new CmsPageOptimizer($cmsSection);
            $optimizer->optimize();
            $this->saved += $optimizer->saved;
            unset($optimizer);
            $remain->plot(-1,true,number_format($this->saved/1024,2,'.',' ').' кб ['.$cmsSection->sectionId.']');
            //toLogDie__('finish');
        }
    }
}