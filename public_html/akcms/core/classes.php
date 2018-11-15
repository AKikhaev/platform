<?php
class CmsException extends ErrorException{
/*
    public function __construct($message="",$code=0,$line=0,$file=''){
        parent::__construct($message,$code);
        if ($line!=0) $this->line = $line;
        if ($file!='') $this->file = $file;
    }
*/
}
class DBException extends CmsException { 
	public $text = '';
	public $isDuplicate = false;
	public $field = '';
	public function __construct($message = '', $text = '') {
		$this->text = $text;
		if (mb_stripos($text,'duplicate key value violates unique constraint')!==false) {
			$this->isDuplicate = true;
			$matches = array();
			if (preg_match('/DETAIL:  Key \(([^\)]+)\)/ui',$text,$matches)>0) $this->field = $matches[1];
		}
		parent::__construct($message."\n".$text);
	}
}

abstract class AclProcessor { /* acl */
    public $page = [];
	private $owner = false;
	protected $aclSuper = array('admin','owner');
    /**
     * @var array
     * Применимо к проверком, кроме exactly
     */
    protected $acl = array();
    protected function initAclSuper() {return $this->aclSuper;} // return array(); owner,admin,...
    protected function initAcl() {return $this->acl;} // return array(); owner,admin,...
	protected function isOwner() {return $this->owner;}
    protected function setOwner($isOwner) {return $this->owner = $isOwner;}
    protected function isSpecifiedRight($rightName) {return null;}

    /*** Проверяет право доступа с учетом текущего класса. Допустимые разрешения ClassName.methodName, ClassName.*, *.methodName, RightName
     * @param null $rightName
     * Имя права. Если null - используется имя метода
     * @param bool|string $class
     * Если null - автоопределение, если false - без класса
     * @param $exact
     * true - Обрабатывать только superAcl, иначе false
     * @return bool
     */
    public function hasRight($rightName = null, $class = null, $exact = false) {
        if ($class===null) {
            $stacktrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
            $class = isset($stacktrace[1]['class'])?$stacktrace[1]['class']:'';
            if ($rightName===null) $rightName = $stacktrace[1]['function'];
        }
        /*
        Работа механизма через разрешение от запрещенного. Изначально null - права не определены
        и если так и будут не определны в ходе проверок, то переключаются в false - запрещено.
        Если в процессе право запрещено, то разрешить его нельзя (приоритет запрещения)
        Если право не задано или разрешено, то его еще можно запретить в ходе проверок.
         */
        $needRights = $exact ?
            $this->initAclSuper() :
            array_merge($this->initAclSuper(),$this->initAcl());
        $userRights = CmsUser::$rights;
        $rightRes = null;

        if ($class === false) {
            $needRights[] = $rightName;
        } else {
            $needRights[] = $class.'.'.$rightName;
            $needRights[] = $class.'.*';
            //$needRights[] = '*.'.$rightName;
        }
        //$specifiedRight = $this->isSpecifiedRight($rightName);
        //if ($specifiedRight !== null) $rightRes = $specifiedRight; // Не помню
        //if ($this->isOwner()) $userRights[CMS_OBJOWNER] = true; // Владелец

        // Проверка на наличие необходимых прав (права, прописаные у объекта)
        foreach ($needRights as $needRight) {
            if ($rightRes !== false && isset($userRights[$needRight])) {
                $rightRes = $userRights[$needRight];
            }
        }

        return $rightRes?:false;
    }

}

abstract class PgUnitAbstract extends AclProcessor { /* Pg_ untits */
	public $unitParam = array();
	public function view($viewName) {
		$viewUnit = str_replace('_','/',get_class($this));
		return "$viewUnit/$viewName.php";
	}
	public function view2($viewName) {
        $viewUnit = str_replace('_','/',get_class($this));
		return "$viewUnit/$viewName";
	}
	public function __construct($pathParams = array()) { $this->unitParam = $pathParams; }
    public function initAjx(){
        $ajaxList = [];
        $rc = new ReflectionClass($this);
        foreach ($rc->getMethods() as $method) {
            if (mb_substr($method->getName(), -4) === 'Ajax') {
                $ajaxList['_'.mb_substr($method->getName(), 0, -4)] = [
                    'func' => $method->getName(),
                    'object' => $this
                ];
            }
        }
        return $ajaxList;
    }
	public static function buildLevelSiteMap(&$putInto,$parentId,$parentUrlFull,$showHidden = false) {} // Строит карту сайта
}

