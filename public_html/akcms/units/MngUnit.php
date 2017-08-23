<?php

class MngUnit extends CmsPage {

	function initAjx()
	{
		$ajaxes = array(
			'_mng' => array(
				'func' => 'ajxGenQR'),
			'_auth' => array(
				'func' => 'ajxAuth'),
		);
		return $ajaxes;
	}
  
	function _rigthList()
	{
		return array(
		);
	}

	function initAcl()
	{
		return array(
		'admin'=>true,
		'owner'=>true,
		'default'=>null
		);
	}

	function ajxAuth(){
		$checkRule = array();
		$checkRule[] = array('l', '.');
		$checkRule[] = array('p', '.');
		$checkResult = checkForm($_POST,$checkRule);
		if (count($checkResult)==0) {
            if (CmsUser::auth($_POST['l'],$_POST['p'])) return json_encode('t');
			else $checkResult[] = array('f'=>'!','s'=>'wrong');
		} 
		return json_encode(array('error'=>$checkResult));		
	}
	
	function ajxGenQR() {
		global $cfg;
		if (!isset($_COOKIE[$cfg['site_session_name_qr']])) 
			$qr = mb_substr(md5(time()),0,10);
		else 
			$qr = $_COOKIE[$cfg['site_session_name_qr']];
		setcookie($cfg['site_session_name_qr'], $qr, time()+600, '/');
		return json_encode(mb_substr(md5($qr.'!'),1,11));
	}

	function __construct(&$pageTemplate)
	{
		global $cfg,$shape,$Cacher;

		$pathstr_part = $GLOBALS['path'][0];

		if ($pathstr_part=='_auth') {
            core::$renderPage = true;
            $this->title = 'Добро пожаловать';

            $shape['cval'] = md5($_SERVER['REMOTE_ADDR']);
			$shape['msg'] = '';
			$shape['location'] = '';
			$location = (isset($_GET['url'])?!empty($_GET['url']):false)?$_GET['url']:'/_/';
            if (isset($_GET['c'])) {
                $pageTemplate = 'login_qr';
            } else {
				$pageTemplate = 'login';
				if (isset($_COOKIE[$cfg['site_session_name_qr']])) {
					$qr = $_COOKIE[$cfg['site_session_name_qr']];
					$data = array();
					if ($Cacher->cache_read_drop($cfg['site_session_name_qr'].'_'.mb_substr(md5($qr.'!'),1,11),$data)) {
						if (!isset($_COOKIE[session_name()])) session_start();
                        $_SESSION['u'] = $data['u'];
						$_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
						header('Location: '.$location);
					}
				}
			}
			if (isset($_POST['do'])) {
				$checkRule = array();
				$checkRule[] = array('lval'     , '.');
				$checkRule[] = array('pval'     , '.');
				$checkResult = checkForm($_POST,$checkRule,$_POST['cval']==substr($shape['cval'],4,5).substr($shape['cval'],1,2));
				if (count($checkResult)==0)
				{
					if (CmsUser::auth($_POST['lval'],$_POST['pval'])) {
						if (isset($_GET['c'])) {
							$Cacher->cache_write($cfg['site_session_name_qr'].'_'.$_GET['c'],$_SESSION,600);
						}
						header('Location: '.$location);
					}
					else $shape['msg'] = 'Неверное имя или пароль!';
				} else $shape['msg'] = 'Введите имя и пароль!';
			}
		} elseif ($pathstr_part=='_logout') {
            core::$renderPage = true;
			$pageTemplate = 'logout';
			CmsUser::logout();
            $shape['newurl'] = '/';
            //$shape['newurl'] = isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'/';
		} elseif ($pathstr_part==':auth' && core::$isAjax) {
        }
		else throw new CmsException('page_not_found');

	}

    static function getContent() {}
}