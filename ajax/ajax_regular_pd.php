<?php
include('../config.php');


/*
	ini_set('display_errors', true);
	ini_set('error_reporting',  E_ALL);
	error_reporting(E_ALL + E_STRICT);
*/

ini_set('display_errors', true);
ini_set('error_reporting',  E_ALL);
error_reporting(E_ALL + E_STRICT);

//print '<pre>' . print_r($_SESSION, true) . '</pre>';

class CReg_user_ajax extends gdHandlerSkeleton {
    public $output = array(); //Создаем массив который будет отослан в виде JSON.

    public function __construct() {

        $this->output['error'] = 'false';

        switch ($_GET['context']) {

            case 'from_to_actual':
                $this->from_to_actual();
                break;

            case 'from_to_old':
                $this->from_to_old();
                break;


            case 'export__pskovBitrix':
                $this->export__pskovBitrix();
                break;



        }

        echo json_encode( $this->output );

    }

    private function export__pskovBitrix(){


        $appeal = db_appeals_list::find_by_id(101131);
        $campaign = db_campaigns_list::find_by_id($appeal->destination);

        $exportVars = [
            'name' => '',
            'last_name' => '',
            'second_name' => '',
            'phone' => $appeal->phone,
            'email' => $appeal->email,
            'address' => 'test',
            'source_description' => $campaign->title,
            'utm_campaign' => $appeal->utm_list,
        ];

        $parts = explode(" ", $appeal->full_name, 3);

        if (isset($parts[1]))
            $exportVars['name'] = $parts[1];

        if (isset($parts[0]))
            $exportVars['last_name'] = $parts[0];

        if (isset($parts[2]))
            $exportVars['second_name'] = $parts[2];

        $url = 'https://yabloko.bitrix24.ru/rest/35/dunjsbhiyqpsntc0/crm.contact.add.json';

        $content = gdHandlerSkeleton::post_query($url, $exportVars);

        print '<pre>' . print_r($content, true) . '</pre>';
        print '<pre>' . print_r($exportVars, true) . '</pre>';

        exit();
    }

    private function from_to_old() {
        Global $_CFG ;

        $contacts = db_old_appeals::find('all',[
            'select' => 'id, name as name_appeal, phone, email, city as yd_city, street_name as yd_street_name, house_number as yd_house_number, appartment_raw as flat, unibased_at',
            'conditions'=>['unibased_at is NULL'],
            'limit'=> 50,
        ]);

        if (empty($contacts)){
            print 'nothing';
            return ;
        }

        include_once $_CFG['root'] . 'classes/gd_unibaseclient.php';

        $secret = 'vkzx8618721k2nk11jkv';

        foreach ($contacts as $_contact) {

            $data = $_contact->to_array();

            $data['phone'] = parseAndformatPhone($data['phone']);

            if ($data['flat'] == '')
                unset($data['flat']);

            unset($data['unibased_at']);

            //print '<pre>' . print_r($data, true) . '</pre>';


            //continue ;

            //exit();

            $reply = gdUnibaseClient::pushUserData($data, 'pd2016', $secret);

            //print '<pre>' . print_r($reply, true) . '</pre>';

            //exit();


            if($reply['error'] == 'false') {
                $_contact->unibased_at = 'now';
                $_contact->save();

                print 'Saved<br/>';

            } else {
                echo print_r($reply).chr(10).chr(13);
            }
        }

        redirect('https://podpishi.org/ajax/ajax_regular_pd.php?context=from_to');

    }


    private function from_to_actual() {
        Global $_CFG ;

        $contacts = db_appeals_list::find('all',[
            'select' => 'id, full_name as name_appeal, phone, email, yd_city, yd_street_name, yd_house_number, flat, unibased_at, destination',
            'conditions'=>['unibased_at is NULL'],
            'limit'=> 50,
        ]);

        if (empty($contacts)){
            print 'nothing';
            return ;
        }

        include_once $_CFG['root'] . 'classes/gd_unibaseclient.php';

        $secret = 'vksaoude21j3jiu7s7a8999';

        foreach ($contacts as $_contact) {

            $data = $_contact->to_array();

            $data['phone'] = parseAndformatPhone($data['phone']);


            $data['campaignid_' . $data['destination']] = 1 ;

            unset($data['destination']);

            if ($data['flat'] == '')
                unset($data['flat']);

            unset($data['unibased_at']);

            ///print '<pre>' . print_r($data, true) . '</pre>';


            //continue ;

            //exit();

            $reply = gdUnibaseClient::pushUserData($data, 'pd2019', $secret);

            //print '<pre>' . print_r($reply, true) . '</pre>';

            //exit();


            if($reply['error'] == 'false') {
                $_contact->unibased_at = 'now';
                $_contact->save();

                print 'Saved<br/>';

            } else {
                echo print_r($reply).chr(10).chr(13);
            }
        }

        redirect('https://podpishi.org/ajax/ajax_regular_pd.php?context=from_to_actual');

    }


}

$CReg_user_ajax = new CReg_user_ajax;
?>
