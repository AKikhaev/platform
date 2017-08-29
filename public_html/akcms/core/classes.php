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
		if (mb_stripos($text,'ERROR:  duplicate key value violates unique constraint')!==false) {
			$this->isDuplicate = true;
			$matches = array();
			if (preg_match('/DETAIL:  Key \(([^\)]+)\)/ui',$text,$matches)>0) $this->field = $matches[1];
		}
		parent::__construct($message."\n".$text);
	}
}

abstract class AclProcessor { /* acl */
	protected $thisOwner = false;
	protected function initAcl() {return array();} // return array(); ...,owner,admin,idividual
	protected function isOwner() {return $this->thisOwner;}  
	protected function isSpecifiedRight($rightName) {return null;}

	protected function checkRight($class,$rightName) {
		#echo "[$class.$rightName]";
		$rightRes = null;
		$needRights = $this->initAcl();
		$userRights = CmsUser::$rights;
		if ($this->isOwner()) $userRights[] = CMS_OBJOWNER;
		$specifiedRight = $this->isSpecifiedRight($rightName);
		if ($specifiedRight != null) $rightRes = $specifiedRight;
		if ($rightRes !== false) {
			foreach ($needRights as $needRight=>$v)
				if (isset($userRights[$needRight])){
					$rightRes = $userRights[$needRight];
					break;
				}
		}
		if ($rightRes !== false && isset($userRights["$class.$rightName"])) $rightRes = $userRights["$class.$rightName"];
		if ($rightRes !== false && isset($userRights["$class.*"])) $rightRes = $userRights["$class.*"];
		if ($rightRes !== false && isset($userRights["*.$rightName"])) $rightRes = $userRights["*.$rightName"];
		if ($rightRes === null) $rightRes = false;
		#print_r($rightRes);
		return $rightRes;
	}

	protected function hasRight($rightName = null) {
		$stacktrace = debug_backtrace(false);
		if (isset($stacktrace[1])) {
			$class = $stacktrace[1]['class'];
			$method = $stacktrace[1]['function'];
			return $this->checkRight($class,$rightName===null?$method:$rightName);
		}
		else throw new CmsException('core_error');
	}
}

abstract class CmsPage extends AclProcessor { /* page */
    public $page = [];
	protected $title;
	protected $cacheWholePage = true;
	public function canCache() { return $this->cacheWholePage;}
	public function noCache() { $this->cacheWholePage=false; }
	public function __construct(&$pageTemplate) {}
	public function getTitle() {return $this->title;}
	public function initAjx() {return array();}
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
	public function initAjx() {return array();}
	public static function buildLevelSiteMap(&$putInto,$parentId,$parentUrlFull,$showHidden = false) {} // Строит карту сайта
}

