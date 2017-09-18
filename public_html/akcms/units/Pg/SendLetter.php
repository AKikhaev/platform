<?php # Написать письмо на сайт

class Pg_SendLetter extends PgUnitAbstract {

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

	public function ajxSendEmail()
	{
		$keys = array(
            'name'=>'Имя',
            'email'=>'Эл.почта',
            'message'=>'Сообщение',
            'ip_added'=>'IP адрес',
            'url_added'=>'Страница',
		);

		global $cfg;
		$data = $_POST;
		if (count($data)>0) {
            http_response_code(200);
            $data['ip_added'] = core::get_client_ip();
            $data['url_added'] = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];

            $html = '<table>';
            foreach ($data as $k => $v) {
                $html .= '<tr><th style="text-align: left">'.(isset($keys[mb_strtolower($k)])?$keys[mb_strtolower($k)]:htmlentities($k)).'</th><td>'.htmlentities($v).'</td></tr>';
            }
            $html .= '</table>';
            $email = isset($data['email']) ? $data['email'] : '';
            $replyTo = $email!==''?'Reply-To:'.$data['email']:'';
            if (!sendMailHTML($cfg['email_moderator'], 'Письмо с сайта '.$_SERVER['SERVER_NAME'], $html,$replyTo,$cfg['email_from']))
				http_response_code(501);
        } else http_response_code(500);
	}
  
	public function render()
	{
		return '';
	}
  
}
