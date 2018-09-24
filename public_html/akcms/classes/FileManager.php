<?php

class FileManagerItem{
    private $hashSalt = 'Fbc|';
    private $fileInfo;
    private $fileManager;
    public function __construct(modelCmsObjFiles &$fileInfo,FileManager &$fileManager)
    {
        $this->fileInfo = clone $fileInfo;
        $this->fileManager = $fileManager;
    }

    /** Short last part of path
     * @return string
     */
    protected function pathShort() {
        $path_ = str_split(str_pad($this->fileInfo->cofId, 12, '0',STR_PAD_LEFT),3);
        if (!$this->fileManager->secured)
            $path_[count($path_)-1] = hash('crc32', $this->hashSalt.$this->fileInfo->cofId);
        $path = implode('/',$path_);
        //$path = implode('/',str_split(hash('md5', $this->$hashSalt.$num),4));
        return $path.($this->fileInfo->cofFileExt==''?'':'.'.$this->fileInfo->cofFileExt);
    }

    /** path into storage
     * @param string $prePath
     * @return string
     */
    public function pathIn($prePath='o') { return $this->fileManager->folder.$prePath.'/'.$this->pathShort(); }

    /** User url
     * @param string $prePath
     * @return string
     */
    public function pathUrl($prePath='o') {
        global $cfg;
        $url = '/';
        if ($this->fileInfo->cofSrvId>0) {
            $url = $cfg['images_domains_url'][$this->fileInfo->cofSrvId];
        }
        if ($this->fileManager->secured) {
            $url .= sprintf('ajx/_sys/_fsDownload/%s/%d/%s.%s',
                $prePath,
                $this->fileInfo->cofId,
                $this->fileInfo->cofFile,
                $this->fileInfo->cofFileExt
            );
        } else $url .= $this->fileManager->folder.$prePath.'/'.$this->pathShort();
        return  $url;
    }

    /** Remove file
     * @return int
     * @throws DBException
     */
    public function drop() {
        if ($this->fileInfo->cofSrvId<1) {
            $files = glob($this->pathIn('*'));
            foreach ($files as $file) @unlink($file);
        } else {
            global $cfg;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,$cfg['images_domains_url'][$this->fileInfo->cofSrvId].'_api/img/drop?path='.$this->pathShort());
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $result=curl_exec ($ch);
            if ($result!='t'); //todo send notify
            curl_close ($ch);
        }
        return $this->fileInfo->delete();
    }
}


class FileManager extends PgUnitAbstract {
    public $secured;
    public $folder;
    private $folderSecured = '../fileStorage/';
    private $folderPublic = 's/fileStorage/';

    public function __construct($secured = false)
    {
        $this->secured = $secured;
        $this->folder = $secured ? $this->folderSecured : $this->folderPublic;
    }

    /** Get files list
     * @param $obj
     * Объект
     * @param $objId
     * ИД объекта
     * @param string $field
     * Поле привязки
     * @param string $ext
     * Расширение файлов
     * @return FileManagerItem[]
     * Список файлов
     * @throws DBException
     */
    public function get($obj,$objId,$field='',$ext='') {
        $filesInfo = (new modelCmsObjFiles())->fields()->where(
            [modelCmsObjFiles::$_cofObj,'=',$obj],
            [modelCmsObjFiles::$_cofObjId,'=',$objId],
            [modelCmsObjFiles::$_cofObjField,'=',$field]
        );
        if ($ext!=='') $filesInfo->and_(modelCmsObjFiles::$_cofFileExt,'=',$ext);
        $list = [];
        foreach ($filesInfo->get() as $fileInfo) {
            $list[] = new FileManagerItem($fileInfo,$this);
        }
        return $list;
    }

    /** get file by id
     * @param $id
     * @return false|FileManagerItem
     * @throws DBException
     */
    public function getById($id) {
        $fileInfo = (new modelCmsObjFiles())->fields()->where(
            [modelCmsObjFiles::$_cofId,'=',$id]
        );
        if ($fileInfo->get()->hasData())
            return new FileManagerItem($fileInfo,$this);
        else return false;
    }

    /**
     * @param $fileTempPath
     * @param $obj
     * @param $objId
     * @param $objField
     * @param $fileName
     * @param $fileExt
     * @param string $title
     * @param bool $secured
     * @param int $srvId
     * @return FileManagerItem|false
     * @throws DBException
     * @throws Throwable
     */
    public function newFile($fileTempPath,$obj,$objId,$objField,$fileName,$fileExt,$title = '',$secured = false, $srvId = 0){
        if (file_exists($fileTempPath)) {
            $fmiFileInfo = new modelCmsObjFiles();
            $fmi = new FileManagerItem($fmiFileInfo,$this);
            $fmiFileInfo->cofObj = $obj;
            $fmiFileInfo->cofObjId = $objId;
            $fmiFileInfo->cofObjField = $objField;
            $fmiFileInfo->cofTitle = $title;
            $fmiFileInfo->cofFile = $fileName;
            $fmiFileInfo->cofFileExt = $fileExt;
            $fmiFileInfo->cofOwnerId = (CmsUser::isLogin())?CmsUser::$user['id_usr']:0;
            $fmiFileInfo->cofSrvId = $srvId;
            $fmiFileInfo->cofSecured = $secured;
            $fmiFileInfo->cofDraft = true;
            $fmiFileInfo->insert();
            try {
                rename($fileTempPath, $fmi->pathIn());
            } catch (Throwable $e) {
                $fmiFileInfo->delete();
                core::GlobalExceptionHandler($e);
                return false;
            }
            $fmiFileInfo->cofDraft = false;
            $fmiFileInfo->insert();
            return $fmi;
        } else return false;
    }

