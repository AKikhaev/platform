<?php # Список подразделов текущего раздела без вложенностей

$greet = function($name)
{
    printf("Hello %s\r\n", $name);
};


class Pg_UsersMng extends PgUnitAbstract {

    function initAjx()
    {
        return array(
        );
    }

    function _rigthList()
    {
        return array(
        );
    }

    private $tableConfig = null;
    private $groups_dict = null;

    public function _YesNo($value,$field,$data) {
        return $value==='t'?'да':'нет';
    }
    public function _EdtYesNo($value,$field,$data) {
        return sprintf('<input type="checkbox" value="t" name="%s" %s>',
            $field,
            $value==='t'?'checked':''
        );
    }
    public function _Group($value,$field,$data) {
        global $sql;
        $groups = [];
        foreach ($sql->da_a($value) as $groupId) {
            $groups[] = $this->groups_dict[$groupId]['v'];
        }
        return implode(', ',$groups);
    }
    public function _EdtGroup($value,$field,$data) {
        global $sql;
        $html = '';
        $values = $sql->da_a($value);
        foreach ($this->groups_dict as $item) {
            $html .= sprintf('<label><input type="checkbox" value="%s" name="%s[]" %s>%s</label><br/>',
                $item['k'],
                $field,
                in_array($item['k'],$values)?'checked':'',
                $item['v']
            );
        }
        return $html;
    }
    public function _EdtPass($value,$field,$data) {
        return sprintf('<input type="password" value="" name="%s">',
            $field
        );
    }

    public function __construct(array $pathParams = array())
    {
        global $sql;
        parent::__construct($pathParams);

        $this->groups_dict = $sql->query_dict('SELECT id_usrgpr as k,usrgrp_name as v FROM cms_users_groups ORDER BY 2');

        $this->tableConfig = [
            'id_usr' => [
                'name' => 'ИД',
                'hideTable' => true,
                'hideEdit' => true,
                'type'=>'int'
            ],
            'usr_login' => [
                'name' => 'Логин',
            ],
            'usr_name' => [
                'name' => 'Имя',
            ],
            'usr_grp' => [
                'name' => 'Группа',
                'toText' => [&$this,'_Group'],
                'toEdit' => [&$this,'_EdtGroup'],
                'type'=>'a_d'
            ],
            'usr_email' => [
                'name' => 'Эл. почта',
            ],
            'usr_enabled' => [
                'name' => 'Разрешен',
                'toText' => [&$this,'_YesNo'],
                'toEdit' => [&$this,'_EdtYesNo'],
                'type'=>'bool'
            ],
            'usr_activated' => [
                'name' => 'Активирован',
                'toText' => [&$this,'_YesNo'],
                'toEdit' => [&$this,'_EdtYesNo'],
                'type'=>'bool'
            ],
            'usr_password' => [
                'name' => 'Пароль',
                'hideTable' => true,
                'toEdit' => [&$this,'_EdtPass'],
            ],
            'usr_password_check' => [
                'name' => 'Пароль повтор',
                'hideTable' => true,
                'toEdit' => [&$this,'_EdtPass'],
            ],
            'action' => [
                'name' => '',
                'hideEdit' => true,
            ],
        ];

    }

    function render()
    {
        /* @var PageUnit $page */
        global $sql,$page,$cfg;
        $html = '';

        if (core::$inEdit && $page->hasRight('admin',false,true))
        {
            if (isset($_GET['id'])) {
                $id = $sql->d($_GET['id']);
                if ($id===0) {
                    $html .= '<h2>Создание пользователя</h2>';
                    $user = ['id_usr'=>0];


                } else {
                    $html .= '<h2>Редактирование пользователя</h2>';
                    $query = 'SELECT id_usr,usr_login,usr_name,usr_enabled,usr_grp,usr_activated,usr_email FROM cms_users WHERE NOT usr_admin AND id_usr=' . $id;
                    $user = $sql->query_fa($query);
                }

                $errors = [];
                if (count($_POST)>0) {
                    $user_ = $_POST;
                    if ($user_['usr_password']!==$user_['usr_password_check']) $errors[]='Пароли не совпадают!';
                    if ($user_['usr_login']==='') $errors[]='Логин не задан!';
                    if ($user_['usr_name']==='') $errors[]='Имя не задано!';
                    if (!isset($user_['usr_enabled'])) $user_['usr_enabled'] = 'f';
                    if (!isset($user_['usr_activated'])) $user_['usr_activated'] = 'f';
                    if ($user_['usr_password']==='' && $id===0) $errors[]='Пароль не задан!';
                    if ($user_['usr_password']!=='') $user_['usr_password_md5'] = md5($cfg['usrprepass'].$user_['usr_password']);
                    $user_['usr_grp'] = $sql->a_d(isset($user_['usr_grp'])?$user_['usr_grp']:[]);
                    unset($user_['usr_password'],$user_['usr_password_check'],$user['id_usr']);
                    $user = array_merge($user,$user_);

                    if (count($errors)===0) {
                        $data = [];
                        foreach ($user as $k=>$v) {
                            $value = $sql->t($v);
                            if (@$this->tableConfig[$k]['type']==='bool') $value = $sql->b($v);
                            if (@$this->tableConfig[$k]['type']==='a_d') $value = $v;
                            if (@$this->tableConfig[$k]['type']==='int') $value = $v;

                            $data[$k] = $value;
                        }

                        try {
                            if ($id===0) $sql->command($sql->pr_i('cms_users',$data));
                            else $sql->command($sql->pr_u('cms_users',$data,'id_usr='.$id));
                            header('Location: ?');
                        } catch (DBException $e) {
                            if ($e->isDuplicate) {
                                $errors[] = 'Этот логин уже занят!';
                            }
                            else $errors[] = 'Критическая ошибка '.$e->getMessage();
                        }

                    }


                }
                if (count($errors)>0) $html .= '<span style="color: darkred">'.implode('<br/>',$errors).'</span>';

                if ($user) {
                    if (isset($_GET['drop'])) {
                        $sql->command($sql->pr_d('cms_users',['id_usr'=>$id]));
                        header('Location: ?');
                    }
                    $html .= '<form method="post" >';
                    $html .= TableHeader();
                    $html .= TableEditRows($this->tableConfig,$user);
                    $html .= TableBottom();
                    $html .= '<input type="submit" value="Сохранить"></form>';
                } else $html .= 'НЕ НАЙДЕН!';

                $html .= '<hr>';
            }

            $query = sprintf('SELECT id_usr,usr_login,usr_name,usr_grp,usr_enabled,usr_activated FROM cms_users WHERE NOT usr_admin order by id_usr');
            $users = $sql->query_all($query);

            $html .= TableHeader($this->tableConfig);
            foreach ($users as $user) {
                $user['action'] =
                    sprintf('<a href="?id=%1$d" >Изменить</a><br/><a href="?id=%1$d&drop" onclick="return confirm(\'Удалить пользователя %2$s?\')" >Удалить</a>',
                        $user['id_usr'],
                        $user['usr_login']
                    );

                $html .= TableRow($this->tableConfig,$user);
            }
            $html .= TableBottom();
            $html .= '<a href="?id=0">Добавить пользователя</a>';

        } else throw new CmsException("page_not_found");
        return $html;
    }
}