class CacheController { /* cache */
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
			if (time()<$c_obj['u']) {
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
  
	public static function auth($login,$password) {
		global $sql;
		$query = sprintf('select * from cms_users where usr_login = %s and usr_password_md5 = %s and usr_enabled and usr_activated limit 1;', 
			$sql->t($login),
			$sql->t(md5($GLOBALS['cfg']['usrprepass'].$password)));
		$datausr = $sql->query_first_assoc($query);
		if ($datausr!==false?$datausr['usr_login']==$login:false) {
			if (!isset($_COOKIE[session_name()])) session_start();
			$_SESSION['u'] = $login;
			$_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
			self::$user = $datausr;
			return true;
		} else return false;
	}
  
	public static function hasLogin($login) {
		global $sql;
		$query = sprintf('select id_usr from cms_users where usr_login ilike %s;', 
			$sql->t($login));
		$datausr = $sql->query_first_assoc($query);
		return $datausr!==false?$datausr['id_usr']:false;
	}
	
    public static function isLogin() {
        return count(self::$user)>0;
    }
  
	public static function logout() {
		$_SESSION = array();		
		if (isset($_COOKIE[session_name()])) {
			setcookie(session_name(), '', time()-42000, '/');
			@session_destroy();
			self::$user = array();
		}
	}
	
	public static function register($login,$email,$password,$name) {
		global $sql;
		$actcode = md5('юhuu'.(time()*2-2.899));
		$query = sprintf('INSERT INTO cms_users (usr_login,usr_email,usr_password_md5,usr_name,usr_actcode) VALUES(%s,%s,%s,%s,%s) RETURNING id_usr;', 
			$sql->t($login),
			$sql->t($email),
			$sql->t(md5($GLOBALS['cfg']['usrprepass'].$password)),
			$sql->t($name),
			$sql->t($actcode)
		);
		$res = $sql->query_first_row($query);
		return $res!=false?array('id'=>$res[0],'actcode'=>$actcode):false;
	}

	public static function newLostcode($login) {
		global $sql;
		$lostcode = md5('яhuu'.(time()*2-2.897));
		$query = sprintf('UPDATE cms_users SET usr_lostcode = %s WHERE usr_login = %s RETURNING usr_email;',
			$sql->t($lostcode),
			$sql->t($login)
		);
		$res = $sql->query_first_row($query);
		return $res!=false?array('email'=>$res[0],'lostcode'=>$lostcode):false;
	}

	public static function newLostpass($login,$lostcode,$password) {
		global $sql;
		$query = sprintf('UPDATE cms_users SET usr_lostcode = \'\', usr_password_md5=%s WHERE usr_lostcode=%s AND usr_login = %s RETURNING usr_email;',
			$sql->t(md5($GLOBALS['cfg']['usrprepass'].$password)),
			$sql->t($lostcode),
			$sql->t($login)
		);
		$res = $sql->query_first_row($query);
		return $res!=false?array('email'=>$res[0]):false;
	}
	
	public static function checkLostcode($login,$code) {
		global $sql;
		if ($code=='') return false;
		$query = sprintf('SELECT count(*) as reccount FROM cms_users WHERE usr_login = %s AND usr_lostcode = %s;',
			$sql->t($login),
			$sql->t($code)
		);
		$res = $sql->query_first_row($query);
		return $res!=false?$res[0]>0:false;
	}	
	
	public static function activate($login,$actcode) {
		global $sql;
		$query = sprintf('UPDATE cms_users SET usr_activated = true WHERE usr_enabled AND NOT usr_activated AND usr_login = %s AND usr_actcode = %s;', 
			$sql->t($login),
			$sql->t($actcode)
		);
		$res = $sql->command($query);
		return $res>0;
	}
	
	public static function init() {
		global $sql;
		if (isset($_COOKIE[session_name()])) {
			session_start();
			if (isset($_SESSION['u'],$_SESSION['ip'])) {
				if ($_SESSION['ip'] != $_SERVER['REMOTE_ADDR']) self::logout(); else {
					$login = $_SESSION['u'];
					$query = sprintf('select *,array(SELECT (__if(usrrght_mode,\'\',\'!\'::text)||usrrght_name) FROM cms_users_groups_rgth where usrrght_grpid=any(usr_grp)) as rights from cms_users where usr_login = %s and usr_enabled and usr_activated limit 1;', 
						$sql->t($login));
					$datausr = $sql->query_first_assoc($query);
					if ($datausr!==false?$datausr['usr_login']==$login:false) {
						self::$rights = array();
						foreach (explode(',',trim($datausr['rights'],'}{')) as $right) {
							$mode = true;
							if (strpos($right, '!') === 0) {
								$right = substr($right,1);
								$mode = false;
							}
							if (!isset(self::$rights[$right]) || self::$rights[$right]===true) self::$rights[$right] = $mode;
						}
						self::$user = $datausr;
						#if ($datausr['usr_admin']=='t') CmsUser::$groups[] = 'admin';
					} else self::logout();
				}
            } #else CmsUser::logout();
		}
	}
}
class core {
    public static $isAjax = false;
    public static $ajaxAction = '';
    public static $inEdit = false;
    public static $serverName = '';
    public static $testServer = false;
    public static $prodServer = false;
    public static $userAuth = false;
    public static $time_start = 0;
    private static $sharedObj = array();
    private static $GlobalErrors = '';
    private static $ErrorFirstTitle = '';
    private static $terminals = array();
    public static $outputData = '';
    public static $renderPage;

