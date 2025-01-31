<?php
include('../config.php');

	ini_set('display_errors', true);
	ini_set('error_reporting',  E_ALL);
	error_reporting(E_ALL + E_STRICT);

//print GetRealIp();
//print '<pre>' . print_r($_SERVER, true) . '</pre>';

//print '<pre>' . print_r($_SESSION, true) . '</pre>';

if (isset($argv[1]))
    $_GET['context'] = $argv[1] ;

class CReg_user_ajax extends gdHandlerSkeleton {
    public $output = array(); //Создаем массив который будет отослан в виде JSON.



    public function __construct() {
        if (isset($_GET['context'])) {
            switch ($_GET['context']) {
                case 'hithere':
                    $this->hithere();
                    break;
                case 'restore_appeal':
                    $this->restore_appeal();
                    break;

                case 'calc__apiRegionName':
                    $this->calc__apiRegionName();
                    break;

                case 'calc__apiRegionNameOld':
                    $this->calc__apiRegionNameOld();
                    break;



                case 'list__apiRegionName':
                    $this->list__apiRegionName();
                    break;

                case 'calc__apiRegionNameEparty':
                    $this->calc__apiRegionNameEparty();
                    break;

                case 'email__test':
                    $this->email__test();
                    break;

                case 'calc__goSubregionId':
                    $this->calc__goSubregionId();
                    break;


                case 'calc__goEpartySubregionId':
                    $this->calc__goEpartySubregionId();
                    break;

                case 'calc__goOldAppealsSubregionId':
                    $this->calc__goOldAppealsSubregionId();
                    break;

                case 'get__go2020Contacts':
                    $this->get__go2020Contacts();
                    break;


	            case 'get__globalsignContacts':
		            $this->get__globalsignContacts();
		            break;

                case 'get__old_globalsignContacts':
                    $this->get__old_globalsignContacts();
                    break;

                case 'test__uniGeocoder':
                    $this->test__uniGeocoder();
                    break;

                case 'use__dadataAPI':
                    $this->use__dadataAPI();
                    break;

                case 'set__sorter_federal':
                    $this->set__sorter_federal();
                    break;

                case 'test__xlsxExport':
                    $this->test__xlsxExport();
                    break;

                case 'test__sapegaList':
                    $this->test__sapegaList();
                    break;

                case 'test__cikNodesPskov':
                    $this->test__cikNodesPskov();
                    break;

                case 'test__authorsAssociation':
                    $this->test__authorsAssociation();
                    break;


            }
        }

        if (isset($_GET['json']))
            print '<pre>' . print_r($this->output, true) . '</pre>';

        echo json_encode( $this->output );

    }

    private function test__authorsAssociation(){

        $campaigns_all = db_campaigns_list::find('all', [
            'conditions' => ['author_id>0'],
        ]);

        foreach ($campaigns_all  as $campaign){

            $campaign->addAuthor($campaign->author_id);

        }

        $campaign = db_campaigns_list::find_by_id(118);

        $authors = $campaign->getAuthorsPublicCached();

        print '<pre>' . print_r($authors, true) . '</pre>';

        exit();

    }

    private function test__cikNodesPskov(){

        $total = db_cik_nodes::count();

        $start_id = 337811215;

        $node = db_cik_nodes::find_by_text($_GET['text']);

        $recTest = db_cik_nodes::find_by_id($node->id);

        print gdHandlerSkeleton::generateSimpleHtmlTable($recTest->buildTree(9));

        //print '<pre>' . print_r($recTest->buildTree(10), true) . '</pre>';
        exit();


        $topAdmLevel = $node->getChildren();

        $table = [];

        foreach ($topAdmLevel as $node){

            $item = [
                'Район' => $node->text,
            ];

            $subLevel = $node->getChildren();

            //print $node->text . ' ' . count($subLevel) . '<br/>';

            foreach ($subLevel as $subnode){

                $item['МО'] = $subnode->text ;

                $mo = $subnode->getChildren();

                foreach ($mo as $submo){

                    $item['submo'] = $submo->text ;

                    $subsubmo_list = $submo->getChildren();

                    foreach ($subsubmo_list as $subsubmo){

                        $item['subsubmo'] = $subsubmo->text ;

                        if ($subsubmo->isHouse())
                            $item['subsubmo'] .= ' d';

                        $table[] = $item ;

                    }




                }





            }



            break ;
        }

        print gdHandlerSkeleton::generateSimpleHtmlTable($table);

        //print '<pre>' . print_r($node->getChildren(), true) . '</pre>';

        print $total;

        exit();

    }

