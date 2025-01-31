<?php
include('../config.php');
/*
    ini_set('display_errors', true);
    ini_set('error_reporting',  E_ALL);
    error_reporting(E_ALL + E_STRICT);
*/


//print '<pre>' . print_r($_SESSION, true) . '</pre>';
/*
	Скрипт обслуживает ajax запросы для авторизации и разлогинивания.
	Все ответы выводятся в виде JSON
*/

class CReg_user_ajax extends gdHandlerSkeleton {
    protected $output = array(); //Создаем массив который будет отослан в виде JSON.

    public function __construct() {
        $this->output['error'] = 'false';

        if (isset($_GET['context'])) {
            switch ($_GET['context']) {
                case 'authUser':
                    $this->authUser();
                    break;

            }
        }

        echo json_encode( $this->output );
    }

    /*
        Авторизация.
        Получаем $_GET['auth_phone'], $_GET['auth_password']

        Записываем в текущую сессию объект пользователя в случае успеха.

    */
    public function authUser(){

        $required_fields = array('auth_phone', 'auth_password');

        foreach ($required_fields as $_key){
            if (!isset($_POST[$_key]) || $_POST[$_key]==''){

                $this->output['error_text'] = 'Недостаточно данных';
                $this->output['error'] = 'wrong input2 '  . $_key;
                $this->output['field'] = $_key ;

                $this->output['errors'][] = ['field'=>$_key,'text'=> 'Недостаточно данных'];
                return ;
            }
        }

        $_POST['auth_phone'] = preg_replace('~\D+~', '', $_POST['auth_phone']);

        $_POST['auth_phone'] = mb_strtolower(trim($_POST['auth_phone']), 'UTF-8');

        $user = db_main_users::find_by_phone($_POST['auth_phone']);

        if (empty($user) OR $user->read_attribute('role') == 'none'){
            $this->output['error_text'] = 'Неправильные данные для входа';
            $this->output['error'] = 'wrong input1';
            $this->output['field'] = 'general' ;
            return ;
        }

        if (!in_array($user->read_attribute('role'), ['admin', 'settler', 'guest', 'coordinator'])){
            $this->output['error_text'] = 'Неправильные данные для входа';
            $this->output['error'] = 'not allowed';
            $this->output['field'] = 'general' ;
            return ;
        }

        // Check password
        $password = md5($_POST['auth_password'] . $user->read_attribute('salt'));

        $_POST['auth_password'] = mb_strtolower($_POST['auth_password'], 'UTF-8');

        $password_ci = md5($_POST['auth_password'] . $user->read_attribute('salt'));

        if ($password != $user->read_attribute('password') AND $password_ci != $user->read_attribute('password') ){
            // AND $_POST['auth_password'] != ''
            $this->output['error_text'] = 'Неправильные данные для входа';
            $this->output['error'] = 'wrong input';
            $this->output['field'] = 'auth_password' ;
            return ;
        }


        $_USER = new gh_user('db_main_users');
        $_USER->loadById($user->id);

        $outputAuth = $_USER->performAuth();

        $this->output['session_hash'] = $outputAuth ;


        $this->output['status'] = 'true';
    }

}

$aux = new CReg_user_ajax;
?>