class CacheController { /* cache */
    /**
     * @var bool Ингорировать время, брать если есть
     */
    public $forceCache = false;

	public function getpath($key, $cd=false) {
		global $cfg;
		$key = md5($key);
		$dirpath = $cfg['filecache']['path'].substr($key,0,1).'/';
		if ($cd && !file_exists($dirpath)) mkdir($dirpath,0755,true);
		return $dirpath.$key;
	}
	
	public function cache_read($key, &$val) {
		$ipath = $this->getpath($key);
		$dump = file_exists($ipath)?file_get_contents($ipath):false;
		if ($dump!==false) {
			$c_obj = unserialize($dump);
			if (time()<$c_obj['u'] || $this->forceCache) {
				$val = $c_obj['d'];
				return true;
			} //else @unlink($ipath); //$this->cache_drop($key); //Зачем удалять если его и так перезапишут
		}
		return false;
	}
	
	public function cache_read_drop($key, &$val) {
		$f = $this->cache_read($key,$val);
		if ($f) $this->cache_drop($key);
		return $f;
	}
	
	public function cache_write($key, &$val, $life=86400, $until=0) {
		$ipath = $this->getpath($key,true);
		$c_obj = array('d'=>$val,'u'=> $until==0?time()+$life:$until);
		return file_put_contents($ipath,serialize($c_obj))>0;
	}
	
	public function cache_exists($key) {
		$ipath = $this->getpath($key);
		return file_exists($ipath);
	}
	
	public function cache_drop($key) {
		$ipath = $this->getpath($key);
		if (file_exists($ipath)) @unlink($ipath);
	}
}

class CmsUser {
    public static $rights = array();
    public static $user = array();
	private static $fields = 'id_usr,usr_login,usr_name,usr_admin,usr_enabled,usr_grp,usr_activated,usr_email';

	/**
	 * @param int $length
	 * @return string
	 * @throws Exception
	 */
	public static function generate_password_string($length = 20){
		$chars =  'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789`-=~!@#$%^&*()_+,./<>?;:[]{}\|';

		$str = '';
		$max = strlen($chars) - 1;

		for ($i=0; $i < $length; $i++)
			$str .= $chars[random_int(0, $max)];

		return $str;
	}

	private static function forceAuth($userData){
        if (!isset($_COOKIE[session_name()])) session_start();
        $_SESSION['u'] = $userData['usr_login'];
        $_SESSION['ip'] = core::get_client_ip();
        CmsUser::$user = $userData;
        return true;
    }

    public static function auth($loginOrEmail, $password, $emailInsteadLogin = false) {
        global $sql;
        $query = sprintf('select '.self::$fields.' from cms_users where %s = %s and usr_password_md5 = %s and usr_enabled and usr_activated limit 1;',
			$emailInsteadLogin ? 'usr_email' : 'usr_login',
            $sql->pgf_text($loginOrEmail),
            $sql->pgf_text(md5($GLOBALS['cfg']['usrprepass'].$password)));
        $datausr = $sql->query_first_assoc($query);
        if ($datausr!==false) return self::forceAuth($datausr);
        else return false;
    }

    public static function authAuto($login,$autohash) {
        global $sql;
        $query = sprintf('select '.self::$fields.' from cms_users where usr_login = %s and usr_autohash = %s and usr_enabled and usr_activated limit 1;',
            $sql->pgf_text($login),
            $sql->pgf_text(md5($GLOBALS['cfg']['usrprepass'].$autohash)));
        $datausr = $sql->query_first_assoc($query);
        if ($datausr!==false) return self::forceAuth($datausr);
        else return false;
    }

    public static function authAuto_id($id,$autohash) {
        global $sql;
        $query = sprintf('select '.self::$fields.' from cms_users where id_usr = %d and usr_autohash = %s and usr_enabled and usr_activated limit 1;',
            $sql->d($id),
            $sql->pgf_text(md5($GLOBALS['cfg']['usrprepass'].$autohash)));
        $datausr = $sql->query_first_assoc($query);
        if ($datausr!==false) return self::forceAuth($datausr);
        else return false;
    }