    private function test__sapegaList(){

        if (date('Y-m-d') != '2021-05-26'){
            print 'wrong access';
            exit();
        }




        $appeals = db_appeals_list::find('all', [
            'conditions' => ['destination=104'],
            'select' => 'id, full_name, city, street_name, house_number, flat, LOWER(email) as email, phone, date',
            'order' => 'date DESC',
        ]);

        $emails = gdHandlerSkeleton::collectKeys($appeals, ['email']);
        $phones = gdHandlerSkeleton::collectKeys($appeals, ['phone']);

        $phones = db_appeals_list::find('all', [
            'conditions' => ['destination!=104 AND phone IN (?)', $phones],
            'select' => 'phone',
            'group' => 'phone',
        ]);

        $phones = gdHandlerSkeleton::collectKeys($phones, ['phone']);

        $emails = db_appeals_list::find('all', [
            'conditions' => ['destination!=104 AND LOWER(email) IN (?)', $emails],
            'select' => 'LOWER(email) as email',
            'group' => 'email',
        ]);

        $emails = gdHandlerSkeleton::collectKeys($emails, ['email']);


        $table = [];

        $ipsAmount = [];
        $citiesAmount = [];

        foreach ($appeals as $appeal){

            if (!isset($citiesAmount[$appeal->city]))
                $citiesAmount[$appeal->city] = 0;

            $citiesAmount[$appeal->city]++;

        }


        foreach ($appeals as $appeal){

            $is_ip_duplicate = '';

            //if ($ipsAmount[$appeal->ip_addr] > 1)
            //    $is_ip_duplicate = '<span style="color: red;">дубль айпи</span>';

            $rare_city = '';

            if ($citiesAmount[$appeal->city] <= 3)
                $rare_city = '<span style="color: red;">город редкий</span>';

            $phone_warn = '';

            if (self::isSimilarSymbolRepeats(ltrim($appeal->phone, '7999'), 4))
                $phone_warn = '<span style="color: red;">цифры номера повт.</span>';

            $before_was = '';

            if (in_array($appeal->phone, $phones))
                $before_was = '<span style="color: green;">раньше подписывался</span>';

            if (in_array($appeal->email, $emails))
                $before_was = '<span style="color: green;">раньше подписывался</span>';

            $signature = '';
            if ($before_was == ''){

                gc_collect_cycles();

                $appeal_t = db_appeals_list::find_by_id($appeal->id, [
                    'select' => 'signature',
                ]);

                gc_collect_cycles();

                $signature = '<img src="' . $appeal_t->signature . '" width=\'150\'>';
                
            }
            
            $item = [
                'full_name' => $appeal->full_name,
                'city' => $appeal->city,
                '$rare_city' => $rare_city,
                'street_name' => $appeal->street_name,
                'house_number' => $appeal->house_number,
                'date' => $appeal->date->format('d.m. H:i'),
                'phone' => $appeal->phone,
                //'ss' => (ltrim($appeal->phone, '79992')),
                'phone_warn' => $phone_warn,
                'email' => $appeal->email,
                'before_was' => $before_was,
                'подпись' => $signature,
                //'ip_addr' => $appeal->ip_addr,
                //'ip_ip_duplicate' => $is_ip_duplicate,
            ];

            $table[] = $item ;

        }

        print gdHandlerSkeleton::generateSimpleHtmlTable($table);
        exit();

    }

    static function isSimilarSymbolRepeats($string, $desired_amount){

        $last_symbol = '';
        $amount = 1;

        $split = str_split($string);

        foreach ($split as $key){

            if ($last_symbol == '' OR $last_symbol == $key){
                $last_symbol = $key ;
                $amount++;
            }
            else {
                $last_symbol = $key ;
                $amount = 1;
            }

            if ($amount >= $desired_amount){

                return true  ;
            }

        }

        return false;
    }


