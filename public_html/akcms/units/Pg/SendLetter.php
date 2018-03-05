<?php # Написать письмо на сайт

class Pg_SendLetter extends PgUnitAbstract {
    private $hash;

	public function initAjx()
	{
		return array(
            '_send' => array(
                'func' => 'ajxSendEmail',
                'object' => $this
			),
		);
	}
  
	public function _rigthList()
	{
		return array(
		);
	}

	public function __construct(array $pathParams = array())
    {
        global $shape;
        $this->hash = md5($_SERVER['REMOTE_ADDR'].PATH_SEPARATOR.(isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:'').PATH_SEPARATOR.date('Yd') );

        $js = <<<JS
            jQuery(document).ready(function() { var ax='$this->hash',form=$('form');form.append($('<input type="hidden" name="cval"/>').prop('value',ax.substring(4,9)+ax.substring(1,3))); });
JS;
        $shape['js_admin'] = (isset($shape['js_admin'])?$shape['js_admin']:'').'<script>'.jsMin::minify($js).'</script>';

        parent::__construct($pathParams);
    }

    public function ajxSendEmail()
	{
        global $cfg;
        $data = $_POST;
	    if ( isset($data['cval']) && $data['cval'] === substr($this->hash,4,5).substr($this->hash,1,2) ) {
            $keys = array(
                'name' => 'Имя',
                'email' => 'Эл.почта',
                'message' => 'Сообщение',
                'ip_added' => 'IP адрес',
                'url_added' => 'Страница',
            );
            unset($data['cval']);
            if (count($data) > 0) {
                http_response_code(200);
                $data['ip_added'] = core::get_client_ip();
                $data['url_added'] = (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'http://' . $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']);

                $html = '<table>';
                foreach ($data as $k => $v) {
                    $html .= '<tr><th style="text-align: left">' . (isset($keys[mb_strtolower($k)]) ? $keys[mb_strtolower($k)] : htmlentities($k)) . ': </th><td>' . htmlentities($v) . '</td></tr>';
                }
                $html .= '</table>';
                $email = isset($data['email']) ? $data['email'] : '';
                $replyTo = $email !== '' ? 'Reply-To:' . $data['email'] : '';
                if (!sendMailHTML($cfg['email_moderator'], 'Письмо с сайта ' . $_SERVER['SERVER_NAME'], $html, $replyTo, $cfg['email_from']))
                    http_response_code(501);
            } else http_response_code(500);
        } else http_response_code(403);

	}
  
	public function render()
	{
		return '';
	}
  
}
