<?php

class FeedUnit extends CmsPage {

	function initAjx()
	{
		$ajaxes = array(
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
  
	function __construct(&$pageTemplate)
	{
		global $sql,$cfg,$pathlen,$path;
        if ($pathlen==2 && $path[0]=='feed') {

            #<link rel="alternate" type="application/rss+xml" title="RSS" href="/feed/rss2/" />
            #<link rel="alternate" type="application/atom+xml" title="ATOM" href="/feed/atom/" />

            ob_end_clean();
            $domain = 'http://' . $_SERVER['SERVER_NAME'];
            $pathstr_part = $GLOBALS['path'][1];
            $query = sprintf('SELECT sec_url_full,sec_namefull,sec_imgfile,sec_contshort,sec_content,sec_from FROM cms_sections
		    WHERE not sec_system AND sec_enabled AND sec_to_news AND sec_content<>\'\' AND sec_from<NOW() ORDER BY sec_from DESC LIMIT 20;');
            $dataset = $sql->query_all($query);
            if ($dataset !== false) {

                include("akcms/classes/FeedWriter/FeedTypes.php");
                if ($pathstr_part == 'rss2') {
                    $feed = new RSS2FeedWriter();

                    $feed->setTitle($cfg['feed_title']);
                    $feed->setLink($domain . '/feed_rss/');
                    $feed->setDescription($cfg['feed_desc']);
                    //$feed->setImage($cfg['feed_title'],$domain.'/feed_rss/',$domain.'/img/t/rssLogo.jpg');

                    foreach ($dataset as $idata) {
                        if ($idata['sec_contshort'] != '') {
                            $desc = strip_tags(str_replace('// <![CDATA[', '<![CDATA[', $idata['sec_contshort']));
                        } else {
                            $desc = GetTruncText(strip_tags(str_replace('// <![CDATA[', '<![CDATA[', $idata['sec_content'])), 200);
                        }
                        $newItem = $feed->createNewItem();
                        $newItem->setTitle($idata['sec_namefull']);
                        $newItem->setLink($domain . '/' . $idata['sec_url_full']);
                        $newItem->setDate(date('U', strtotime($idata['sec_from'])));
                        $newItem->setDescription($desc);
                        //$newItem->setEncloser('http://www.attrtest.com', '1283629', 'audio/mpeg');
                        //$newItem->addElement('author', 'admin@ajaxray.com (Anis uddin Ahmad)');
                        //$newItem->addElement('guid', 'http://www.ajaxray.com',array('isPermaLink'=>'true'));

                        //Now add the feed item
                        $feed->addItem($newItem);
                    }
                    $feed->generateFeed();
                } else
                    if ($pathstr_part == 'atom') {
                        $feed = new ATOMFeedWriter();

                        $feed->setTitle($cfg['feed_title']);
                        $feed->setLink($domain . '/feed_rss/');
                        $feed->setChannelElement('updated', date(DATE_ATOM, strtotime($dataset[0]['sec_from'])));
                        //$feed->setImage($cfg['feed_title'],$domain.'/feed_rss/',$domain.'/img/t/rssLogo.jpg');

                        foreach ($dataset as $idata) {
                            if ($idata['sec_contshort'] != '') {
                                $desc = strip_tags(str_replace('// <![CDATA[', '<![CDATA[', $idata['sec_contshort']));
                            } else {
                                $desc = GetTruncText(strip_tags(str_replace('// <![CDATA[', '<![CDATA[', $idata['sec_content'])), 200);
                            }
                            $newItem = $feed->createNewItem();
                            $newItem->setTitle($idata['sec_namefull']);
                            $newItem->setLink($domain . '/' . $idata['sec_url_full']);
                            $newItem->setDate(date('U', strtotime($idata['sec_from'])));
                            $newItem->setDescription($desc);

                            //Now add the feed item
                            $feed->addItem($newItem);
                        }
                        $feed->generateFeed();
                    } else throw new CmsException("page_not_found");
            }
            die();
        } else {
            throw new CmsException('page_not_found');
        }
	}

  #Content
	function getContent()
	{
		return '';
	}
}