    private function test__xlsxExport(){
        Global $_CFG ;

        include($_CFG['root'] . 'classes/simpleXLSXGen.php');


        $books = [
            ['ISBN', 'title', 'author', 'publisher', 'ctry' ],
            [618260307, 'The Hobbit', 'J. R. R. Tolkien', 'Houghton Mifflin', 'USA'],
            [908606664, 'Slinky Malinki', 'Lynley Dodd', 'Mallinson Rendel', 'NZ']
        ];
        $xlsx = SimpleXLSXGen::fromArray( $books );
        $xlsx->downloadAs('books.xlsx'); // or downloadAs('books.xlsx') or $xlsx_content = (string) $xlsx

        exit();
    }

    private function set__sorter_federal(){

        $csv = gdHandlerSkeleton::getWithHeaderByUrl('https://docs.google.com/spreadsheets/d/e/2PACX-1vRi414ZG273UM_GG9eJvk-6FkvUGSC3IgDK0nDA48UNXD3FNmRR7FjPShmf9ImdbaiLa2DhnkcAJfEt/pub?gid=0&single=true&output=csv');

        foreach ($csv as $key => $item){

            $number = $key + 1;

            $region = db_federal_regions::find_by_iso_code($item['код iso 3166-2[1]']);

            if (empty($region)){
                print $item['наименование субъекта'] . '<br/>';
                continue ;
            }

            $region->code = $number;
            $region->save();

        }

    }

    /*
     * Arg: address, [forced_geo_system]
     */
    private function use__dadataAPI(){

        $geocoder_city = uniGeocoder::requestQualifiedCoordsByAddress($_GET['address'], '', $_GET['forced_geo_system']);

        $this->output['geocoder'] = $geocoder_city;

    }

    private function test__uniGeocoder(){


        $apikey = '9e89ef7e-7675-4324-ba7b-5f2c2fcc91fa';

        $geocoder_city = uniGeocoder::requestQualifiedCoordsByAddress('Москва, улица Московская обл., рабочий поселок Дрожжино, Новое шоссе, дом д. 4, корп. 2', $apikey, 'yd');

        print '<pre>' . print_r($geocoder_city, true) . '</pre>';
        exit();

        $apikey = '9e89ef7e-7675-4324-ba7b-5f2c2fcc91fa';

        $address = 'Москва, Соловьиный проезд, дом 2';

        $geocoder = uniGeocoder::requestQualifiedCoordsByAddress($address, $apikey, 'yd');

        print 'Used: ' . $geocoder['used_engine'] . '<br/>';

        print '<pre>' . print_r($geocoder, true) . '</pre>';

    }

    private function get__go2020Contacts(){

        if (date('Y-m-d') != '2020-09-08'){
            print 'wrong y';
            exit();
        }

        $this->output['contacts'] = [];

        $appeals = db_appeals_list::find('all', [
            'conditions' => ['go_subregion_id NOT IN ("notyet", "-1")'],
            'select' => 'full_name as name, "" as surname, "" as middlename, destination, phone, date, go_subregion_id',
        ]);

        foreach ($appeals as $appeal){

            $campaign = db_campaigns_list::find_by_id($appeal->destination);

            $item = [
                'name' => $appeal->name,
                'surname' => '',
                'middlename' => '',
                'phone' => $appeal->phone,
                'subregion_id' => $appeal->go_subregion_id,
                'origin_date' => $appeal->date->format('Y-m-d H:i:s'),
                'origin_name' => $campaign->title,
            ];

            $parts = explode(" ", $appeal->name, 3);

            if (isset($parts[1]))
                $item['name'] = $parts[1];

            if (isset($parts[0]))
                $item['surname'] = $parts[0];

            if (isset($parts[2]))
                $item['middlename'] = $parts[2];


            $this->output['contacts'][] = $item ;
        }

        $appeals = db_old_appeals::find('all', [
            'conditions' => ['go_subregion_id NOT IN ("notyet", "-1")'],
            'select' => 'name, "" as surname, "" as middlename, destination, phone, reg_date, go_subregion_id',
        ]);

        foreach ($appeals as $appeal){

            $item = [
                'name' => $appeal->name,
                'surname' => '',
                'middlename' => '',
                'phone' => $appeal->phone,
                'subregion_id' => $appeal->go_subregion_id,
                'origin_date' => $appeal->reg_date->format('Y-m-d H:i:s'),
                'origin_name' => $appeal->destination,
            ];

            $parts = explode(" ", $appeal->name, 3);

            if (isset($parts[1]))
                $item['name'] = $parts[1];

            if (isset($parts[0]))
                $item['surname'] = $parts[0];

            if (isset($parts[2]))
                $item['middlename'] = $parts[2];


            $this->output['contacts'][] = $item ;
        }

        //print '<pre>' . print_r($this->output['contacts'], true) . '</pre>';
        //exit();

    }

