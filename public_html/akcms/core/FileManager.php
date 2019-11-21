<?php

class FileManagerItem{
    private $folderSecured = '../fileStorage/';
    private $folderPublic = 's/fileStorage/';

    private $hashSalt = 'Fbc|';
    private $fileInfo;
    public $isDuplicate = false;

    /**
     * FileManagerItem constructor.
     * @param modelCmsObjFiles|null $fileInfo
     * @throws DBException
     */
    public function __construct(modelCmsObjFiles $fileInfo = null)
    {
        $this->fileInfo = $fileInfo===null ? new modelCmsObjFiles() : clone $fileInfo;
    }

    /**
     * @return modelCmsObjFiles
     */
    public function getFileInfo()
    {
        return $this->fileInfo;
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
    public function pathIn($prePath='o') {
        if ($this->fileInfo->cofSrvId>0) return $this->pathShort();
        else return ($this->fileInfo->cofSecured=='t' ? $this->folderSecured : $this->folderPublic) . $prePath . '/' . $this->pathShort();
    }

    /** User url
     * @param string $prePath
     * @return string
     */
    public function pathUrl($prePath='o') {
        global $cfg;
        if ($this->fileInfo->cofSecured===true || $this->fileInfo->cofSecured=='t') {
            $url = sprintf('/ajx/_sys/_fsDownload/%s/%d/%s',
                $prePath,
                $this->fileInfo->cofId,
                $this->fileInfo->cofFile
            );
        } else
        if ($this->fileInfo->cofSrvId>0) {
            $url = $cfg['images_domains_url'][$this->fileInfo->cofSrvId].$prePath.'/'.$this->pathShort();
        } else $url = '/'.$this->folderPublic.$prePath.'/'.$this->pathShort();
        return  $url;
    }

    /** User preview url
     * @param string $prePath
     * @return string
     */
    public function pathPreview($prePath='s'){
        return in_array($this->fileInfo->cofFileExt,['jpg','png','bmp','gif','jpeg'])
            ? $this->pathUrl($prePath)
            : '/akcms/assets/Icons/filetypes/'.$this->fileInfo->cofFileExt.'.png';

    }

    /** Remove file
     * @return int
     * @throws DBException
     */
    public function drop() {
        if ($this->fileInfo->cofSrvId<1) {
            $files = glob($this->pathIn('*'));
            foreach ($files as $file) {
                @unlink($file);
                @unlink($file.'.tmp');
            }
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
        $data['url'] = $this->pathUrl();
        $data['urlPreview'] = $this->pathPreview();
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

    /** rename file for disposition header
     * @param $name
     * @return int
     * @throws DBException
     */
    public function setName($name) {
        $this->fileInfo->cofFile = $name;
        return $this->fileInfo->update();
    }

    /**
     * @param string $cofTitle
     * @return int
     * @throws DBException
     */
    public function setTitle($cofTitle)
    {
        $this->fileInfo->cofTitle = $cofTitle;
        return $this->fileInfo->update();
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
    private $serverUploadId = 0;
    private $hashSalt = 'Fbc|';

    private $onACLcheckCallback = null; //todo

    private $onUploadCallback = null; //todo
    private $onUploadedCallback = null;

    private $onRemoveCallback = null; //todo
    private $onRemovedCallback = null;

    /**
     * @param null $onUploadCallback
     */
    public function onUpload($onUploadCallback)
    {
        $this->onUploadCallback = $onUploadCallback;
    }

    /**
     * @param null $onUploadedCallback
     */
    public function onUploaded($onUploadedCallback)
    {
        $this->onUploadedCallback = $onUploadedCallback;
    }
    private function onUploadedProcess(FileManagerItem $fmi){
        if ($this->onUploadedCallback instanceof Closure) {
            $callBack = $this->onUploadedCallback;
            $callBack($fmi);
        }
    }

    /**
     * @param null $onRemovedCallback
     */
    public function setOnRemoved($onRemovedCallback)
    {
        $this->onRemovedCallback = $onRemovedCallback;
    }
    private function onRemovedProcess(FileManagerItem $fmi){
        if ($this->onRemovedCallback instanceof Closure) {
            $callBack = $this->onRemovedCallback;
            $callBack($fmi);
        }
    }



    /**
     * @param int $serverUploadId
     */
    public function setServerUploadId($serverUploadId)
    {
        $this->serverUploadId = $serverUploadId;
    }

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
    public static function get($obj, $objId, $objField='', $ext='') {
        $filesInfo = (new modelCmsObjFiles())->fields()->where(
            [modelCmsObjFiles::$_cofObj,'=',$obj],
            [modelCmsObjFiles::$_cofObjId,'=',$objId],
            [modelCmsObjFiles::$_cofObjField,'=',$objField],
            [modelCmsObjFiles::$_cofEnabled,'=',true]
        )->order(modelCmsObjFiles::$_cofId);
        if ($ext!=='') $filesInfo->and_(modelCmsObjFiles::$_cofFileExt,'=',$ext);
        $list = [];
        foreach ($filesInfo->get() as $fileInfo) {
            $list[] = new FileManagerItem($fileInfo);
        }
        return $list;
    }

    /** build html preview html spans
     * @param $files FileManagerItem[]
     * @return string
     */
    public static function listToHtml($files){
        $html = '';
        $tn_width = 100;
        $tn_height = 100;
        if (count($files)>0) foreach ($files as $file) {
            /* @var $file FileManagerItem */
            $html .= 
                "<span class='FileUploader_Item' style='width: ".$tn_width."px; height: ".$tn_height."px'>" .
                "<a href='".$file->pathUrl()."' title='".$file->getFile()."' target='_blank'>".
                "<img width='".$tn_width."' height='".$tn_height."' class='FileUploader_img' src='".$file->pathPreview()."'>" .
                "</a>".
                "</span>";
        }
        return $html;
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
            return new FileManagerItem($fileInfo);
        else return false;
    }

    private function tempPath(FileManagerItem $fmi) {
        return sys_get_temp_dir().'/upl_'.hash('crc32', $this->hashSalt.$fmi->getId()).'.'.$fmi->getFileExt();
    }

    /**
     * @param FormData $data
     * @param $file
     * @param bool $secured
     * @param int $srvId
     * @return FileManagerItem|false
     * @throws DBException
     * @throws CmsException
     */
    public function newChunk(FormData $data,$file,$secured = false, $srvId = 0){
        $tmp = '.tmp';

        $fileName = $file['name'][0];
        $fileName = preg_replace('/\.jpeg$/iu','.jpg',$fileName);
        $fileTempPath = $file['tmp_name'][0];
        if ($data->chunkNum==1) { // first part
            $fmi = new FileManagerItem(new modelCmsObjFiles());
            $fmiFileInfo = $fmi->getFileInfo();
            $fmiFileInfo->cofObj = $data->obj;
            $fmiFileInfo->cofObjId = $data->objId;
            $fmiFileInfo->cofObjField = $data->objField;
            $fmiFileInfo->cofTitle = $data->title;
            $fmiFileInfo->cofFile = $fileName;
            $fmiFileInfo->cofFileExt = mb_strtolower((new SplFileInfo($fileName))->getExtension());
            $fmiFileInfo->cofOwnerId = (CmsUser::isLogin())?CmsUser::$user['id_usr']:0;
            $fmiFileInfo->cofSrvId = $srvId;
            $fmiFileInfo->cofSecured = $secured;
            $fmiFileInfo->cofDraft = true;
            try {
                $fmiFileInfo->insert();
                $tempPath = $this->tempPath($fmi);
                rename($fileTempPath, $tempPath);
            } catch (Throwable $e) {
                $fmiFileInfo->delete();
                core::GlobalExceptionHandler($e);
                return false;
            }
        }

        if ($data->chunkNum == $data->chunkCount || $data->chunkNum > 1 && $data->chunkNum < $data->chunkCount) { // next part or last
            if(isset($data->id)) {
                $fmi = $this->getById($data->id);
                $fmiFileInfo = $fmi->getFileInfo();
                if (
                    $fmiFileInfo->cofObj == $data->obj &&
                    $fmiFileInfo->cofObjId == $data->objId &&
                    $fmiFileInfo->cofObjField == $data->objField
                ) {
                    $tempPath = $this->tempPath($fmi);
                    $pathIn = $fmi->pathIn();

                    try {
                        // next
                        if ($data->chunkNum > 1) {
                            if (!file_exists($tempPath) || !file_put_contents($tempPath, file_get_contents($fileTempPath), FILE_APPEND | LOCK_EX)) {
                                throw new CmsException('first chunk doesnt exists');
                            }
                            unlink($fileTempPath);
                        }
                        if ($data->chunkNum == $data->chunkCount) { // last

                            if ($fmi->getSrvId()<1) {
                                $filePath = (new SplFileInfo($pathIn))->getPath();
                                if (!file_exists($filePath)) mkdir($filePath,0777,true);
                                rename($tempPath, $pathIn);
                                if ($fmiFileInfo->cofSecured == false || $fmiFileInfo->cofSecured == 'f') chmod($pathIn,0664);
                            } else {
                                global $cfg;
                                $post = array('file'=>curl_file_create($tempPath));
                                $ch = curl_init();
                                curl_setopt($ch, CURLOPT_URL,$cfg['images_domains_url'][$fmi->getSrvId()].'_api/img/upld?path='.$fmi->pathIn());
                                curl_setopt($ch, CURLOPT_POST,1);
                                curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                                $result=curl_exec ($ch);
                                if ($result != 't') throw new CmsException('error to upload to another server: '.$result.' '.$fmi->pathIn());
                                curl_close ($ch);
                                @unlink($tempPath);####
                            }

                            $fmiFileInfo->cofDraft = false;
                            $fmiFileInfo->update();
                            $this->onUploadedProcess($fmi);
                        }
                    } catch (Throwable $e) {
                        $fmiFileInfo->delete();
                        core::GlobalExceptionHandler($e);
                        return false;
                    }

                } else return false;
            } else return false;
        }
        return $fmi;
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
            $fileName = preg_replace('/\.jpeg$/iu','.jpg',$fileName);

            if ($srvId == 0) { // Temporary. Need cross server operation api
                foreach ($this->get($obj, $objId, $objField) as $file) {
                    if ($file->getFile() == $fileName &&
                        filesize($file->pathIn()) == filesize($fileTempPath) &&
                        md5_file($file->pathIn()) == md5_file($fileTempPath)
                    ) {
                        unlink($fileTempPath);
                        $file->isDuplicate = true;
                        return $file;
                    }
                }
            }

            $fmi = new FileManagerItem(new modelCmsObjFiles());
            $fmiFileInfo = $fmi->getFileInfo();
            $fmiFileInfo->cofObj = $obj;
            $fmiFileInfo->cofObjId = $objId;
            $fmiFileInfo->cofObjField = $objField;
            $fmiFileInfo->cofTitle = $title;
            $fmiFileInfo->cofFile = $fileName;
            $fmiFileInfo->cofFileExt = mb_strtolower((new SplFileInfo($fileName))->getExtension());
            $fmiFileInfo->cofOwnerId = (CmsUser::isLogin())?CmsUser::$user['id_usr']:0;
            $fmiFileInfo->cofSrvId = $srvId;
            $fmiFileInfo->cofSecured = $secured;
            $fmiFileInfo->cofDraft = true;
            try {
                $fmiFileInfo->insert();
                $pathIn = $fmi->pathIn();
                $filePath = (new SplFileInfo($pathIn))->getPath();
                if ($fmi->getSrvId()<1) {
                    if (!file_exists($filePath)) mkdir($filePath,0777,true);
                    rename($fileTempPath, $pathIn);
                    if ($fmiFileInfo->cofSecured == false || $fmiFileInfo->cofSecured == 'f') chmod($pathIn,0664);
                } else {
                    global $cfg;
                    $post = array('file'=>curl_file_create($fileTempPath));
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL,$cfg['images_domains_url'][$fmi->getSrvId()].'_api/img/upld?path='.$fmi->pathIn());
                    curl_setopt($ch, CURLOPT_POST,1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    $result=curl_exec ($ch);
                    if ($result != 't') throw new CmsException('error to upload to another server: '.$result.' '.$fmi->pathIn());
                    curl_close ($ch);
                    @unlink($fileTempPath);####
                }
                $this->onUploadedProcess($fmi);
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
     * @param int $srvId
     * @param string $fileExt
     * @return FileManagerItem
     * @throws DBException
     */
    static public function getLazy($id,$srvId = 0,$fileExt='jpg') {
        $fmi = new FileManagerItem();
        $fmiFileInfo = $fmi->getFileInfo();
        $fmiFileInfo->cofId = $id;
        $fmiFileInfo->cofSrvId = $srvId;
        $fmiFileInfo->cofFileExt = $fileExt;
        return $fmi;
    }

    /**
     * @param $id
     * @param string $prePath
     * @param int $srvId
     * @param string $fileExt
     * @return string
     * @throws DBException
     */
    static public function getLazyPreview($id,$prePath = 'o',$srvId = 0,$fileExt='jpg') {
        $fmi = new FileManagerItem();
        $fmiFileInfo = $fmi->getFileInfo();
        $fmiFileInfo->cofId = $id;
        $fmiFileInfo->cofSrvId = $srvId;
        $fmiFileInfo->cofFileExt = $fileExt;
        return $fmi->pathUrl($prePath);
    }

    public function objectFileListAjax(){
        //https://knpz-ken.ru/ajx/_sys/_objectFileList?obj=capp&objId=7&field=files
        $data = (new FormData($_POST))
            ->addCheck('obj','/[a-zA-Z0-9_]+/')
            ->addCheck('objId',FormData::$Integer)
            ->addCheck('objField','/[a-zA-Z0-9_]+/')
            ->validateData();
        if ($data->isValid()) {
            return functs::json_encode_objectsArray($this->get($data->obj, $data->objId, $data->objField));
        }
        else return json_encode(['error'=>$data->errors()]);
    }

    public function objectFileRemoveAjax(){
        //https://knpz-ken.ru/ajx/_sys/_objectFileRemove?id=60
        $data = (new FormData($_POST))
            ->addCheck('obj','/[a-zA-Z0-9_]+/')
            ->addCheck('objId',FormData::$Integer)
            ->addCheck('objField','/[a-zA-Z0-9_]+/')
            ->addCheck('id',FormData::$Integer)
            ->validateData();
        if ($data->isValid()) {
            $res = false;
            $fmi = $this->getById($data->id);
            if ($fmi!==false) {
                $fmiInfo = $fmi->getFileInfo();
                if ($fmiInfo->cofObj == $data->obj && $fmiInfo->cofObjId == $data->objId && $fmiInfo->cofObjField == $data->objField) {
                    $res = $fmi->drop() == 1;
                    if ($res) $this->onRemovedProcess($fmi);
                }
            }
            return json_encode($res);
        }
        else return json_encode(['error'=>$data->errors()]);
    }

    public function objectFileUploadAjax(){
        $data = (new FormData($_POST))
            ->addCheck('obj','/[a-zA-Z0-9_]+/')
            ->addCheck('objId',FormData::$Integer)
            ->addCheck('objField','/[a-zA-Z0-9_]+/')
            ->addCheck('fileSize',FormData::$Integer)
            ->addCheck('chunkCount',FormData::$Integer,false)
            ->addCheck('chunkNum',FormData::$Integer,false)
            ->addCheck('id',FormData::$Integer,false)
            ->validateData();
        if ($data->addToErrors(isset($_FILES['file']),'file','empty')) {
            $data->addToErrors(is_uploaded_file($_FILES['file']['tmp_name'][0]),'file','Wrong');
            //$data->addToErrors(in_array($_FILES['file']['type'],['image/jpeg','image/png','image/bmp']),'file','wrong');
        }
        if ($data->isValid()) {
            if (isset($data->chunkCount)) {
                $fmi = $this->newChunk($data,$_FILES['file'],false,$this->serverUploadId);
            }
            else {
                $fmi = $this->newFile($_FILES['file']['tmp_name'][0], $data->obj, $data->objId, $data->objField, $_FILES['file']['name'][0],'',false,$this->serverUploadId);
            }
            return $fmi;
        }
        else return json_encode(['error'=>$data->errors()]);
    }

    /**
     * @return false|string
     * @throws DBException
     */
    public function objectFileSetTitle() {
        $data = (new FormData($_POST))
            ->addCheck('obj','/[a-zA-Z0-9_]+/')
            ->addCheck('objId',FormData::$Integer)
            ->addCheck('objField','/[a-zA-Z0-9_]+/')
            ->addCheck('id',FormData::$Integer)
            ->addCheck('title',FormData::$String)
            ->validateData();
        if ($data->isValid()) {
            $res = false;
            $fmi = $this->getById($data->id);
            if ($fmi!==false) {
                $fmiInfo = $fmi->getFileInfo();
                if ($fmiInfo->cofObj == $data->obj && $fmiInfo->cofObjId == $data->objId && $fmiInfo->cofObjField == $data->objField)
                    $res = $fmi->setTitle($data->title);
            }
            return json_encode($res);
        }
        else return json_encode(['error'=>$data->errors()]);
    }

    /**
     * @return false|string
     * @throws DBException
     */
    public function objectFileSetName() {
        $data = (new FormData($_POST))
            ->addCheck('obj','/[a-zA-Z0-9_]+/')
            ->addCheck('objId',FormData::$Integer)
            ->addCheck('objField','/[a-zA-Z0-9_]+/')
            ->addCheck('id',FormData::$Integer)
            ->addCheck('name',FormData::$String)
            ->validateData();
        if ($data->isValid()) {
            $res = false;
            $fmi = $this->getById($data->id);
            if ($fmi!==false) {
                $fmiInfo = $fmi->getFileInfo();
                if ($fmiInfo->cofObj == $data->obj && $fmiInfo->cofObjId == $data->objId && $fmiInfo->cofObjField == $data->objField)
                    $res = $fmi->setName($data->name);
            }
            return json_encode($res);
        }
        else return json_encode(['error'=>$data->errors()]);
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