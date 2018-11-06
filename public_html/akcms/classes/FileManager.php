<?php

class FileManagerItem{
    private $folderSecured = '../fileStorage/';
    private $folderPublic = 's/fileStorage/';

    private $hashSalt = 'Fbc|';
    private $fileInfo;
    private $fileManager;
    public $isDuplicate = false;
    public function __construct(modelCmsObjFiles &$fileInfo,FileManager &$fileManager)
    {
        $this->fileInfo = $fileInfo;
        $this->fileManager = $fileManager;
    }

    /** Short last part of path
     * @return string
     */
    protected function pathShort() {
        $path_ = str_split(str_pad($this->fileInfo->cofId, 12, '0',STR_PAD_LEFT),3);
        //if (!$this->fileInfo->cofSecured)
        $path_[count($path_)-1] = hash('crc32', $this->hashSalt.$this->fileInfo->cofId);
        $path = implode('/',$path_);
        //$path = implode('/',str_split(hash('md5', $this->$hashSalt.$num),4));
        return
            $path.
            ($this->fileInfo->cofFileExt==''?'':'.'.$this->fileInfo->cofFileExt).
            ($this->fileInfo->cofPacked=='t'?'.7z':'');
    }

    /** path into storage
     * @param string $prePath
     * @return string
     */
    public function pathIn($prePath='o') { return ($this->fileInfo->cofSecured=='t' ? $this->folderSecured : $this->folderPublic) . $prePath . '/' . $this->pathShort(); }

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
        if ($this->fileInfo->cofSecured=='t') {
            $url .= sprintf('ajx/_sys/_fsDownload/%s/%d/%s',
                $prePath,
                $this->fileInfo->cofId,
                $this->fileInfo->cofFile
            );
        } else $url .= $this->folderPublic.$prePath.'/'.$this->pathShort();
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

    public function __toString()
    {
        $data = $this->fileInfo->asArray();
        $data['uri'] = $this->pathUrl();
        return json_encode($data);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->fileInfo->cofId;
    }

    /**
     * @return string
     */
    public function getObj()
    {
        return $this->fileInfo->cofObj;
    }

    /**
     * @return int
     */
    public function getObjId()
    {
        return $this->fileInfo->cofObjId;
    }

    /**
     * @return string
     */
    public function getObjField()
    {
        return $this->fileInfo->cofObjField;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->fileInfo->cofTitle;
    }

    /**
     * @return string
     */
    public function getFile()
    {
        return $this->fileInfo->cofFile;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->fileInfo->cofEnabled;
    }

    /**
     * @return int
     */
    public function getOwnerId()
    {
        return $this->fileInfo->cofOwnerId;
    }

    /**
     * @return string
     */
    public function getFileExt()
    {
        return $this->fileInfo->cofFileExt;
    }

    /**
     * @return bool
     */
    public function isPacked()
    {
        return $this->fileInfo->cofPacked;
    }

    /**
     * @return int
     */
    public function getSrvId()
    {
        return $this->fileInfo->cofSrvId;
    }

    /**
     * @return bool
     */
    public function isDraft()
    {
        return $this->fileInfo->cofDraft;
    }

    /**
     * @return bool
     */
    public function isSecured()
    {
        return $this->fileInfo->cofSecured;
    }

    /**
     * @return string
     */
    public function getUploadedStamp()
    {
        return $this->fileInfo->cofUploadedStamp;
    }

    /**
     * @param string $cofTitle
     * @throws DBException
     */
    public function setTitle($cofTitle)
    {
        $this->fileInfo->cofTitle = $cofTitle;
        $this->fileInfo->update();
    }

    /**
     * @param bool $cofEnabled
     * @throws DBException
     */
    public function setEnabled($cofEnabled)
    {
        $this->fileInfo->cofEnabled = $cofEnabled;
        $this->fileInfo->update();
    }

    /**
     * @param bool $cofPacked
     * @throws DBException
     */
    public function setPacked($cofPacked)
    {
        $this->fileInfo->cofPacked = $cofPacked;
        $this->fileInfo->update();
    }

}


class FileManager extends PgUnitAbstract {