    public static function get_client_ip() {
        $ipaddress = getenv('HTTP_CLIENT_IP');
        if ($ipaddress==false) $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        if ($ipaddress==false) $ipaddress = getenv('HTTP_X_FORWARDED');
        if ($ipaddress==false) $ipaddress = getenv('HTTP_FORWARDED_FOR');
        if ($ipaddress==false) $ipaddress = getenv('HTTP_FORWARDED');
        if ($ipaddress==false) $ipaddress = getenv('REMOTE_ADDR');
        return $ipaddress;
    }
    private static function hidePathForError($filename) {
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') $filename = str_replace('\\','/',$filename);
		if (isset($_SERVER) && strpos($filename,$_SERVER['DOCUMENT_ROOT'])!==false)
    		$filename = substr($filename,strlen($_SERVER['DOCUMENT_ROOT']));
    	else $filename = basename($filename);
		return $filename;
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
            	self::$ErrorFirstTitle = $errortype[$errno].': '.$errmsg.' '.
					($_SERVER['SERVER_NAME']?:'').' '.($_SERVER['REQUEST_URI']?:'');
            $err .= 'src: ' . self::hidePathForError($filename).': '.$linenum . "\n";
            if (in_array($errno, array(E_USER_ERROR, E_USER_WARNING, E_USER_NOTICE, E_NOTICE, E_ERROR, E_WARNING, -1))) {#E_WARNING,
                $tracedata = array();
                if (!isset($backtrace[0])) $backtrace = debug_backtrace(0);
                foreach($backtrace as $k=>$d) if (is_array($d)) {
                    $i='  '.$k.': ';
                    if (isset($d['file'])) $i  .= self::hidePathForError($d['file']);
                    if (isset($d['line'])) $i  .= ':'.$d['line'];
                    if (isset($d['class'])) $i .= ' {'.$d['class'].'}';
                    if (isset($d['type'])) $i  .= ' '.$d['type'];
                    if (isset($d['function'])) $i .= ' '.$d['function'];
                    $tracedata[] = $i;
                }
                $err .= implode("\n",$tracedata) . "\n";
            }
            if (isset($cfg['debug']) && $cfg['debug']===true) {
                if (self::$isAjax) {}//echo '/* '.$err.' */';
                else echo '<script>console.log('.json_encode($err).');</script>';
                //else echo '<!--'.$err.'-->';
                //echo ErrorsStringToHTML($err);
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
        if (self::$isAjax) return; //json_encode(array('error'=>array(array('f'=>'system','s'=>'failure'))));
        $errorPageTemplate = 'error_page';
        $shape['title'] = 'Прозиошла ошибка';
        $shape['metas'] = '';
        $shape['gajs'] = self::$prodServer?GetShape('parts/counters'):'';
        if ($e->getMessage()=='page_not_found') {
            header('HTTP/1.0 404 Not Found');
            //header('Location: /',true,404);
            $errorPageTemplate = 'error_pagenotfound';
            $shape['title'] = 'Страница не найдена';
        } else
            if ($e->getMessage()=='login_needs') {
                header('HTTP/1.0 401 Unauthorized');
                #var_dump__($e);
                $shape['metas'] = '<meta http-equiv="Refresh" content="0; URL=/_auth/?url='.urlencode($GLOBALS['pathurl']).'">';
                $errorPageTemplate = 'error_pageauthneeds';
                $shape['title'] = 'Страница не найдена';
            } else
                header('HTTP/1.0 500 Internal error');

        $shape['worktime'] = (microtime(true)- self::$time_start);
        $shape['reason'] = $e->getMessage();
        @ob_end_flush();
        echo GetShape('errors/'.$errorPageTemplate, $shape);
    }
    public static function ShutdownHandler()
    {
        Global $cfg;
        if (self::$GlobalErrors!='') {
            if (mb_stripos(self::$ErrorFirstTitle,'page_not_found')!==false && mb_substr(self::$ErrorFirstTitle,-5,5)=='.map/') return;

            $emailTo = $cfg['email_error'];
            $inf = "sessioninfo:\n";
            //if (isset($_SERVER['SERVER_NAME'])) $inf .= " addr: " . $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] . "\n";
            if (isset($_SERVER['HTTP_USER_AGENT'])) $inf .= ' useragent: ' . $_SERVER['HTTP_USER_AGENT'] . "\n";
            if (isset($_SERVER['HTTP_REFERER'])) $inf .= ' referer: ' . $_SERVER['HTTP_REFERER'] . "\n";
            if (isset($_SERVER['REMOTE_ADDR'])) $inf .= ' ip: ' . $_SERVER['REMOTE_ADDR'] . "\n";
            $inf .= "\n";
            self::$GlobalErrors .= $inf;

            //$cfg['debug']===true
            $host = $_SERVER['HTTP_HOST'];

            unset($GLOBALS['cfg'], $GLOBALS['_SERVER'], $GLOBALS['page'], $GLOBALS['runObj'], $GLOBALS['shapes'], $GLOBALS['shape'], $GLOBALS['Cacher'], $GLOBALS['pagecontent'], $GLOBALS['e'], $GLOBALS['html']);
            $GlobalVars = print_r($GLOBALS,true);
            $GlobalVars = preg_replace('/Array\n\s*/','Array',$GlobalVars);
            $GlobalVars = preg_replace('/\n\s+\(/','(',$GlobalVars);
            $GlobalVars = preg_replace('/\n\s+\)/',')',$GlobalVars);
            $GlobalVars = preg_replace('/\n\s*\n/',"\n",$GlobalVars);

            $sent = self::terminalWrite(
                "\x07\x1b[2J\x1b[H\x1b[3J" .
                "\033]0;".date('M d H:i:s ').self::$ErrorFirstTitle."\007" .
                '=> ' . date('M d H:i:s ').self::$ErrorFirstTitle . "\n" . self::$GlobalErrors . $GlobalVars . '<=='
            );
            if (!$sent) $sent = sendTelegram(self::$ErrorFirstTitle.' '. self::get_client_ip());
            if (!$sent) sendMailHTML($emailTo, 'ERROR '.self::$ErrorFirstTitle, self::ErrorsStringToHTML(self::$GlobalErrors).'<pre>'.$GlobalVars.'</pre>','',$cfg['email_from']);
        }
        //if (isset(core::$prodServer)) try { new LiveinternetSeTracker($cfg['liveinternet_account']); } catch(Exception $e) {}
    }
    private static function ErrorsStringToHTML($errStr)
    {
        return str_replace(array("\n", ' '),array("<br>\n",'&nbsp;'),$errStr);//
    }
    private static function SendErrorMessage($errStr,$title='',$wrn='WARNING') {
        Global $cfg;
        $emailTo = $cfg['email_error'];
        sendMailHTML($emailTo, $wrn.' '.$_SERVER['HTTP_HOST'].' '.$title, $errStr,'',$cfg['email_from']);
    }

    public static function cms_autoload($class_name)
    {
        $class_name = str_replace('_','/',$class_name);
        $res = @include_once $class_name.'.php';
        if ($res==false) {
            $bugtrace = debug_backtrace(0)[1];
            throw new CmsException('class_not_found: '.$class_name,0,E_USER_ERROR,
                isset($bugtrace['file'])?$bugtrace['file']:'',
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
    public static function getTerminalsList(){
        $out = array();
        $currentUser = get_current_user();
        $ip = self::get_client_ip();
        exec('who| grep '.$currentUser.' | grep '.$ip,$terminalsRaw);
        foreach ($terminalsRaw as $data) {
            if (preg_match('/([A-Za-z0-9_-]+)\s+([a-zA-Z0-9\/]+)\s+([\d-]+)\s+([\d:]+)\s+\(([\d.]+)\)/iu', $data, $terminalInfo)) {
                $out[] = array(
                    'user' => $terminalInfo[1],
                    'terminal' => $terminalInfo[2],
                    'date' => $terminalInfo[3],
                    'time' => $terminalInfo[4],
                    'ip' => $terminalInfo[5],
                );
            }
        }

        return self::$terminals = array_reverse($out);
    }
    public static function terminalWrite($data, $terminal=null){

        if ($terminal==null) {
            if (!isset(self::$terminals[0])) self::getTerminalsList();
            if (isset(self::$terminals[0])) $terminal = self::$terminals[0]['terminal'];
            else return false;
        }
        //exec('who > /dev/pts/1');
        $tty = fopen('/dev/'.$terminal, 'wb');
        $r = fwrite($tty, "$data\n");
        fclose($tty);
        return $r !== false;
    }
    public static function terminalClear($terminal=null) { self::terminalWrite("\e[2J\e[H\e[3J",$terminal); }
    public static function terminalBeep($terminal=null) { self::terminalWrite("\x07",$terminal); }
    public static function proceedAjax(){
        global $page;
        /* @var $page CmsPage */
        $outputData = '';
        $f = false;
        foreach ($page->initAjx() as $k => $v) {
            //echo $pathstr.'=='.$k."<br>\n";
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
    public static function tmpl($shape, $vars=array(), $replace_once = false) //Возвращает готовый HTML код
    {
        global $shapes;

        if (empty($shapes[$shape]))
            $shapes[$shape]=load_filecheck($shape.'.shtm',true);
        $html=$shapes[$shape];

        $html=self::str($html, $vars, $replace_once);
        return $html;
    }
}