	private function get__globalsignContacts() {

		global $_CFG;

		if (!isset($_GET['key_oiho40449']) || md5($_GET['key_oiho40449'] . '9090w;l') !== $_CFG['globalsign_export_key_hash']) {
			print 'wrong y';
			exit;
		}

		header('Access-Control-Allow-Origin: https://go.city4people.ru/');

		$this->output['contacts'] = [];

		$last_id = $_GET['last_id'];

		$appeals = db_appeals_list::all([
			'select'     => 'id, full_name as name, "" as surname, "" as middlename, destination, phone, email, date, city, street_name, house_number, flat, lat, lng',
			'conditions' => [
				'
					(api_city = "Москва" OR yd_city = "Москва" OR go_city = "Москва" OR city="Москва"
						OR api_city = "москва" OR yd_city = "москва" OR go_city = "москва" OR city="москва")
					AND id > ?
				',
				$last_id
			],
			'order'      => 'id ASC',
			'limit'      => 200,
		]);

		foreach ($appeals as $appeal) {

			$campaign = db_campaigns_list::first([
				'select'     => 'title',
				'conditions' => [
					'id = ?',
					$appeal->destination
				],
			]);

			$item = [
				'name'         => $appeal->name,
				'surname'      => '',
				'middlename'   => '',
				'phone'        => $appeal->phone,
				'email'        => $appeal->email,
				'origin_date'  => $appeal->date->format('Y-m-d H:i:s'),
				'origin_id'    => $appeal->id,
				'origin_name'  => $campaign->title,
				'city'         => $appeal->city,
				'street_name'  => $appeal->street_name,
				'house_number' => $appeal->house_number,
				'flat'         => $appeal->flat,

				'lat' => $appeal->lat,
				'lng' => $appeal->lng,
			];

			$parts = explode(" ", $appeal->name, 3);

			if (isset($parts[1]))
				$item['name'] = $parts[1];

			if (isset($parts[0]))
				$item['surname'] = $parts[0];

			if (isset($parts[2]))
				$item['middlename'] = $parts[2];

			$this->output['contacts'][] = $item;
		}

		/*$appeals = db_old_appeals::find('all', [
			'select' => 'name, "" as surname, "" as middlename, destination, phone, reg_date, go_subregion_id',
		]);

		foreach ($appeals as $appeal){

			$item = [
				'name' => $appeal->name,
				'surname' => '',
				'middlename' => '',
				'phone' => $appeal->phone,
				'subregion_id' => $appeal->go_subregion_id,
				'origin_date' => $appeal->reg_date->format('Y-m-d H:i:s'),
				'origin_name' => $appeal->destination,
			];

			$parts = explode(" ", $appeal->name, 3);

			if (isset($parts[1]))
				$item['name'] = $parts[1];

			if (isset($parts[0]))
				$item['surname'] = $parts[0];

			if (isset($parts[2]))
				$item['middlename'] = $parts[2];


			$this->output['contacts'][] = $item ;
		}*/

		// print '<pre>' . print_r($this->output['contacts'], true) . '</pre>';
		// exit;

	}