    public static function hasLogin($login) {
        global $sql;
        $query = sprintf('select '.self::$fields.' from cms_users where usr_login ilike %s;',
            $sql->pgf_text($login));
        $datausr = $sql->query_first_assoc($query);
        return $datausr;
    }

    public static function hasEmail($email) {
        global $sql;
        $query = sprintf('select '.self::$fields.' from cms_users where usr_email ilike %s;',
            $sql->pgf_text($email));
        $datausr = $sql->query_first_assoc($query);
        return $datausr;
    }

    public static function hasId($id) {
        global $sql;
        $query = sprintf('select '.self::$fields.' from cms_users where id_usr = %d;',
            $sql->d($id));
        $datausr = $sql->query_first_assoc($query);
        return $datausr;
    }

    public static function isLogin() {
        return count(CmsUser::$user)>0;
    }

    public static function logout() {
        $_SESSION = array();
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time()-42000, '/');
            @session_destroy();
            CmsUser::$user = array();
        }
    }

    public static function register($login,$email,$password,$name,$soname = '') {
        global $sql;
        $actcode = md5('юhuu'.random_bytes(20));
        $cmsUser = new modelCmsUsers();
		$cmsUser->usrLogin = $login;
		$cmsUser->usrEmail = $email;
		$cmsUser->usrPasswordMd5 = md5($GLOBALS['cfg']['usrprepass'].$password);
		$cmsUser->usrName = $name;
		$cmsUser->usrActcode = $actcode;
		if ($soname!=='') $cmsUser->usrSoname = $soname;
		if (core::$IS_CLI) $cmsUser->usrRegisteredId = 'cli';
		elseif (core::get_client_ip() !== false) $cmsUser->usrRegisteredId = core::get_client_ip();
		$regData = $cmsUser->insert()?$cmsUser->asArray():[];
		unset($regData['usr_password_md5']);
		return $regData;
    }

    public static function genLostcode($login) {
        global $sql;
        $lostcode = md5('юhuu'.random_bytes(20));
        $query = sprintf('UPDATE cms_users SET usr_lostcode = %s WHERE usr_login = %s RETURNING usr_email;',
            $sql->pgf_text($lostcode),
            $sql->pgf_text($login)
        );
        $res = $sql->query_first_row($query);
		return $res!=false?array('email'=>$res[0],'lostcode'=>$lostcode):false;
    }

    public static function newLostpass($login,$lostcode,$password) {
        global $sql;
        $query = sprintf('UPDATE cms_users SET usr_lostcode = \'\', usr_password_md5=%s WHERE usr_lostcode=%s AND usr_login = %s RETURNING usr_email;',
            $sql->pgf_text(md5($GLOBALS['cfg']['usrprepass'].$password)),
            $sql->pgf_text($lostcode),
            $sql->pgf_text($login)
        );
        $res = $sql->query_first_row($query);
        return $res!=false?array('email'=>$res[0]):false;
    }

    public static function checkLostcode($login,$code) {
        global $sql;
        if ($code=='') return false;
        $query = sprintf('SELECT count(*) as reccount FROM cms_users WHERE usr_login = %s AND usr_lostcode = %s;',
            $sql->pgf_text($login),
            $sql->pgf_text($code)
        );
        $res = $sql->query_first_row($query);
        return $res!=false?$res[0]>0:false;
    }

    public static function changePassword($login,$passwordOld,$password) {
        global $sql;
        $query = sprintf('UPDATE cms_users SET usr_password_md5=%s WHERE usr_password_md5=%s AND usr_login = %s;',
            $sql->pgf_text(md5($GLOBALS['cfg']['usrprepass'].$password)),
            $sql->pgf_text(md5($GLOBALS['cfg']['usrprepass'].$passwordOld)),
            $sql->pgf_text(mb_strtolower($login))
        );
        $res = $sql->command($query);
        return $res?true:false;
    }

    public static function changeName($login,$name) {
        global $sql;
        $query = sprintf('UPDATE cms_users SET usr_name=%s WHERE usr_login = %s;',
            $sql->pgf_text($name),
            $sql->pgf_text(mb_strtolower($login))
        );
        $res = $sql->command($query);
        return $res?true:false;
    }

    public static function setNewPassword($login,$password) {
        global $sql;
        $query = sprintf('UPDATE cms_users SET usr_password_md5=%s WHERE usr_login = %s;',
            $sql->pgf_text(md5($GLOBALS['cfg']['usrprepass'].$password)),
            $sql->pgf_text(mb_strtolower($login))
        );
        $res = $sql->command($query);
        return $res?true:false;
    }

    public static function setNewPassword_id($id,$password) {
        global $sql;
        $query = sprintf('UPDATE cms_users SET usr_password_md5=%s WHERE id_usr = %d RETURNING id_usr,usr_login,usr_name,usr_email;',
            $sql->pgf_text(md5($GLOBALS['cfg']['usrprepass'].$password)),
            $sql->d($id)
        );
        $res = $sql->query_first($query);
        return $res;
    }

    public static function genNewAutohash_id($id) {
        global $sql;
        $autohash = md5(mt_rand(10000000,99999999));
        $query = sprintf('UPDATE cms_users SET usr_autohash=%s WHERE id_usr = %d;',
            $sql->pgf_text(md5($GLOBALS['cfg']['usrprepass'].$autohash)),
            $sql->d($id)
        );
        $res = $sql->command($query);
        return $res>0?$autohash:false;
    }

    public static function activate($login,$actcode,$autoLogin = false) {
        global $sql;
        $query = sprintf('UPDATE cms_users SET usr_activated = true WHERE usr_enabled AND NOT usr_activated AND usr_login = %s AND usr_actcode = %s RETURNING '.self::$fields,
            $sql->pgf_text($login),
            $sql->pgf_text($actcode)
        );
        $datausr = $sql->query_first($query);
        if ($datausr!==false && $autoLogin) self::forceAuth($datausr);
        return $datausr;
    }

    public static function init() {
        global $sql;
        if (isset($_COOKIE[session_name()])) {
            session_start();
            if (isset($_SESSION['u']) && isset($_SESSION['ip'])) {
                if ($_SESSION['ip'] != $_SERVER['REMOTE_ADDR']) CmsUser::logout(); else {
                    $login = $_SESSION['u'];
                    #if ($_SERVER['REMOTE_ADDR']=='109.172.77.170') $login = '79615272331';
                    $query = sprintf('select *,array(SELECT (__if(usrrght_mode,\'\',\'!\'::text)||usrrght_name) FROM cms_users_groups_rgth where usrrght_grpid=any(usr_grp)) as rights from cms_users where usr_login = %s and usr_enabled and usr_activated limit 1;',
                        $sql->pgf_text($login));
                    $datausr = $sql->query_first_assoc($query);
                    if ($datausr!==false?$datausr['usr_login']==$login:false) {
                        CmsUser::$rights = array();
                        foreach (explode(',',trim($datausr['rights'],'}{')) as $right) {
                            $mode = true;
                            if (substr($right,0,1)=='!') {
                                $right = substr($right,1);
                                $mode = false;
                            }
                            if (!isset(CmsUser::$rights[$right]) || CmsUser::$rights[$right]===true) CmsUser::$rights[$right] = $mode;
                        }
                        CmsUser::$user = $datausr;
                        #if ($datausr['usr_admin']=='t') CmsUser::$groups[] = 'admin';
                    } else CmsUser::logout();
                };
            } #else CmsUser::logout();
        }
    }
}
final class core {
    public static $isAjax = false;
    public static $ajaxAction = '';
    public static $inEdit = false;
    public static $serverHost = '';
    public static $testServer = false;
    public static $prodServer = false;
    public static $devTest = false; // Developer test mode. New looks, for example
    public static $userAuth = false;
    public static $time_start = 0;
    private static $sharedObj = array();
    private static $GlobalErrors = '';
    private static $ErrorFirstTitle = '';
    public static $outputData = '';
    public static $renderPage;
    public static $OS_WIN = false;
    public static $IS_CLI = false;

