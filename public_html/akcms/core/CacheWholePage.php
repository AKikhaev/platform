<?php
class CacheWholePage {
    public static $cacheThis = false;
    public static $life=31536000; // 86400*365

    /** Cache whole url when $cacheThis is true
     * @param $content
     * @param int $life
     */
    public static function cacheTry($content) {
        if (!self::$cacheThis) return;
        global $Cacher;
        $key = $_SERVER['REQUEST_URI'];
        $Cacher->cache_write($key,$content,self::$life);
    }

    /**
     * @return bool
     */
    private static function outWholePage() {
        global $Cacher;
        $key = $_SERVER['REQUEST_URI'];
        $content = '';
        if (
            //!CmsUser::isLogin() &&
            !core::$isAjax &&
            !(isset($_COOKIE['cacheskip']) && $_COOKIE['cacheskip']=='1') &&
            $Cacher->cache_read($key,$content)
        ) {
            //$Cacher->cache_drop($key);
            ChromePhp::log($key);
            echo $content; die;
        }
        return false;
    }

    /**
     * CacheWholePage constructor.
     * @throws CmsException
     */
    public function __construct()
    {
        if (!self::outWholePage()) { throw new CmsException("page_not_found"); }
    }
}
