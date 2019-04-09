<?php
$cfg['debug'] = true;
$cfg['site_domain'] = 'site.ru';
$cfg['site_map_urlpref'] = 'http://site.ru/';								    ###
$cfg['site_map_file'] = 'sitemap.xml';
$cfg['site_title'] = 'Main page title ';                                 ### page title - <this text>
$cfg['feed_title'] = $cfg['site_title'];										                ###
$cfg['feed_desc'] = 'Feeds description, usually shows under site name';	###
$cfg['site_cookies_domain'] = '.site.ru';							                    ###
$cfg['email_error'] = 'aliday.pr@gmail.com';
$cfg['email_moderator'] = 'aliday.pr@gmail.com';
$cfg['email_from'] = 'script <no-reply@'.$cfg['site_domain'].'>';
$cfg['email_from_user'] = 'no-reply@'.$cfg['site_domain'];						                ###
$cfg['usrprepass'] = 'dU%f:';
$cfg['default_timezone'] = 'Europe/Moscow'; 
#$cfg['site_cookies_expire'] = mktime(0, 0, 1, 3, 24, 2029);
$cfg['site_cookies_expire'] = time()*20*365*24*60*60;
$cfg['site_session_name'] = 'aks';
$cfg['site_session_name_qr'] = 'akq';
$cfg['site_session_parameters'] = [];
$cfg['ga_account'] = 'UA-XXXXXX-XX';											                ###
#$cfg['liveinternet_account'] = 'beside.ru';									                ###
$cfg['imagespath'] = 's/images/';
$cfg['filespath'] = 's/files/';
$cfg['server_test'] = array('site.ru.local');								                ###
$cfg['server_prod'] = array('site.ru','www.site.ru');									            ###
$cfg['domains_redirects'] = array();//array('www.knpz-ken.ru'=>'http://knpz-ken.ru');	        ###
$cfg['domains_approved'] = array('site.ru','www.site.ru','site.ru.local');			        ###
$cfg['telegramId'] = '203405254'; // Телеграм notify id
$cfg['user_terminal_uid'] = 1000;
##$cfg['memcache']['host'] = 'localhost';
$cfg['filecache']['path'] = '../cache/';
$cfg['db_count'] = 1;
$cfg['db'][1]['schema']   = 'site_ru';
$cfg['db'][1]['host'] = '127.0.0.1';
$cfg['db'][1]['database'] = 'site_ru';
$cfg['db'][1]['username'] = 'site_ru';
$cfg['db'][1]['password'] = '$uPeRCrypt0H@arDpsW';
$cfg['CmsPages_load'] = [
    'PageUnit',
    'MngUnit',
    'SysUnit',
    //'FeedUnit',
    'TmplMapperUnit',
    'RootFolder',
    'CheckFileMoved',
];
$cfg['pages'] = array(
    'text'=>'Текст',
    'obshinf'=>'Общая информация',
    'rukovodstvo'=>'Руководство',
    'rabotaunas'=>'Работа у нас',
    'products'=>'Продукция',
    'novosti_item'=>'Новость',
    'pressa_item'=>'Публикация прессы',
    'novosti'=>'Новости',
    'pressa'=>'Пресса о нас',
    'fotoarhiv'=>'Фотоархив',
    'obyavlenie'=>'Объявление',
    'raskrinf'=>'Раскрытие инф',
    'tendery'=>'Тендеры',
    'contact'=>'Контакты',
    'rabotaunas_vacancy'=>'Вакансия',
    'tendery_tender'=>'Тендер',
    'loginpage'=>'Кабинет',
    'index'=>'Главная',
);
$cfg['pgunits_hidden'] = array(
    'Pg_SiteMap'=>'Карта сайта',
    'Pg_Search'=>'Поиск',
    'Pg_UsersMng'=>'Управление пользователями',
);
$cfg['pgunits'] = array(
	'Pg_SubSecLst'=>'Рубрики раздела',

	'Pg_Gallery'=>'Галерея основная',
	#'Pg_GalleryInject'=>'Галерея встраиваемая',
	'Pg_GalleryMini'=>'Галерея мини',

	'Pg_SendLetter'=>'Написать письмо',
	'Pg_SiteMap'=>'Карта сайта',
	#'Pg_Search'=>'Поиск',
	#'Pg_Media'=>'Восп. аудио/видео',
	#'Pg_Banners'=>'Управление баннерами',
    'Pg_UsersMng'=>'Управление пользователями',
);