    /***
     * Core loader
     */
    static function init(){
        global $cfg;
        set_include_path(
            'akcms/u/units'.PATH_SEPARATOR.
            'akcms/u/models'.PATH_SEPARATOR.
            'akcms/units'.PATH_SEPARATOR.
            'akcms/classes'.PATH_SEPARATOR.
            'akcms/models'.PATH_SEPARATOR.
            'akcms/u/template'.PATH_SEPARATOR.
            'akcms/template'.PATH_SEPARATOR.'.');

        error_reporting(-1);
        set_error_handler('core::GlobalErrorHandler',-1);
        set_exception_handler('core::GlobalExceptionHandler');
        register_shutdown_function('core::ShutdownHandler');
        spl_autoload_register('core::cms_autoload');

        umask(0002);
        setlocale(LC_CTYPE, 'ru_RU.UTF-8');
        setlocale(LC_COLLATE, 'ru_RU.UTF-8');
        mb_internal_encoding("UTF-8");
        mb_http_output('UTF-8');
        mb_http_input('UTF-8');
        mb_language('uni');
        mb_regex_encoding('UTF-8');
        date_default_timezone_set($cfg['default_timezone']);
    }

    public static function get_client_ip() {
        $ip = getenv('HTTP_CLIENT_IP');
        if ($ip===false) $ip = getenv('HTTP_X_FORWARDED_FOR');
        if ($ip===false) $ip = getenv('HTTP_X_FORWARDED');
        if ($ip===false) $ip = getenv('HTTP_FORWARDED_FOR');
        if ($ip===false) $ip = getenv('HTTP_FORWARDED');
        if ($ip===false) $ip = getenv('REMOTE_ADDR');
        return $ip;
    }
    public static function hidePathForShow($filename) {
		if (isset($_SERVER) && isset($_SERVER['DOCUMENT_ROOT']) && strpos($filename,$_SERVER['DOCUMENT_ROOT'])===0) {
            $filename = substr($filename, strlen($_SERVER['DOCUMENT_ROOT']));
        }
    	else $filename = basename($filename);
		return $filename;
	}
	private static function GlobalErrorHandler_paramToText(&$n, &$v, $lvl, &$parameters, &$d) {
        ++$lvl;
        switch (gettype($v)) {
            case 'boolean': $v = $v===true?'TRUE':'FALSE'; break;
            case 'integer':
            case 'double':
            case 'float': $v = GetTruncString($v,20); break;
            case 'object': $v = '{'.get_class($v).'}'; break;
            case 'array':
                if ($lvl>3) {
                    $v = '[…'.count($v).']';
                    break;
                } else {
                    $arr = [];
                    foreach ($v as $nn=>$value) if ($nn<3){
                        $arr[] = (is_numeric($nn)?'':$nn.'=>').self::GlobalErrorHandler_paramToText($n,$value,$lvl+1,$parameters,$d);
                    }
                    $v = '['.implode(',',$arr).(count($v)>count($arr)?',…'.count($v):'').']';
                }
                break;
            case 'string':
                if (isset($parameters[$n]) && (mb_stripos($parameters[$n]->getName(),'passw')!==false || mb_stripos($parameters[$n]->getName(),'psw')!==false)) $v='*****';
                elseif (isset($d['function']) && (in_array($d['function'],['require_once','require']) || strrpos($d['function'],'file_')===0))
                {
                    if (isset($_SERVER) && isset($_SERVER['DOCUMENT_ROOT']) && strpos($v,$_SERVER['DOCUMENT_ROOT'])===0) {
                        $v = substr($v, strlen($_SERVER['DOCUMENT_ROOT']));
                    }
                }
                $v = '\''.GetTruncString($v,20).'\'';
                break;
            default: $v = gettype($v); break;
        }
        return $v;
    }
    public static function GlobalErrorHandler($errno, $errmsg, $filename, $linenum, $backtrace)
    {
        Global $cfg;
        $errortype = array (
            0                    => '?',
            E_ERROR              => 'Error',
            E_WARNING            => 'Warning',
            E_PARSE              => 'Parsing Error',
            E_NOTICE             => 'Notice',
            E_CORE_ERROR         => 'Core Error',
            E_CORE_WARNING       => 'Core Warning',
            E_COMPILE_ERROR      => 'Compile Error',
            E_COMPILE_WARNING    => 'Compile Warning',
            E_USER_ERROR         => 'User Error',
            E_USER_WARNING       => 'User Warning',
            E_USER_NOTICE        => 'User Notice',
            E_STRICT             => 'Runtime Notice',
            E_RECOVERABLE_ERROR  => 'Catchable Fatal Error',
            E_DEPRECATED         => 'Deprecated',
            E_USER_DEPRECATED    => 'User deprecated',
            -1					 => 'In try'
        );

        if ($errmsg !== 'login_needs' && error_reporting()!==0) {
            $err = $errortype[$errno].': '. $errmsg . "\n";
            if (self::$ErrorFirstTitle=='')
            	self::$ErrorFirstTitle = $errortype[$errno].': '.explode("\n",$errmsg)[0].' '.
					(isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:'').' '.(isset($_SERVER['REQUEST_URI'])?$_SERVER['REQUEST_URI']:'');
            $err .= 'src: ' . self::hidePathForShow($filename).': '.$linenum . "\n";
            if (in_array($errno, array(E_USER_ERROR, E_USER_WARNING, E_USER_NOTICE, E_NOTICE, E_ERROR, E_WARNING, -1, 0))) {#E_WARNING,
                $tracedata = array();
                if (!isset($backtrace[0])) { $backtrace = debug_backtrace(0); unset($backtrace[0]);}
                foreach($backtrace as $k=>$d) if (is_array($d)) {
                    $i='  '.$k.': ';
                    if (isset($d['file'])) $i  .= self::hidePathForShow($d['file']);
                    if (isset($d['line'])) $i  .= ':'.$d['line'];
                    if (isset($d['class'])) $i .= ' {'.$d['class'].'}';
                    if (isset($d['type'])) $i  .= ' '.$d['type'];
                    if (isset($d['function'])) $i .= ' '.$d['function'];
                    if (isset($d['args'])) {
                        $parameters = [];
                        try {
                            if (isset($d['function']) && $d['function']!='{closure}')
                                $parameters = isset($d['class']) ?
                                    (new ReflectionClass($d['class']))->getMethod($d['function'])->getParameters() :
                                    (new ReflectionFunction('file_exists'))->getParameters();
                        } catch (Exception $eDie) {
                            ChromePhp::log(
                                $eDie
                            );
                        }
                        foreach ($d['args'] as $n=>&$v){
                            $v = self::GlobalErrorHandler_paramToText($n,$v,0,$parameters,$d);
                        }
                        $i .= '('.implode(',',$d['args']).')';
                    }
                    $tracedata[] = $i;
                }
                $err .= implode(PHP_EOL,$tracedata) . PHP_EOL.PHP_EOL;
            }
            if (self::$testServer || isset($cfg['debug']) && $cfg['debug']===true) {
                if (self::$IS_CLI) {}//echo '/* '.$err.' */';
                else ChromePhp::error($err);
            }
            self::$GlobalErrors .= $err;
        }

        return true;
    }
    public static function GlobalExceptionHandler($e){
        /* @var $e Exception */
        self::GlobalErrorHandler($e->getCode(),$e->getMessage(),$e->getFile(),$e->getLine(),$e->getTrace());
    }
    public static function InTryErrorHandler(Exception $e) {
		self::GlobalErrorHandler(-1,$e->getMessage(),$e->getFile(),$e->getLine(),$e->getTrace());
		if (self::$isAjax) { http_response_code(500); return; } //json_encode(array('error'=>array(array('f'=>'system','s'=>'failure'))));
		$errorPageTemplate = 'error_page';
        $shape['title'] = 'Произошла ошибка';
        $shape['metas'] = '';
        $shape['gajs'] = self::$prodServer?shp::tmpl('parts/counters'):'';
        if ($e->getMessage()=='page_not_found') {
            header('HTTP/1.0 404 Not Found');
            //header('Location: /',true,404);
            $errorPageTemplate = 'error_pagenotfound';
            $shape['title'] = 'Страница не найдена';
        } else
            if ($e->getMessage()=='login_needs') {
                $url = $_SERVER['SCRIPT_URL']; if (trim($url,'/')==='_') $url = '/';
                shp::redirectHidden('/_auth/?url='.urlencode($url));
//                //header('HTTP/1.0 401 Unauthorized');
//                header('HTTP/1.0 404 Not Found');
//                header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
//                header('Expires: 0');
//                #var_dump__($e);
//                $shape['metas'] = '<meta http-equiv="Refresh" content="0; URL=/_auth/?url='.urlencode($url).'">';
//                $errorPageTemplate = 'error_pageauthneeds';
//                $shape['title'] = 'Страница не найдена';
            } else
                header('HTTP/1.0 500 Internal error');

        $shape['worktime'] = (microtime(true)- self::$time_start);
        $shape['reason'] = $e->getMessage();
        @ob_end_flush();
        echo shp::tmpl('errors/'.$errorPageTemplate, $shape);
    }
    public static function ShutdownHandler()
    {
		Global $cfg,$pathstr;
		if (self::$GlobalErrors!='') {
			CmsLogger::getTerminalsList();
			if (mb_stripos(self::$ErrorFirstTitle,'page_not_found')!==false && !core::$isAjax) {
				return;
                if (mb_substr($pathstr,-5,5)=='.map/') return;
                if (mb_substr($pathstr,-5,5)=='.php/' ||
                    mb_strpos($pathstr,'admin')!==false ||
                    mb_strpos($pathstr,'bitrix')!==false ||
                    mb_strpos($pathstr,'netcat')!==false ||
                    mb_strpos($pathstr,'cms')!==false ||
                    mb_strpos($pathstr,'manager')!==false ||
                    mb_strpos($pathstr,'wp-content')!==false ||
                    mb_strpos($pathstr,'login.html/')!==false
                ) return;
            }

            $emailTo = $cfg['email_error'];
            $ip = self::get_client_ip();
            $inf = '';
            //if (isset($_SERVER['HTTP_HOST'])) $inf .= " addr: " . $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] . PHP_EOL;
            if (isset($_SERVER['HTTP_USER_AGENT'])) $inf .= ' useragent: ' . $_SERVER['HTTP_USER_AGENT'] . PHP_EOL;
            if (isset($_SERVER['HTTP_REFERER'])) $inf .= ' referer: ' . $_SERVER['HTTP_REFERER'] . PHP_EOL;
            if ($ip!==false) $inf .= ' ip: ' . $ip  . PHP_EOL;
            $inf .= "\n";
            self::$GlobalErrors .= $inf;

            //$cfg['debug']===true
            //$host = $_SERVER['HTTP_HOST'];

            unset(
                $GLOBALS['cfg'], $GLOBALS['_SERVER'], $GLOBALS['page'], $GLOBALS['runObj'], $GLOBALS['shapes'],
                $GLOBALS['shape'], $GLOBALS['Cacher'], $GLOBALS['pagecontent'], $GLOBALS['e'], $GLOBALS['html'],
                $GLOBALS['remain'],$GLOBALS['sql']
            );
            $GlobalVars = CmsLogger::var_log_export($GLOBALS);
            $sent = CmsLogger::write(
                ( self::$IS_CLI?'':"\x1b[2J\x1b[H\x1b[3J" ). // Clear screen, move to left upper, clear all with scroll
                '=> ' . date('M d H:i:s ').self::$ErrorFirstTitle . PHP_EOL.
                "\x07\033]0;".date('M d H:i:s ').self::$ErrorFirstTitle."\007" .
                self::$GlobalErrors . $GlobalVars . '<=='.PHP_EOL
            );
            if (!$sent) $sent = @sendTelegram(self::$ErrorFirstTitle.PHP_EOL.$inf);
            if (!$sent) sendMailHTML($emailTo, 'ERROR '.self::$ErrorFirstTitle, self::ErrorsStringToHTML(self::$GlobalErrors).'<pre>'.$GlobalVars.'</pre>','',$cfg['email_from']);
            //if (self::$IS_CLI) sleep(2);
        }
        //if (isset(core::$prodServer)) try { new LiveinternetSeTracker($cfg['liveinternet_account']); } catch(Exception $e) {}
    }
    private static function ErrorsStringToHTML($errStr)
    {
        return str_replace(array("\n", ' '),array("<br>\n",'&nbsp;'),$errStr);//
    }
    public static function cms_autoload($class_name)
    {
        $classNameSlashed = str_replace(['_','\\'],'/',$class_name);
        $res = @include_once $classNameSlashed.'.php';
        if ($res===false) $res = @include_once $class_name.'.php';
        if ($res===false) {
            $bugtrace = debug_backtrace(0)[1];
            throw new CmsException('class_not_found: '.$class_name,-1,E_ERROR,
                isset($bugtrace['file'])?$bugtrace['file']:(isset($bugtrace['function'])?$bugtrace['function'].'(…)':''),
                isset($bugtrace['line'])?$bugtrace['line']:-1); // 5.3.0+
            //self::InTryErrorHandler(new CmsException('class_not_found: '.$class_name,0,E_USER_ERROR,$bugtrace['file'],$bugtrace['line'])); die();
        }
        #if ($cfg['debug']) var_dump_(debug_backtrace());
    }
    public static function getSharedObj($obj_name)
    {
        if (isset(self::$sharedObj[$obj_name])) return self::$sharedObj[$obj_name];
        else return self::$sharedObj[$obj_name] = new $obj_name();
    }
    public static function proceedAjax(){
        global $page;
        /* @var $page CmsPage */
        $outputData = '';
        $f = false;
        foreach ($page->initAjx() as $k => $v) {
            //echo self::$ajaxAction.'=='.$k."<br>\n";
            if (self::$ajaxAction==$k) {
                $runObj = &$page;
                if (isset($v['object'])) $runObj = &$v['object'];
                elseif (isset($v['class'])) $runObj = new $v['class']();
                $outputData = $runObj->{$v['func']}();
                $f = true;
                break;
            }
        }
        if (!$f) throw new CmsException('page_not_found');
        return $outputData;
    }
}
class shp{
    public static $editMode = false;
    public static function edtble(&$html, $vars=array()) //Возвращает готовый HTML код
    {
        $html=preg_replace_callback('/{#(e):(\w+)#}/u',function($matches){
            var_dump__($matches);
        },$html);
        return $html;
    }

