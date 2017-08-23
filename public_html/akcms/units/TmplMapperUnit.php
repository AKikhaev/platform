<?php

class TmplMapperUnit extends CmsPage {

	function initAjx()
	{
		$ajaxes = array(
			'_cr' => array(
				'func' => 'ajxCreateRegion'
            ),
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

	function prepareHtml($html){
	    $html = str_replace('<br/>','<br>',$html);
        $html = str_replace('<br />','<br>',$html);
        $html = str_replace("\r\n","\n",$html);
	    return $html;
    }

	function ajxCreateRegion(){
        $checkRule = array();
        $checkRule[] = array('html','');
        $checkRule[] = array('locate','.');
        $checkRule[] = array('label','.');
        $checkResult = checkForm($_POST,$checkRule,$this->hasRight());
        if (count($checkResult)==0) {
            $data = $_POST;
            $result = [];
            $data['html'] = $this->prepareHtml($data['html']);
            $data['locate'] =  $this->prepareHtml($data['locate']);

            file_put_contents('../html.txt',$data['html']);
            file_put_contents('../locate.txt',$data['locate']);

            $template = $GLOBALS['path']; unset($template[0]); $data['template'] = 'akcms/u/template/'.implode('/',$template).'.shtm';
            copy($data['template'],$data['template'].'.bak');

            $html =  $this->prepareHtml(file_get_contents($data['template'])); $out = '';
            $data['html_count_'] = substr_count($data['locate'],$data['html']);
            $data['locate_count_'] = substr_count($html,$data['locate']);

            core::terminalClear();

            $label = str_replace('!','',$data['label'],$replaceAnchor);
            $label = str_replace('*','',$label,$withoutClose);
            $htmls = explode($data['locate'],$html);
            //var_log_terminal(count($htmls)); die();
            if (count($htmls)==2) {
                if ($replaceAnchor) {
                    $out = $htmls[0].
                        '{#'.$label.'#}'.
                        $data['locate'].
                        ($withoutClose>0? '' : '{/#'.$label.'#}').
                        $htmls[1];
                } else {
                    $locates = explode($data['html'], $data['locate']);
                    if (count($locates) == 2) {
                        $out = $htmls[0].$locates[0].
                            '{#'.$label.'#}'.
                            $data['html'].
                            ($withoutClose>0? '' : '{/#'.$label.'#}').
                            $locates[1].$htmls[1];
                    } elseif (count($locates) == 1) $result['error'] = 'Фрагмент не найден!';
                    else $result['error'] = 'Более одного вхождения фрагмента! ' . count($locates);
                }
            } elseif (count($htmls)==1) $result['error'] = 'Родитель не найден! ';
            else $result['error'] = 'Более одного вхождения родителя! '.count($htmls);

            if ($out!='') {
                $written  = file_put_contents($data['template'],$out);
                $result['status'] = 'Шаблон обновлен! Размер файла '.$written;
            }
            //$data['html_places'] = mb_strpos_all($data['locate'],$data['html']);

            var_log_terminal($data);
            return json_encode($result);
        }
        return json_encode(array('error'=>$checkResult));

    }

	function __construct(&$pageTemplate)
	{
		global $pathlen;

		if ($GLOBALS['path'][0]=='_tmpl') {
            if (!core::$userAuth) throw new CmsException("login_needs");
            if (core::$isAjax) return;
            if ($pathlen==1) {
                $templates = [];
                foreach(glob('akcms/u/template/*/*.shtm') as $item) {
                    $item = mb_substr($item,17,mb_strlen($item)-5-17);
                    $templates[] = sprintf("<a href='http://kend.beside.ru/_tmpl/%s'>%s</a>",
                        $item,
                        $item
                    );
                }
                echo implode('<br/>',$templates);
            } else {
                core::$renderPage = true;
                $this->title = 'Маппер';
                $pageTemplate = 'tmpl_mapper';
                $template = $GLOBALS['path'];
                unset($template[0]);
                echo file_get_contents(implode('/',$template).'.shtm', true);
                echo shp::tmpl('pages/tmpl_mapper',array('template'=>implode('/',$template)));
            }
            die();
		} else throw new CmsException('page_not_found');

	}

    static function getContent() {}
}