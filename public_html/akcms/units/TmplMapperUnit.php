<?php

function decode_entities_full($string, $quotes = ENT_COMPAT, $charset = 'ISO-8859-1') {
    return html_entity_decode(preg_replace_callback('/&([a-zA-Z][a-zA-Z0-9]+);/', 'convert_entity', $string), $quotes, $charset);
}

/**
 * Helper function for decode_entities_full().
 *
 * This contains the full HTML 4 Recommendation listing of entities, so the default to discard
 * entities not in the table is generally good. Pass false to the second argument to return
 * the faulty entity unmodified, if you're ill or something.
 * Per: http://www.lazycat.org/software/html_entity_decode_full.phps
 */
function convert_entity($matches, $destroy = true) {
    static $table = array(
        'quot' => '&#34;','amp' => '&#38;','lt' => '&#60;','gt' => '&#62;','OElig' => '&#338;','oelig' => '&#339;',
        'Scaron' => '&#352;','scaron' => '&#353;','Yuml' => '&#376;','circ' => '&#710;','tilde' => '&#732;',
        'ensp' => '&#8194;','emsp' => '&#8195;','thinsp' => '&#8201;','zwnj' => '&#8204;','zwj' => '&#8205;',
        'lrm' => '&#8206;','rlm' => '&#8207;','ndash' => '&#8211;','mdash' => '&#8212;','lsquo' => '&#8216;',
        'rsquo' => '&#8217;','sbquo' => '&#8218;','ldquo' => '&#8220;','rdquo' => '&#8221;','bdquo' => '&#8222;',
        'dagger' => '&#8224;','Dagger' => '&#8225;','permil' => '&#8240;','lsaquo' => '&#8249;','rsaquo' => '&#8250;',
        'euro' => '&#8364;','fnof' => '&#402;','Alpha' => '&#913;','Beta' => '&#914;','Gamma' => '&#915;',
        'Delta' => '&#916;','Epsilon' => '&#917;','Zeta' => '&#918;','Eta' => '&#919;','Theta' => '&#920;',
        'Iota' => '&#921;','Kappa' => '&#922;','Lambda' => '&#923;','Mu' => '&#924;','Nu' => '&#925;','Xi' => '&#926;',
        'Omicron' => '&#927;','Pi' => '&#928;','Rho' => '&#929;','Sigma' => '&#931;','Tau' => '&#932;',
        'Upsilon' => '&#933;','Phi' => '&#934;','Chi' => '&#935;','Psi' => '&#936;','Omega' => '&#937;',
        'alpha' => '&#945;','beta' => '&#946;','gamma' => '&#947;','delta' => '&#948;','epsilon' => '&#949;',
        'zeta' => '&#950;','eta' => '&#951;','theta' => '&#952;','iota' => '&#953;','kappa' => '&#954;',
        'lambda' => '&#955;','mu' => '&#956;','nu' => '&#957;','xi' => '&#958;','omicron' => '&#959;','pi' => '&#960;',
        'rho' => '&#961;','sigmaf' => '&#962;','sigma' => '&#963;','tau' => '&#964;','upsilon' => '&#965;',
        'phi' => '&#966;','chi' => '&#967;','psi' => '&#968;','omega' => '&#969;','thetasym' => '&#977;',
        'upsih' => '&#978;','piv' => '&#982;','bull' => '&#8226;','hellip' => '&#8230;','prime' => '&#8242;',
        'Prime' => '&#8243;','oline' => '&#8254;','frasl' => '&#8260;','weierp' => '&#8472;','image' => '&#8465;',
        'real' => '&#8476;','trade' => '&#8482;','alefsym' => '&#8501;','larr' => '&#8592;','uarr' => '&#8593;',
        'rarr' => '&#8594;','darr' => '&#8595;','harr' => '&#8596;','crarr' => '&#8629;','lArr' => '&#8656;',
        'uArr' => '&#8657;','rArr' => '&#8658;','dArr' => '&#8659;','hArr' => '&#8660;','forall' => '&#8704;',
        'part' => '&#8706;','exist' => '&#8707;','empty' => '&#8709;','nabla' => '&#8711;','isin' => '&#8712;',
        'notin' => '&#8713;','ni' => '&#8715;','prod' => '&#8719;','sum' => '&#8721;','minus' => '&#8722;',
        'lowast' => '&#8727;','radic' => '&#8730;','prop' => '&#8733;','infin' => '&#8734;','ang' => '&#8736;',
        'and' => '&#8743;','or' => '&#8744;','cap' => '&#8745;','cup' => '&#8746;','int' => '&#8747;',
        'there4' => '&#8756;','sim' => '&#8764;','cong' => '&#8773;','asymp' => '&#8776;','ne' => '&#8800;',
        'equiv' => '&#8801;','le' => '&#8804;','ge' => '&#8805;','sub' => '&#8834;','sup' => '&#8835;',
        'nsub' => '&#8836;','sube' => '&#8838;','supe' => '&#8839;','oplus' => '&#8853;','otimes' => '&#8855;',
        'perp' => '&#8869;','sdot' => '&#8901;','lceil' => '&#8968;','rceil' => '&#8969;','lfloor' => '&#8970;',
        'rfloor' => '&#8971;','lang' => '&#9001;','rang' => '&#9002;','loz' => '&#9674;','spades' => '&#9824;',
        'clubs' => '&#9827;','hearts' => '&#9829;','diams' => '&#9830;','nbsp' => '&#160;','iexcl' => '&#161;',
        'cent' => '&#162;','pound' => '&#163;','curren' => '&#164;','yen' => '&#165;','brvbar' => '&#166;',
        'sect' => '&#167;','uml' => '&#168;','copy' => '&#169;','ordf' => '&#170;','laquo' => '&#171;','not' => '&#172;',
        'shy' => '&#173;','reg' => '&#174;','macr' => '&#175;','deg' => '&#176;','plusmn' => '&#177;','sup2' => '&#178;',
        'sup3' => '&#179;','acute' => '&#180;','micro' => '&#181;','para' => '&#182;','middot' => '&#183;',
        'cedil' => '&#184;','sup1' => '&#185;','ordm' => '&#186;','raquo' => '&#187;','frac14' => '&#188;',
        'frac12' => '&#189;','frac34' => '&#190;','iquest' => '&#191;','Agrave' => '&#192;','Aacute' => '&#193;',
        'Acirc' => '&#194;','Atilde' => '&#195;','Auml' => '&#196;','Aring' => '&#197;','AElig' => '&#198;',
        'Ccedil' => '&#199;','Egrave' => '&#200;','Eacute' => '&#201;','Ecirc' => '&#202;','Euml' => '&#203;',
        'Igrave' => '&#204;','Iacute' => '&#205;','Icirc' => '&#206;','Iuml' => '&#207;','ETH' => '&#208;',
        'Ntilde' => '&#209;','Ograve' => '&#210;','Oacute' => '&#211;','Ocirc' => '&#212;','Otilde' => '&#213;',
        'Ouml' => '&#214;','times' => '&#215;','Oslash' => '&#216;','Ugrave' => '&#217;','Uacute' => '&#218;',
        'Ucirc' => '&#219;','Uuml' => '&#220;','Yacute' => '&#221;','THORN' => '&#222;','szlig' => '&#223;',
        'agrave' => '&#224;','aacute' => '&#225;','acirc' => '&#226;','atilde' => '&#227;','auml' => '&#228;',
        'aring' => '&#229;','aelig' => '&#230;','ccedil' => '&#231;','egrave' => '&#232;','eacute' => '&#233;',
        'ecirc' => '&#234;','euml' => '&#235;','igrave' => '&#236;','iacute' => '&#237;','icirc' => '&#238;',
        'iuml' => '&#239;','eth' => '&#240;','ntilde' => '&#241;','ograve' => '&#242;','oacute' => '&#243;',
        'ocirc' => '&#244;','otilde' => '&#245;','ouml' => '&#246;','divide' => '&#247;','oslash' => '&#248;',
        'ugrave' => '&#249;','uacute' => '&#250;','ucirc' => '&#251;','uuml' => '&#252;','yacute' => '&#253;',
        'thorn' => '&#254;','yuml' => '&#255;'
    );
    if (isset($table[$matches[1]])) return $table[$matches[1]];
    // else
    return $destroy ? '' : $matches[0];
}