	private function get__old_globalsignContacts() {

		global $_CFG;

		if (!isset($_GET['key_oiho40449']) || md5($_GET['key_oiho40449'] . '9090w;l') !== $_CFG['globalsign_export_key_hash']) {
			print 'wrong y';
			exit;
		}

		header('Access-Control-Allow-Origin: https://go.city4people.ru/');

		$this->output['contacts'] = [];

		if (strpos($_GET['last_id'], 'old_') === false)
			return;

		$last_id = str_replace('old_', '', $_GET['last_id']);

		$appeals = db_old_appeals::all([
			'select'     => 'id, name, "" as surname, "" as middlename, destination, phone, email, reg_date as date, city, street_name, house_number, appartment as flat, lat, lng',
			'conditions' => [
				'
					(city="Москва" OR city="москва")
					AND id > ?
				',
				$last_id
			],
			'order'      => 'id ASC',
			'limit'      => 200,
		]);

		foreach ($appeals as $appeal) {

			$item = [
				'name'       => $appeal->name,
				'surname'    => '',
				'middlename' => '',

				'phone' => $appeal->phone,
				'email' => $appeal->email,

				'origin_id'   => 'old_' . $appeal->id,
				'origin_date' => $appeal->date,
				'origin_name' => $appeal->destination,

				'city'         => $appeal->city,
				'street_name'  => $appeal->street_name,
				'house_number' => $appeal->house_number,
				'flat'         => $appeal->flat,

				'lat' => $appeal->lat,
				'lng' => $appeal->lng,
			];

			$parts = explode(" ", $appeal->name, 3);

			if (isset($parts[1]))
				$item['name'] = $parts[1];

			if (isset($parts[0]))
				$item['surname'] = $parts[0];

			if (isset($parts[2]))
				$item['middlename'] = $parts[2];

			$this->output['contacts'][] = $item;
		}

	}

    private function email__test(){
        Global $_CFG ;

        $text = file_get_contents($_CFG['root'] . 'static/letter-sam24.html');

        $type = '_sisters24072020';
        print $text;

        $newMail = array(
            'type' => $type,
            'user_id' => -1,
            'receiver' => 'viksem999@gmail.com', //Bananstena@gmail.com //theshtab@gmail.com
            'subject' => 'Последний день сбора обращений в поддержку сестёр Хачатурян. ',
            'text' => $text,
            'send_title' => 'Подпишите обращение в Прокуратуру',
            'send_from' => 'no-reply-sisters',
            'priority' => 0,
            'result' => 'inqueue',
            'date' => 'now',
        );

        //$isSent = addEmailToQueue($newMail, true);

        //print '<pre>' . print_r($isSent, true) . '</pre>';

        //exit();

        $emailsRaw = db_appeals_list::find('all', [
            'select' => 'LOWER(email) as email',
            'conditions' => ['destination IN (11,12,29)'],
            'group' => 'LOWER(email)',
        ]);

        $emails = [];

        foreach ($emailsRaw as $_email)
            $emails[] = $_email->email ;

        $emails = array_unique($emails);

        //print count($emails);
        //exit();

        //$emails = file($_CFG['root'] . 'static/email_gpbsd');

        //var_dump($emails);
        //exit();

        $emailSentAlready = db_mailQueue::find('all', [
            'select' => 'id, TRIM(LOWER(receiver)) as receiver',
            'conditions' => ['type=?', $type],
        ]);

        $emailSentAlready = gdHandlerSkeleton::collectKeys($emailSentAlready, ['receiver']);


        $emailSentAlready2 = db_appeals_list::find('all', [
            'select' => 'id, TRIM(LOWER(email)) as receiver',
            'conditions' => ['destination=42'],
        ]);

        $emailSentAlready2 = gdHandlerSkeleton::collectKeys($emailSentAlready2, ['receiver']);


        $emailSentAlready = array_merge($emailSentAlready, $emailSentAlready2);
        //print '<pre>' . print_r($emails, true) . '</pre>';

        //var_dump(in_array('marinka1284@ya.ru', $emailSentAlready));

        $number = 1 ;
        foreach ($emails as $_email){

            $_email = mb_strtolower($_email, 'UTF-8');

            if (in_array($_email, $emailSentAlready))
                continue ;

            $_email = trim($_email);
            $_email = str_replace(["\n", "\r"], '', $_email);

            $newMail = array(
                'type' => $type,
                'user_id' => -1,
                'receiver' => $_email, //Bananstena@gmail.com //theshtab@gmail.com
                'subject' => 'Последний день сбора обращений в поддержку сестёр Хачатурян.',
                'text' => $text,
                'send_title' => 'Подпишите обращение в Прокуратуру',
                'send_from' => 'no-reply-sisters',
                'priority' => 0,
                'result' => 'inqueue',
                'date' => 'now',
            );

            //print '<pre>' . print_r($newMail, true) . '</pre>';
            //exit();

            //$isSent = addEmailToQueue($newMail, false);
            //exit();

            print $newMail['receiver'] . "\r\n" . '<br/>';

            $isSent = addEmailToQueue($newMail, false);

            //if ($number >= )
            //    break ;

            $number++;

        }

    }