    public function objectFileListAjax(){

    }




    static function saveImg($tmpPath,$obj,$objId,$ext='jpg',$prePath='o/',$name='',$file='') {
        if (!file_exists($tmpPath)) return false;
        global $sql,$cfg;

        $srv_id = 2; //Куда класть картинки

        $query = $sql->pr_i('cms_obj_photos',array(
            'cop_obj' => $sql->t($obj),
            'cop_obj_id' => $sql->d($objId),
            'cop_name' => $sql->t($name),
            'cop_file' => $sql->t($file),
            'cop_srv_id' => $sql->d($srv_id),
        )).' RETURNING *';
        $imgData = $sql->query_first($query);

        $shortpath = self::pathByNum_Short($imgData['id_cop'], $ext);

        if ($srv_id<=1) {
            $fileinfo = pathinfo($shortpath);
            $filefolder = self::$folder . $prePath . $fileinfo['dirname'];
            #$filename = $fileinfo['basename'];
            $fullpath = self::$folder . $prePath . $shortpath;

            @mkdir($filefolder, 0777, true);
            @rename($tmpPath, $fullpath);
        } else {
            $post = array('file'=>curl_file_create($tmpPath));
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,$cfg['images_domains_url'][$srv_id].'_api/img/upld?path='.$shortpath);
            curl_setopt($ch, CURLOPT_POST,1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $result=curl_exec ($ch);
            if ($result != 't') $imgData['error'] = 'error to upload to another server';//rollback

            curl_close ($ch);
            @unlink($tmpPath);####
        }

        return $imgData;
    }

    static function setHeaderImg($obj,$objId,$id,$ext='jpg') {
        global $sql;
        $query = sprintf ('UPDATE cms_obj_photos SET cop_hdr = false WHERE id_cop <> %d AND cop_obj=%s AND cop_obj_id=%d;',
            $sql->d($id),
            $sql->pgf_text($obj),
            $sql->d($objId)
        );
        $res_count = $sql->command($query);
        $query = sprintf ('UPDATE cms_obj_photos SET cop_hdr = true WHERE id_cop = %d AND cop_obj=%s AND cop_obj_id=%d',
            $sql->d($id),
            $sql->pgf_text($obj),
            $sql->d($objId)
        ).' returning *';
        $res = $sql->query_first($query);
        return $res;
    }

    static function ajx_GlrIList($obj,$objId) {
        $dataset = self::getImages($obj, $objId);
        if ($dataset!==false) foreach($dataset as &$item) {
            $item['cop_full'] = ImgManger::pathByNum('cmps',$item['id_cop'],$item['cop_srv_id']);
            unset($item['cop_file']);
        }
        return $dataset;
    }

    static function ajx_GlrINameUpd($obj,$objId,$id,$name)
    {
        global $sql;
        $query = sprintf ('UPDATE cms_obj_photos SET cop_name = %s WHERE id_cop = %d AND cop_obj=%s AND cop_obj_id=%d;',
            $sql->pgf_text($name),
            $sql->d($id),
            $sql->pgf_text($obj),
            $sql->d($objId)
        );
        $res_count = $sql->command($query);
        return $res_count>0?'t':'f';
    }

    static function ajx_GlrIUpload($obj,$objId)
    {

        global $sql,$page;
        $res_msg = ''; $res_id_cop = 0; $res_cop_file = ''; $res_cop_full = ''; $res_cop_srv_id = 0;

        if (isset($_FILES['file'])?$_FILES['file']['tmp_name']:false)
        {
            $upl = $_FILES['file'];
            if (is_uploaded_file($upl['tmp_name']))
            {
                if ($upl['size']>0)
                {
                    $file_name = mb_strtolower($upl['name']);
                    $file_ext = str_replace('.','',mb_substr($file_name,mb_strrpos($file_name,'.')));

                    if ($file_ext=='jpeg') $file_ext = 'jpg';
                    if ($file_ext=='jpg')
                    {
                        $max_width = 1200;
                        $max_height = 1200;
                        $imginfo = false;

                        try {
                            $imgRszr = new ImgResizer();
                            $dst = $imgRszr->simpleResize($upl['tmp_name'],$max_width,$max_height,0);
                            ImageJpeg($dst,$upl['tmp_name'],90);
                            $imginfo = $imgRszr->imginfo;
                        } catch(Exception $e) {}

                        if ($imginfo!==false)
                        {
                            $imgData = ImgManger::saveImg($upl['tmp_name'],$_POST['obj'],$_POST['obj_id']);
                            $res_id_cop = $imgData['id_cop'];
                            $res_cop_srv_id = $imgData['cop_srv_id'];
                            $res_cop_full = ImgManger::pathByNum('cmps',$res_id_cop,$res_cop_srv_id);

                            imagedestroy($dst);

                        } else
                        {
                            $res_msg = 'Неверный формат файла!';
                        }

                    }
                    else $res_msg = 'Неверный формат файла! Поддерживаются форматы: .jpg';
                } else $res_msg = 'Файл пуст!';
            } else $res_msg = 'Не тот файл!';
        } else $res_msg = 'Файл не передан!';

        return array(
            'status'=> $res_msg==''?0:-1,
            'msg'   => $res_msg,
            'id_cop'=> $res_id_cop,
            'cop_file'=> $res_cop_file,
            'cop_full' => $res_cop_full,
            'cop_srv_id'=>$res_cop_srv_id,
            'cop_name' => '',
            'cop_hdr'=>'f'
        );
    }

}