class TmplMapperUnit extends CmsPage {

	public function initAjx()
	{
		$ajaxes = array(
			'_cr' => array(
				'func' => 'ajxCreateRegion'
            ),
            '_cr_undo' => array(
                'func' => 'ajxCreateRegion_undo'
            ),
		);
		return $ajaxes;
	}
  
	public function _rigthList()
	{
		return array(
		);
	}

	private function prepareHtml($html){
	    $html = str_replace('<br/>','<br>',$html);
        $html = str_replace('<br />','<br>',$html);
        $html = str_replace("\r\n","\n",$html);
        $html = html_entity_decode($html,ENT_NOQUOTES,'UTF-8');
	    return $html;
    }

    private function scriptComment($html) {
	    $html = str_replace(['<script','/script>'],['<!--script','/script-->'],$html);
	    return $html;
    }
    private function scriptUnComment($html) {
        $html = str_replace(['<!--script','/script-->'],['<script','/script>'],$html);
        return $html;
    }

    public function ajxCreateRegion_undo(){
        $checkRule = array();
        $checkRule[] = array('confirm','/^1$/');
        $checkResult = checkForm($_POST,$checkRule,$this->hasRight());
        if (count($checkResult)==0) {
            $data = $_POST;
            $template = $GLOBALS['path']; unset($template[0]); $data['template'] = 'akcms/u/template/'.implode('/',$template).'.shtm';
            if (file_exists($data['template'].'.bak')) {
                $file = file_get_contents($data['template']);
                $fileBak = file_get_contents($data['template'].'.bak');
                if (strcmp($file,$fileBak)!==0)
                    $result = copy($data['template'].'.bak',$data['template'])?'t':'f';
                else $result = 'i';
            } else $result = 'nobackup';

            return json_encode($result);
        }
        return json_encode(array('error'=>$checkResult));

    }