    /** Hiden redirect to destination
     *  {#uri#} - template to back url encoded part to current uri
     * @param $url
     */
    public static function redirectHidden($url){
		//header('HTTP/1.0 401 Unauthorized');
		header('HTTP/1.0 404 Not Found');
		header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
		header('Expires: 0');
		$url = str_replace('{#uri#}',$_SERVER['REQUEST_URI'],$url);
		echo self::tmpl('errors/error_redirect_hidden',[
			'metas'=>'<meta http-equiv="Refresh" content="0; URL='.$url.'">',
			'js'=>'<script>document.location="'.$url.'";</script>',
		]);
		die;
	}

    /***
     * @param $html
     * html as shape
     * @param $vars
     * Parameters
     * @param bool $replace_once
     * @return mixed|null|string|string[]
     */
    public static function str($html, &$vars, $replace_once = false) //Возвращает готовый HTML код
    {
        $html=preg_replace_callback('/{#tmpl:(.*?)#}/u',function($matches) use (&$vars,$replace_once){
            return self::tmpl($matches[1], $vars, $replace_once);
        },$html);

        foreach ($vars as $key => $val) if ($replace_once) {
            $html=preg_replace('/{#'.$key.'#}/',$val,$html,1);
            $html=str_replace('{#'.$key.'#}','',$html);
        } else {
            $html=str_replace('{#'.$key.'#}',$val,$html);
        }
        return $html;
    }

    /***
     * @param $template
     * Shape name
     * @param array $vars
     * Parameters
     * @param bool $replace_once
     * @return mixed|null|string|string[]
     */
    public static function tmpl($template, $vars=[], $replace_once = false) //Возвращает готовый HTML код
    {
        global $shapes;
        if (empty($shapes[$template]))
            $shapes[$template]=load_filecheck($template.'.shtm',true);
        $html=$shapes[$template];

        $html=self::str($html, $vars, $replace_once);
        return $html;
    }

    public static function tmpl_e($template,$vars = []) {
        global $page;
        $execIntoScope = function($template,$data){
            if (is_array($data)) extract($data,EXTR_PREFIX_SAME,'new_');
            ob_start();
            include 'akcms/u/template/parts/' . $template . '.php';
            return ob_get_clean();
        };
        $childHtml = $execIntoScope($template, $vars);
        $isEdit = $page->inEditCan && PageOutACL::getInstance($page->page)->hasRight(); //Персональные права этой страницы
        VisualTheme::replaceStaticHolders($childHtml, $page->page, $isEdit);
        return $childHtml;
    }
}