<?php #❶❷❸❹❺❻❼❽❾
#core::$time_start = microtime(true);
# error tracking
try {
    require_once('akcms/core/core.php'); LOAD_CORE(); # init core
    /* @var $sql pgdb */

    #ob_start(); // Start output buffer

    $pageClass = '';
    $pageTemplate = '';
    core::$renderPage = core::$userAuth || core::$inEdit || core::$isAjax;

    if ($pathlen==1 && ($path[0]=='_auth' || $path[0]=='_mng' || $path[0]=='_logout')) {
        $pageClass = 'MngUnit';
        core::$renderPage = true;
    }
    elseif ($pathlen==2 && $path[0]=='feed')
        $pageClass = 'FeedUnit';
    elseif ($pathlen==2 && $path[0]=='_sys') {
        $pageClass = 'SysUnit';
        core::$renderPage = true;
    }
    elseif($pathlen==1 && $path[0]=='bot_test') {
        $tb = new TelegramBot();
        $tb->webHook();
        die('.');
    }
    else
        $pageClass = 'PageUnit';

    //sendTelegram($pathurl.' '.core::get_client_ip());

    /* @var  $page CmsPage */
    $page = new $pageClass($pageTemplate);
    #if ($pageClass=='') throw new CmsException("page_not_found");
    if (core::$isAjax) {
        core::$outputData .= core::proceedAjax();
    }
    else {
        if (core::$inEdit) {
            $shape['content'] = $page->getContent();
            $shape['title'] = $page->getTitle();
            #$shape['worktime'] = (microtime(true)-core::$time_start);
            core::$outputData = GetShape('pages/'.$pageTemplate, $shape, true);
        } else {
            $html = '';
            #if ($_SERVER['REMOTE_ADDR']=='109.172.77.170') var_dump__($pathstr);
            #if (core::$renderPage || !$Cacher->cache_read($pathstr,$html))
            {
                $pagecontent = $page->getContent();

                $pagecontent = preg_replace('/\<img\s/','<img itemprop="image" ',$pagecontent,1);

                $shape['title'] = $page->getTitle();
                #$shape['worktime'] = (microtime(true)-core::$time_start);

                $html = shp::tmpl('pages/'.$pageTemplate,array('content'=>$pagecontent));
                $html = shp::str($html, $shape, false);
                //$html = Minify_HTML::minify($html);

                #if (!core::$userAuth && $page->canCache()) $Cacher->cache_write($pathstr,$html,600);
            }
            core::$outputData = $html;
            unset($html);
        }
    }
    #$outputData = ob_get_contents(); ob_end_clean();
    #gzipOutput($outputData);
    echo core::$outputData;

} catch(Exception $e) {
    if (class_exists('core') && function_exists('GetShape')) core::InTryErrorHandler($e);
    else echo '<script>console.log('.json_encode(sprintf("%s, %s(%s)",$e->getMessage(),basename($e->getFile(),'php'),$e->getLine())).')</script>';
    unset($e);
}