    private function calc__apiRegionNameEparty(){

        $appeals = db_eparty_people::find('all', [
            'conditions' => ['api_city="" AND lat!="" AND lng!=""'],
            'select' => 'id, eparty_id, city, street, house, lat, lng, api_city, api_region_name',
            'limit' => 10000,
            'order' => 'rand()',
        ]);

        $list = [];

        foreach ($appeals as $_appeal){

            $_appeal = db_eparty_people::find_by_id($_appeal->id, [
                'select' => 'id, eparty_id, city, street, house, lat, lng, api_city, api_region_name',
            ]);

            //print $_appeal->api_city . "\r\n";
            if ($_appeal->api_city != '')
                continue ;

            $apiUrl = 'https://fempolitics.ru/ajax/ajax_utils.php?context=get__buildingRegionAndCityByCoords&lat=' . $_appeal->lat . '&lng=' . $_appeal->lng;

            $reply = json_decode(file_get_contents($apiUrl), true);

            $_appeal->api_city = $reply['result']['city'];
            $_appeal->api_region_name = $reply['result']['region_name'];

            if ($_appeal->api_city == '')
                $_appeal->api_city = -1;

            $_appeal->save();

            $item = $_appeal->to_array();

            $list[] = $item ;

            print "Updated: " . $_appeal->id . ' ' . $reply['result']['city'] . ", " . $reply['result']['region_name'] . "\r\n";
        }

        //print gdHandlerSkeleton::generateSimpleHtmlTable($list);

        //redirect('http://podpishi.org/ajax/ajax_stand.php?context=calc__apiRegionName');

        exit();

    }

    private function list__apiRegionName(){

        if (date('Y-m-d') != '2020-07-19'){
            print 'wymd';
            exit();
        }

        $appeals = db_appeals_list::find('all', [
            'conditions' => ['api_region_name="Бабушкинский"'],
            'select' => 'id, full_name, city, phone, email, api_city, api_region_name, city, street_name, house_number, flat, destination',
        ]);

        print count($appeals);


        $list = [];

        foreach ($appeals as $_appeal){

            $item = $_appeal->to_array();

            $campaign = db_campaigns_list::find_by_id($item['destination']);

            $item['campaign'] = $campaign->title ;

            $list[] = $item ;

        }

        print gdHandlerSkeleton::generateSimpleHtmlTable($list);

        //redirect('http://podpishi.org/ajax/ajax_stand.php?context=calc__apiRegionName');

        exit();

    }

    private function calc__goSubregionId(){

        $appeals = db_appeals_list::find('all', [
            'conditions' => ['go_subregion_id="notyet" AND lat!="" AND lng!=""'],
            'select' => 'id, lat, lng, go_subregion_id, go_city',
            'limit' => 10000,
            'order' => 'rand()',
        ]);

        //print '<pre>' . print_r($appeals, true) . '</pre>';
        //exit();

        $list = [];

        foreach ($appeals as $_appeal){

            $_appeal = db_appeals_list::find_by_id($_appeal->id, [
                'select' => 'id, lat, lng, go_subregion_id, go_city, yd_house_number, api_city, api_region_name',
            ]);

            //print $_appeal->api_city . "\r\n";
            if ($_appeal->go_subregion_id != 'notyet')
                continue ;

            $apiUrl = 'https://go.city4people.ru/ajax/ajax_utils.php?context=get__buildingRegionAndCityByCoords&lat=' . $_appeal->lat . '&lng=' . $_appeal->lng;

            //print $apiUrl;

            $reply = json_decode(file_get_contents($apiUrl), true);

            //print '<pre>' . print_r($reply, true) . '</pre>';
            //continue;

            $_appeal->go_subregion_id = $reply['result']['subregion_id'];
            $_appeal->go_city = $reply['result']['go_city'];

            $_appeal->save();

            $item = $_appeal->to_array();

            $list[] = $item ;

            print "Updated: " . $_appeal->id . ' ' . $reply['result']['go_city'] . ", " . $reply['result']['subregion_id'] . "\r\n";
        }

        //print gdHandlerSkeleton::generateSimpleHtmlTable($list);

        //redirect('http://podpishi.org/ajax/ajax_stand.php?context=calc__apiRegionName');

        exit();

    }