    /** Get files list
     * @param $obj
     * Объект
     * @param $objId
     * ИД объекта
     * @param string $objField
     * Поле привязки
     * @param string $ext
     * Расширение файлов
     * @return FileManagerItem[]
     * Список файлов
     * @throws DBException
     */
    public function get($obj, $objId, $objField='', $ext='') {
        $filesInfo = (new modelCmsObjFiles())->fields()->where(
            [modelCmsObjFiles::$_cofObj,'=',$obj],
            [modelCmsObjFiles::$_cofObjId,'=',$objId],
            [modelCmsObjFiles::$_cofObjField,'=',$objField],
            [modelCmsObjFiles::$_cofEnabled,'=',true]
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
            [modelCmsObjFiles::$_cofId,'=',$id],
            [modelCmsObjFiles::$_cofEnabled,'=',true]
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
    public function newFile($fileTempPath,$obj,$objId,$objField,$fileName,$title = '',$secured = false, $srvId = 0){
        if (file_exists($fileTempPath)) {

            foreach ($this->get($obj,$objId,$objField) as $file) {
                if ($file->getFile()==$fileName &&
                    filesize($file->pathIn())==filesize($fileTempPath) &&
                    md5_file($file->pathIn())==md5_file($fileTempPath)
                ) {
                    unlink($fileTempPath);
                    $file->isDuplicate = true;
                    return $file;
                }
            }

            $fmiFileInfo = new modelCmsObjFiles();
            $fmi = new FileManagerItem($fmiFileInfo,$this);
            $fmiFileInfo->cofObj = $obj;
            $fmiFileInfo->cofObjId = $objId;
            $fmiFileInfo->cofObjField = $objField;
            $fmiFileInfo->cofTitle = $title;
            $fmiFileInfo->cofFile = $fileName;
            $fmiFileInfo->cofFileExt = (new SplFileInfo($fileName))->getExtension();
            $fmiFileInfo->cofOwnerId = (CmsUser::isLogin())?CmsUser::$user['id_usr']:0;
            $fmiFileInfo->cofSrvId = $srvId;
            $fmiFileInfo->cofSecured = $secured;
            $fmiFileInfo->cofDraft = true;
            try {
                $fmiFileInfo->insert();
                $filePath = (new SplFileInfo($fmi->pathIn()))->getPath();
                if ($fmi->getSrvId()<1) {
                    if (!file_exists($filePath)) mkdir($filePath,0777,true);
                    rename($fileTempPath, $fmi->pathIn());
                } else {
                    global $cfg;
                    $post = array('file'=>curl_file_create($fileTempPath));
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL,$cfg['images_domains_url'][$fmi->getSrvId()].'_api/img/upld?path='.$fmi->pathIn());
                    curl_setopt($ch, CURLOPT_POST,1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    $result=curl_exec ($ch);
                    if ($result != 't') throw new CmsException('error to upload to another server');
                    curl_close ($ch);
                    @unlink($fileTempPath);####
                }
            } catch (Throwable $e) {
                $fmiFileInfo->delete();
                core::GlobalExceptionHandler($e);
                return false;
            }
            $fmiFileInfo->cofDraft = false;
            $fmiFileInfo->update();
            return $fmi;
        } else return false;
    }

    /** Lazy initialization from path build ready parameters without any queries
     * @param $id
     * @param string $fileExt
     * @param int $srvId
     * @return FileManagerItem
     * @throws DBException
     */
    public function getLazy($id,$fileExt='jpg',$srvId = 0) {
        $fmiFileInfo = new modelCmsObjFiles();
        $fmi = new FileManagerItem($fmiFileInfo,$this);
        $fmiFileInfo->cofId = $id;
        $fmiFileInfo->cofFileExt = $fileExt;
        $fmiFileInfo->cofSrvId = $srvId;
        return $fmi;
    }

    public function objectFileListAjax(){
        //https://knpz-ken.ru/ajx/_sys/_objectFileList?obj=capp&objId=7&field=files
        $data = $_GET;
        $checkRule = array();
        $checkRule[] = array('obj'  ,'/[a-zA-Z0-9_]+/');
        $checkRule[] = array('objId','/^\d+/');
        $checkRule[] = array('objField','/[a-zA-Z0-9_]+/');
        $checkResult = checkForm($data,$checkRule);
        if (count($checkResult)==0) {
            return functs::json_encode_objectsArray($this->get($data['obj'], $data['objId'], $data['objField']));
        }
        else return json_encode(['error'=>$checkResult]);
    }

    public function objectFileRemoveAjax(){
        //https://knpz-ken.ru/ajx/_sys/_objectFileRemove?id=60
        $data = $_GET;
        $checkRule = array();
        $checkRule[] = array('id','/^\d+/');
        $checkResult = checkForm($data,$checkRule);
        if (count($checkResult)==0) {
            $res = false;
            $fmi = $this->getById($data['id']);
            if ($fmi!==false) $res = $fmi->drop()==1;
            return json_encode($res);
        }
        else return json_encode(['error'=>$checkResult]);
    }

    public function objectFileUploadAjax(){

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