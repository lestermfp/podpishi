<?php
include('../config.php');
include($_CFG['root'] . 'classes/c4p_fundraise.php');

/*
	ini_set('display_errors', true);
	ini_set('error_reporting',  E_ALL);
	error_reporting(E_ALL + E_STRICT);
*/

	ini_set('display_errors', true);
	ini_set('error_reporting',  E_ALL);
	error_reporting(E_ALL + E_STRICT);

//print '<pre>' . print_r($_SESSION, true) . '</pre>';

if (isset($argv[1]))
    $_GET['context'] = $argv[1];

class CReg_user_ajax {
    public $output = array(); //Создаем массив который будет отослан в виде JSON.

    public function __construct() {
		Global $_USER ;

		$this->output['error'] = 'false';

        if (isset($_GET['context'])) {
            switch ($_GET['context']) {

				case 'table__agregateSigns':
                    $this->table__agregateSigns();
                    break;


            }
        }

        if (isset($_GET['json']))
            print '<pre>' . print_r($this->output, true) . '</pre>';

		echo json_encode( $this->output );

    }

    private function table__agregateSigns(){

        if (date('Y-m-d') != '2021-06-29'){
            $this->output['error'] = 'nope';
            return ;
        }

        if (!isset($_GET['page']))
            $_GET['page'] = 0;

        $signs = db_appeals_list::find('all', [
            'select' => 'COUNT(*) as total_signs, email, phone',
            'group' => 'phone, email',
            'offset' => $_GET['page'] * 50 * 1000,
            'limit' => 50 * 1000,
        ]);

        $table =[];

        foreach ($signs as $sign){

            $table[] = $sign->to_array();

        }

        if (isset($_GET['format']) AND $_GET['format'] == 'json'){
            $this->output['signs'] = $table ;
            return ;
        }

        print gdHandlerSkeleton::generateSimpleHtmlTable($table);

        exit();

    }

}

$CReg_user_ajax = new CReg_user_ajax;
?>