    private function calc__goOldAppealsSubregionId(){

        $appeals = db_old_appeals::find('all', [
            'conditions' => ['go_subregion_id="notyet" AND lat!="" AND lng!=""'],
            'select' => 'id, lat, lng, go_subregion_id, go_city',
            'limit' => 10000,
            'order' => 'rand()',
        ]);

        //print '<pre>' . print_r($appeals, true) . '</pre>';
        //exit();

        $list = [];

        foreach ($appeals as $_appeal){

            $_appeal = db_old_appeals::find_by_id($_appeal->id, [
                'select' => 'id, lat, lng, go_subregion_id, go_city',
            ]);

            //print $_appeal->api_city . "\r\n";
            if ($_appeal->go_subregion_id != 'notyet')
                continue ;

            $apiUrl = 'https://go.city4people.ru/ajax/ajax_utils.php?context=get__buildingRegionAndCityByCoords&lat=' . $_appeal->lat . '&lng=' . $_appeal->lng;

            //print $apiUrl;

            $reply = json_decode(file_get_contents($apiUrl), true);

            //print '<pre>' . print_r($reply, true) . '</pre>';
            //continue;

            $_appeal->go_subregion_id = $reply['result']['subregion_id'];
            $_appeal->go_city = $reply['result']['go_city'];

            $_appeal->save();

            $item = $_appeal->to_array();

            $list[] = $item ;

            print "Updated: " . $_appeal->id . ' ' . $reply['result']['go_city'] . ", " . $reply['result']['subregion_id'] . "\r\n";
        }

        //print gdHandlerSkeleton::generateSimpleHtmlTable($list);

        //redirect('http://podpishi.org/ajax/ajax_stand.php?context=calc__apiRegionName');

        exit();

    }

    private function calc__goEpartySubregionId(){

        $appeals = db_eparty_people::find('all', [
            'conditions' => ['go_subregion_id="notyet" AND lat!="" AND lng!=""'],
            'select' => 'id, eparty_id, city, go_subregion_id, go_city, lat, lng, api_city, api_region_name',
            'limit' => 5000,
            'order' => 'rand()',
        ]);

        $list = [];

        //print '<pre>' . print_r($appeals, true) . '</pre>';
        //exit();

        $list = [];

        foreach ($appeals as $_appeal){

            $_appeal = db_eparty_people::find_by_id($_appeal->id, [
                'select' => 'id, eparty_id, city, go_subregion_id, go_city, house, lat, lng, api_city, api_region_name',
            ]);

            //print $_appeal->api_city . "\r\n";
            if ($_appeal->go_subregion_id != 'notyet')
                continue ;

            $apiUrl = 'https://go.city4people.ru/ajax/ajax_utils.php?context=get__buildingRegionAndCityByCoords&lat=' . $_appeal->lat . '&lng=' . $_appeal->lng;

            //print $apiUrl;

            $reply = json_decode(file_get_contents($apiUrl), true);

            //print '<pre>' . print_r($reply, true) . '</pre>';
            //continue;

            $_appeal->go_subregion_id = $reply['result']['subregion_id'];
            $_appeal->go_city = $reply['result']['go_city'];

            $_appeal->save();

            $item = $_appeal->to_array();

            $list[] = $item ;

            print "Updated: " . $_appeal->id . ' ' . $reply['result']['go_city'] . ", " . $reply['result']['subregion_id'] . "\r\n";
        }

        //print gdHandlerSkeleton::generateSimpleHtmlTable($list);

        //redirect('http://podpishi.org/ajax/ajax_stand.php?context=calc__apiRegionName');

        exit();

    }