    public function ajxCreateRegion(){
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

            $html =  $this->prepareHtml(file_get_contents($data['template'])); $out = '';
            $data['html_count_'] = substr_count($data['locate'],$data['html']);
            $data['locate_count_'] = substr_count($html,$data['locate']);

            file_put_contents('../page.txt',$html);
            CmsLogger::clearScreen();

            $label = str_replace('!','',$data['label'],$replaceAnchor);
            $label = str_replace('*','',$label,$withoutClose);
            $htmls = explode($data['locate'],$html);
            //var_log_terminal(count($htmls)); die();
            if (count($htmls)==2) {
                if ($replaceAnchor) {
                    if ($withoutClose>0)
                        $out = $htmls[0].'{#'.$label.'#}'.$data['locate'].''.$htmls[1];
                    else
                        $out = $htmls[0].'{#'.$label.'#/}'.$data['locate'].'{/#'.$label.'#}'.$htmls[1];
                } else {
                    $locates = explode($data['html'], $data['locate']);
                    if (count($locates) == 2) {
                        if ($withoutClose>0)
                            $out = $htmls[0].$locates[0].'{#'.$label.'#}'.$data['html'].''.$locates[1].$htmls[1];
                        else
                            $out = $htmls[0].$locates[0].'{#'.$label.'#/}'.$data['html'].'{/#'.$label.'#}'.$locates[1].$htmls[1];
                    } elseif (count($locates) == 1) $result['error'] = 'Фрагмент не найден!';
                    else $result['error'] = 'Более одного вхождения фрагмента! ' . count($locates);
                }
            } elseif (count($htmls)==1) $result['error'] = 'Родитель не найден! ';
            else $result['error'] = 'Более одного вхождения родителя! '.count($htmls);

            if ($out !== '') {
                copy($data['template'],$data['template'].'.bak');
                $written  = file_put_contents($data['template'],$out);
                $result['status'] = 'Шаблон обновлен! Размер файла '.$written;
            }
            //$data['html_places'] = mb_strpos_all($data['locate'],$data['html']);

            CmsLogger::var_log($data);
            return json_encode($result);
        }
        return json_encode(array('error'=>$checkResult));

    }

	public function __construct()
	{
		global $pathlen, $pageTemplate;

		if ($GLOBALS['path'][0] === '_tmpl') {
            if (!$this->hasRight('1',false)) throw new CmsException('login_needs');
            if (core::$isAjax) return;
            if ($pathlen==1) {
                $templates = [];
                foreach(glob('akcms/u/template/{pages,parts}/*.shtm',GLOB_BRACE) as $item) {
                    $item = mb_substr($item,17,mb_strlen($item)-5-17);
                    $templates[] = sprintf("<a target='_blank' href='/_tmpl/%s'>%s</a>",
                        $item,
                        $item
                    );
                }
                echo implode('<br/>',$templates).'<br/><br/> usage: ctrl+space, shift+space';
            } else {
                core::$renderPage = true;
                $this->title = 'Маппер';
                $pageTemplate = 'tmpl_mapper';
                $template = $GLOBALS['path'];
                unset($template[0]);
                echo //$this->scriptUnComment($this->scriptComment(
                    file_get_contents(implode('/',$template).'.shtm', true);
                //));
                echo shp::tmpl('pages/tmpl_mapper',array('template'=>implode('/',$template)));
            }
            die();
		} else throw new CmsException('page_not_found');

	}

    public static function getContent() {}
}