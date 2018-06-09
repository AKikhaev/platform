<?php // Storage optimizer

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
     * Do a main process
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
                    $this->removed += rrmdir($dir);
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
     */
    public function packAllAction(){
        global $sql;
        profiler::showOverallTimeToTerminal(true);

/*
        $query = 'SELECT section_id,sec_nameshort,sec_content,sec_url_full FROM cms_sections ORDER by 1 DESC';
        $itemObj = $sql->queryObj($query);

        $remain = new remainCalc();
        $remain->init($itemObj->count(),'processing',0);
        toLogInfo('Оптимизация разделов: '.$itemObj->count());
        foreach ($itemObj as $item) {
        }
*/
        $cmsSections = (new modelCmsSections())->fields('section_id,sec_content,sec_url_full')->order('1 desc')->get();

        $remain = new remainCalc();
        $remain->init($cmsSections->count(),'processing',0);
        toLogInfo('Оптимизация разделов: '.$cmsSections->count());
        foreach ($cmsSections as $item) {
            //$item->SecUrl
        }
    }
}