    private function calc__apiRegionNameOld(){

        $appeals = db_old_appeals::find('all', [
            'conditions' => ['api_city="" AND lat!="" AND lng!=""'],
            'select' => 'id, lat, lng, api_city, api_region_name',
            'limit' => 10000,
            'order' => 'rand()',
        ]);

        $list = [];

        foreach ($appeals as $_appeal){

            $_appeal = db_old_appeals::find_by_id($_appeal->id, [
                'select' => 'id, lat, lng, api_city, api_region_name',
            ]);

            //print $_appeal->api_city . "\r\n";
            if ($_appeal->api_city != '')
                continue ;

            $apiUrl = 'https://spb2019.yabloko.ru/ajax/ajax_utils.php?context=get__buildingRegionAndCityByCoords&lat=' . $_appeal->lat . '&lng=' . $_appeal->lng;

            //print $apiUrl;
            //exit();

            $reply = json_decode(file_get_contents($apiUrl), true);

            $_appeal->api_city = $reply['result']['city'];
            $_appeal->api_region_name = $reply['result']['region_name'];

            if ($_appeal->api_city == '')
                $_appeal->api_city = -1;

            $_appeal->save();

            $item = $_appeal->to_array();

            $list[] = $item ;

            print "Updated: " . $_appeal->id . ' ' . $reply['result']['city'] . ", " . $reply['result']['region_name'] . "\r\n";
        }

        //print gdHandlerSkeleton::generateSimpleHtmlTable($list);

        //redirect('http://podpishi.org/ajax/ajax_stand.php?context=calc__apiRegionName');

        exit();

    }

    private function calc__apiRegionName(){

        $appeals = db_appeals_list::find('all', [
            'conditions' => ['api_city="" AND lat!="" AND lng!=""'],
            'select' => 'id, lat, lng, yd_city, yd_street_name, yd_house_number, api_city, api_region_name',
            'limit' => 10000,
            'order' => 'rand()',
        ]);

        $list = [];

        foreach ($appeals as $_appeal){

            $_appeal = db_appeals_list::find_by_id($_appeal->id, [
                'select' => 'id, lat, lng, yd_city, yd_street_name, yd_house_number, api_city, api_region_name',
            ]);

            //print $_appeal->api_city . "\r\n";
            if ($_appeal->api_city != '')
                continue ;

            $apiUrl = 'https://spb2019.yabloko.ru/ajax/ajax_utils.php?context=get__buildingRegionAndCityByCoords&lat=' . $_appeal->lat . '&lng=' . $_appeal->lng;

            //print $apiUrl;
            //exit();

            $reply = json_decode(file_get_contents($apiUrl), true);

            $_appeal->api_city = $reply['result']['city'];
            $_appeal->api_region_name = $reply['result']['region_name'];

            if ($_appeal->api_city == '')
                $_appeal->api_city = -1;

            $_appeal->save();

            $item = $_appeal->to_array();

            $list[] = $item ;

            print "Updated: " . $_appeal->id . ' ' . $reply['result']['city'] . ", " . $reply['result']['region_name'] . "\r\n";
        }

        //print gdHandlerSkeleton::generateSimpleHtmlTable($list);

        //redirect('http://podpishi.org/ajax/ajax_stand.php?context=calc__apiRegionName');

        exit();

    }

    private function restore_appeal(){


        $campaign_id = 58;

        $appealLog = db_main_activityLog::find('first', [
            'conditions' => ['type="deleted_appeal" AND subarg_id=?', $campaign_id],
        ]);

        $details = unserialize($appealLog->details);

        $details['appealDump']['date'] = date('Y-m-d H:i:s', $details['appealDump']['date']);

        $dump = $details['appealDump'];

        $isExists = db_appeals_list::exists([
            'conditions' => ['phone=? AND destination=? AND full_name=?', $dump['phone'], $dump['destination'], $dump['full_name']],
        ]);

        if ($isExists){
            $appealLog->delete();
            print 'Exists<br/>';
            exit();
        }

        db_appeals_list::create($dump);

        print '<pre>' . print_r($details, true) . '</pre>';


    }


    private function hithere(){

        print 'hi';

    }



}

$CReg_user_ajax = new CReg_user_ajax;
?>
