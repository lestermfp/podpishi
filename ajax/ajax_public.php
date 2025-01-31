<?php


class CReg_user_ajax {
    public $output = array(); //Создаем массив который будет отослан в виде JSON.

    public function __construct()
    {

        $this->output['error'] = 'false';

        if (isset($_GET['context'])) {
            switch ($_GET['context']) {
                case 'get__ip':
                    $this->get__ip();
                    break;

            }
        }

        echo json_encode($this->output);

    }

    private function get__ip(){

        header("Access-Control-Allow-Origin: *");

        $this->output['country'] = 'XX';

        if (isset($_SERVER["HTTP_CF_IPCOUNTRY"]))
            $this->output['country'] = $_SERVER["HTTP_CF_IPCOUNTRY"];


    }

}

new CReg_user_ajax();
?>