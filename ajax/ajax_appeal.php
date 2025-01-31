<?php
include('../config.php');

/*
	ini_set('display_errors', true);
	ini_set('error_reporting',  E_ALL);
	error_reporting(E_ALL + E_STRICT);
*/



//print '<pre>' . print_r($_SESSION, true) . '</pre>';

class CReg_user_ajax extends gdHandlerSkeleton {
    public $output = array(); //Создаем массив который будет отослан в виде JSON.

    public function __construct() {
        Global $_CFG ;

        $this->output['error'] = 'false';

        if (isset($_GET['context'])) {
            switch ($_GET['context']) {
                case 'save__appeal':
                    $this->save__appeal();
                    break;

                case 'test__getAddress':
                    $this->test__getAddress();
                    break;


                case 'test__message':
                    $this->test__message();
                    break;

                case 'restore__appealfromLog':
                    $this->restore__appealfromLog();
                    break;

                case 'notify__stop_fb':
                    $this->notify__stop_fb();
                    break;

                case 'list_petitions':
                    $this->list_petitions();
                    break;

            }
        }

        if (isset($_GET['json']))
            print '<pre>' . print_r($this->output, true) . '</pre>';


        echo json_encode( $this->output );

    }


    /*
     * Arg: offset
     */
    private function list_petitions(){

        if (!isset($_GET['offset']) OR !is_numeric($_GET['offset']))
            $_GET['offset'] = 0;

        $campaigns = db_campaigns_list::getIndexList($_GET['offset'], 4, 'yabloko');

        $this->output['campaigns'] = array_slice($campaigns, 0, 3);
        $this->output['has_more'] = (count($campaigns) > 3);

    }

    private function notify__stop_fb(){


        if (isset($_POST['form']))
            $_GET = $_POST ;

        createGdLogEntry('fbshare', array(
            'tg_bot_status' => 'inqueue',
            'phone' => $_GET['form']['phone'],
            'email' => $_GET['form']['email'],
            'full_name' => $_GET['form']['full_name'],
            'city' => $_GET['form']['city'],
            'petition_id' => $_GET['petition_id'],
            'social' => $_GET['social'],
        ));

    }


    private function restore__appealfromLog(){

        $conditions = ['type="appeal_failedsave" AND arg_id=0'];

        $logs_left = db_main_activityLog::count([
            'conditions' => $conditions,
        ]);

        $logs = db_main_activityLog::find('all', [
            'conditions' => $conditions,
            'limit' => 20,
            'order' => 'id ASC',
        ]);

        $this->output['restore__appealfromLog'] = 1;

        print '($logs_left): ' . ($logs_left) . '<br/>';

        //exit();
        foreach ($logs as $log){

            print 'logid: ' . $log->id . '<br/>';

            $details = unserialize($log->details);

            //print '<pre>' . print_r($details, true) . '</pre>';
            //exit();
            $custom_post = $details['args_post'];

            if ($log->id == '103444'){

                $custom_post['form']['street_name'] = 'Московская обл., рабочий поселок Дрожжино, Новое шоссе';
                $custom_post['form']['house_number'] = 'д. 4, корп. 2';

            }

            if ($log->id == '101398'){

                $log->arg_id = -6;
                $log->save();
                continue ;

            }

            if (mb_strlen($custom_post['form']['street_name'], 'UTF-8') > 150){
                $log->arg_id = -5;
                $log->save();
                print 'wtf it was<br/>';
                continue ;
            }

            //print '<pre>' . print_r($custom_post, true) . '</pre>';

            //$url = 'https://podpishi.org/ajax/ajax_stand.php?context=save__appeal';

            //$content = gdHandlerSkeleton::post_query($url, $custom_post);

            if (isset($this->output['appeal_id']))
                unset($this->output['appeal_id']);

            $this->output['error'] = 'false';

            $_POST = $custom_post ;
            $this->save__appeal();

            print 'errror: ' . $this->output['error'] . '<br/>';

            if ($this->output['error'] == 'already signed'){

                $log->arg_id = 1;
                $log->save();

                print 'Already signed<br/>';
                print '<pre>' . print_r($custom_post, true) . '</pre>';

                redirect('https://podpishi.org/ajax/ajax_appeal.php?context=restore__appealfromLog&fefe');

                continue ;

            }

            if ($this->output['error'] == 'yes3'){

                //$log->arg_id = -1;
                //$log->save();

                print 'Sorry? yes3<br/>';
                print '<pre>' . print_r($custom_post, true) . '</pre>';

                continue ;

            }

            // the error that lead to failed log
            if ($this->output['error'] == 'yes oln'){

                print 'Sorry? yes oln<br/>';
                print '<pre>' . print_r($custom_post, true) . '</pre>';
                print '<pre>' . print_r($this->geocoder, true) . '</pre>';

                continue ;

            }


            if ($this->output['error'] == 'notInRussia' OR $this->output['error'] == 'not in russia'){

                $log->arg_id = -2;
                $log->save();

                print 'Already yes3<br/>';
                print '<pre>' . print_r($custom_post, true) . '</pre>';

                continue ;

            }

            if ($this->output['error'] == 'policy_confirm'){

                $log->delete();

                continue ;

            }

            if ($this->output['error'] == 'false'){

                $appeal = db_appeals_list::find_by_id($this->output['appeal_id']);

                $appeal->date = $log->date->format('Y-m-d H:i:s');
                $appeal->save();

                $log->arg_id = 1;
                $log->save();

                print '<b>Success ' . $custom_post['form']['email'] . '</b><br/>';
                continue ;

            }
            else {
                print 'Error ' . $this->output['error'];
                exit();
            }







            break ;

            //var_dump($content);

            //post_query

            //print '<pre>' . print_r($content, true) . '</pre>';


            //exit();
        }

    }

    private function test__getAddress(){

        ini_set('display_errors', true);
        ini_set('error_reporting',  E_ALL);
        error_reporting(E_ALL + E_STRICT);

        $address = 'Троицк, Центральная 18';

        $reply = $this->debug_getCoordsByAddress($address);

        print '<pre>' . print_r($reply, true) . '</pre>';

    }

    private function checkUserAddress($form){
        Global $_CFG ;

        $address = trim($form['city'] . ', улица ' . $form['street_name'] . ', дом ' . $form['house_number']);

        if (isset($_GET['fefe'])){
            print $address . '<br/>';
        }

        $apikey_yd = $_CFG['ydGeocoderKey'] ;

        $site_domain = $_SERVER['HTTP_HOST'];

        if ($site_domain == 'save-belarus.org')
            $apikey_yd = '9e89ef7e-7675-4324-ba7b-5f2c2fcc91fa';

        $geocoder = uniGeocoder::requestQualifiedCoordsByAddress($address, $apikey_yd);
        $this->geocoder = $geocoder ;


        if ($geocoder['result'] != true OR $geocoder['building']['has_building'] != true){

            if ($geocoder['building']['error_text'] == 'Not in Russia'){
                $this->output['error_text'] = 'Адрес находится за пределами РФ, с ним не получится оставить подпись';
                $this->output['error'] = 'not in russia';
                return ;
            }

            if ($_POST['form']['full_name'] == 'Булгакова Михаила Афанасьевича'){

                $this->output['$geocoder'] = $geocoder;
                $this->output['$address'] = $address;


            }

            return ;
        }

        $_POST['yd_address'] = $geocoder['building'];
        $_POST['used_engine'] = $geocoder['used_engine'] ;

        $_POST['lat'] = $geocoder['coords']['lat'] ;
        $_POST['lng'] = $geocoder['coords']['lng'] ;

        //print '<pre>' . print_r($_POST, true) . '</pre>';
        //exit();

    }

    private function checkUserSign(){
        Global $_CFG ;

        // base64 size / 3
        if (!isset($_POST['sign']) OR strlen($_POST['sign']) / 3 > 100 * 1024){
            $this->output['error'] = 'ntf attach1';
            $this->output['error_text'] = 'Изображение с вашей подписью неправильное, попробуйте ещё раз';
            return ;
        }

        // опытным путём установлено, что пустая петиция имеет размер в байтах менее 1800
        if (strlen($_POST['sign']) < 1200){
            $this->output['error'] = 'ntf attach1';
            $this->output['error_text'] = 'Изображение с вашей подписью не удалось сохранить. Попробуйте обновить страницу и оставить подпись снова';
            return ;
        }

        if (strpos($_POST['sign'], 'data:image/png;base64,') === false){
            $this->output['error'] = 'ntf attach2';
            $this->output['error_text'] = 'Изображение с вашей подписью неправильное, попробуйте ещё раз';
            return ;
        }

        $_POST['sign_md5'] = md5($_POST['sign']);

        if (in_array($_POST['sign_md5'], ['2a12b687e35b1403e251c7626eda970e', 'bd1400b82f87cf7769b22c99239e5036', 'b6d7a745a30a8688b2649e8f2f9ca9f5', 'ded3462ce7b20123a0697fa4e170bfc1'])){
            $this->output['error'] = 'ntf attach21';
            $this->output['error_text'] = 'Вы, вероятно, забыли поставить подпись';
            return ;
        }

        $tmp_path = $_CFG['root'] . 'cache/tmp/' . $_POST['sign_md5'] . '.png';


        file_put_contents($tmp_path, base64_decode(substr($_POST['sign'], 22)));

        if(($f_type = getimagesize($tmp_path)) != true){
            $this->output['error'] = 'ntf attach3';
            $this->output['error_text'] = 'Изображение с вашей подписью неправильное, попробуйте ещё раз';
            unlink($tmp_path);
            return ;
        }

        if (!in_array($f_type[0], [328, 340]) OR $f_type[1] != 125){
            $this->output['error'] = 'ntf attach4';
            $this->output['error_text'] = 'Изображение с вашей подписью неправильное, попробуйте ещё раз ' . $f_type[0] . '-' . $f_type[1];
            unlink($tmp_path);
            return ;
        }

        unlink($tmp_path);

    }


    private function save__appeal(){
        Global $_USER;

        //print '<pre>' . print_r($_POST, true) . '</pre>';

        //exit();

        $form = @$_POST['form'];

        $destination = @$_POST['destination']['id'];

        $campaign = db_campaigns_list::find('first', [
            'conditions' => ['id=? AND is_active=1', $destination],
        ]);

        $isMoscowRegionsMode = false;

        if (!empty($campaign) AND classFactory::c_isset($campaign->extra_class, 'moscow_regions'))
            $isMoscowRegionsMode = true;

        $requiredFields = [
            'full_name', 'city', 'street_name', 'house_number', 'flat', 'phone', 'email'
        ];

        if ($isMoscowRegionsMode)
            $requiredFields = [
                'full_name', 'city', 'region_name', 'phone', 'email'
            ];

        if (empty($campaign) OR $campaign->id == 33){

            $requiredFields = [
                'full_name', 'ng_apple_role', 'ng_apple_city', 'ng_apple_year', 'phone', 'email'
            ];

        }

        if (empty($campaign) OR $campaign->url == 'bryuhanova2021_all'){

            $requiredFields = [
                'full_name', 'city', 'phone', 'email'
            ];

        }

        if (empty($campaign) OR $campaign->domain == 'yabloko'){

            $requiredFields = [
                'full_name', 'city', 'phone', 'email'
            ];

            if (!isset($form['policy_confirm']) OR ($form['policy_confirm'] != 'true' AND $form['policy_confirm'] != 1)){
                $this->output['error'] = 'policy_confirm';
                $this->output['error_text'] = 'Вы не дали согласие на обработку персональных данных';
                return ;
            }

        }

        if (empty($campaign) OR $campaign->id == 34){

            $requiredFields = [
                'full_name', 'region_name', 'phone', 'email'
            ];

        }

        if (empty($campaign) OR $campaign->id == 35){

            $requiredFields = [
                'full_name', 'phone', 'email'
            ];

        }

        foreach ($requiredFields as $_param){

            if (!isset($form[$_param])){

                $this->output['error'] = 'all fields required';
                $this->output['error_text'] = 'Все поля являются обязательными';
                return ;

            }

            if ($_param != 'flat' AND $_param != 'full_name' AND $_param != 'ng_apple_city' AND $_param != 'ng_apple_year')
                if ($form[$_param] == ''){

                    $this->output['error'] = 'all fields required not empty' . $_param;
                    $this->output['error_text'] = 'Все поля являются обязательными';
                    return ;

                }

        }



        if (empty($campaign)){
            $this->output['error_text'] = 'Сбор подписей недоступен';
            $this->output['error'] = 'campaign empty';
            return ;
        }

        if ($campaign->id == 33){

            if (!in_array($form['ng_apple_role'], ['Сторонник партии', 'Депутат от Яблока', 'Член Яблока'])){

                $this->output['error'] = 'wrong ng_apple_role';
                $this->output['error_text'] = 'ng_apple_role';
                $this->output['field'] = 'ng_apple_role';
                return ;

            }

            if ($form['ng_apple_city'] == '' AND $form['ng_apple_year'] == ''){
                $this->output['error'] = 'all fields required33';
                $this->output['error_text'] = 'Все поля являются обязательными';
                return ;
            }

        }

        // use default text or usedfined
        if ($campaign->is_appeal_editable){

            if (mb_strlen($_POST['appeal_text'], 'UTF-8') > 8000){
                $this->output['error'] = 'too long appeal_text';
                $this->output['error_text'] = 'Вы написали слишком длинный текст петиции, попробуйте сократить';
                return ;
            }

            if (mb_strlen($_POST['appeal_title'], 'UTF-8') > 250){
                $this->output['error'] = 'too long appeal_title';
                $this->output['error_text'] = 'Вы написали слишком длинный заголовок, попробуйте сократить';
                return ;
            }

        }
        else {

            $_POST['appeal_text'] = pdPetitions::formatParagraph($campaign->appeal_text);
            $_POST['appeal_title'] = $campaign->appeal_title;

        }


        if (strpos($form['full_name'], '.') !== false){
            $this->output['error'] = 'dot found in full_name';
            $this->output['error_text'] = 'Инициалы недопустимы в официальном обращении, укажите полностью ФИО';
            $this->output['field'] = 'full_name';
            return ;
        }

        $form['full_name'] = trim($form['full_name']);

        if ($form['full_name'] == ''){
            $this->output['error'] = 'empty full_name';
            $this->output['error_text'] = 'Нужно обязательно указать Имя и Фамилию';
            $this->output['field'] = 'full_name';
            return ;
        }



        $form['email'] = mb_strtolower($form['email'], 'UTF-8');

        if(filter_var($form['email'], FILTER_VALIDATE_EMAIL) == false) {
            $this->output['error_text'] = 'Укажите корректный E-mail';
            $this->output['error'] = 'wrong email';
            $this->output['field'] = 'email';
            return ;
        }

        $form['phone'] = parseAndformatPhone($form['phone']);

        if (strlen($form['phone']) < 11 OR strlen($form['phone']) >= 15){
            $this->output['error'] = 'wrong phone length';
            $this->output['error_text'] = 'Проверьте, правильно ли вы указали номер телефона';
            $this->output['field'] = 'phone';
            return ;
        }

        if ($campaign->id == 34){

            if ($form['region_name'] == '')
                $form['region_name'] = -1;

            $isRegionExists = db_regions_list::exists([
                'conditions' => ['region_name=? AND city="Санкт-Петербург"', $form['region_name']],
            ]);

            if (!$isRegionExists){
                $this->output['error'] = 'wrong region_name';
                $this->output['error_text'] = 'Выберите МО из списка';
                $this->output['field'] = 'region_name';
                return ;
            }

        }

        if ($isMoscowRegionsMode){

            if ($form['region_name'] == '')
                $form['region_name'] = -1;

            $isRegionExists = db_regions_list::exists([
                'conditions' => ['region_name=? AND city="Москва"', $form['region_name']],
            ]);

            if (!$isRegionExists){
                $this->output['error'] = 'wrong region_name';
                $this->output['error_text'] = 'Выберите район из списка';
                $this->output['field'] = 'region_name';
                return ;
            }

        }


        /*
         * Is unnecessary for yabloko
         */
        $this->checkUserSign();
        if ($this->output['error'] != 'false')
            if ($campaign->domain == 'yabloko'){
                $this->output['error'] = 'false';
                $this->output['error_text'] = '';
            }
            else {
                return ;
            }


            if ($campaign->domain == 'yabloko'){



            }

        if (!$isMoscowRegionsMode)
            if ($campaign->domain != 'yabloko')
                if ($campaign->id != 33 AND $campaign->id != 34 AND $campaign->id != 35)
                    $this->checkUserAddress($form);

        if ($this->output['error'] != 'false') return ;

        //print '<pre>' . print_r($_POST, true) . '</pre>';
        //exit();

        // если ранее уже подписывал манифест

        $isSignExists = db_appeals_list::exists(array(
            'conditions' => array('(phone=? OR email=?) AND destination=?', $form['phone'], $form['email'], $destination),
        ));

        if ($isSignExists){
            $this->output['error'] = 'already signed';
            $this->output['error_text'] = 'Вы уже подписывали петицию, спасибо!';

            //tgBotApi::sendMessageNow('-1001391976289', 'Вы уже подписывали петицию');

            return ;
        }

        // если за последние 10 минут было больше двух попыток - запрещаем

        $amountOfDuplicatesIp = db_appeals_list::count(array(
            'conditions' => array('ip_addr=? AND destination=? AND date>=NOW() - INTERVAL 10 MINUTE', GetRealIp(), $destination),
        ));

        if ($amountOfDuplicatesIp == 0 AND mt_rand(1, 5) == 3)
            unset($_SESSION['blockedIp']);

        if ($amountOfDuplicatesIp >= 7 OR isset($_SESSION['blockedIp'])){

            $_SESSION['blockedIp'] = true ;

            $this->output['error'] = 'already signed ip';
            $this->output['error_text'] = 'Вы уже подписывали петицию, спасибо!';
            return ;
        }



        $newSign = array(
            'signature' => $_POST['sign'],
            'appeal_text' => htmlspecialchars($_POST['appeal_text'], ENT_NOQUOTES),
            'appeal_title' => htmlspecialchars($_POST['appeal_title'], ENT_NOQUOTES),
            'ip_addr' => GetRealIp(),
            'destination' => $destination,
        );

        if ($campaign->domain == 'yabloko'){

            $newSign['street_name'] = htmlspecialchars($form['street_name'], ENT_NOQUOTES);
            $newSign['house_number'] = htmlspecialchars($form['house_number'], ENT_NOQUOTES);
            $newSign['flat'] = htmlspecialchars($form['flat'], ENT_NOQUOTES);

            $newSign['rn_name'] = htmlspecialchars($form['rn_name'], ENT_NOQUOTES);
            $newSign['rn_name'] = trim($newSign['rn_name']);

            if (in_array($newSign['rn_name'], ['none']))
                $newSign['rn_name'] = '';

            if (isset($form['region_name']))
                $newSign['region_name'] = $form['region_name'];

        }

        if ($campaign->domain != 'yabloko') {

            if (!$isMoscowRegionsMode AND $campaign->id != 33 AND $campaign->id != 34 AND $campaign->id != 35) {

                $newSign['lat'] = $_POST['lat'];
                $newSign['lng'] = $_POST['lng'];

                $newSign['yd_city'] = $_POST['yd_address']['city'];
                $newSign['yd_house_number'] = $_POST['yd_address']['house_number'];
                $newSign['yd_street_name'] = $_POST['yd_address']['street_name'];
                $newSign['geocoder'] = $_POST['used_engine'];

                $newSign['yd_addr_hash'] = md5($newSign['yd_city'] . '' . $newSign['yd_street_name'] . $newSign['yd_house_number']);

            } else {
                $newSign['yd_city'] = 'Москва';
            }

            if ($campaign->id == 33) {

                $newSign['ng_apple_role'] = htmlspecialchars($form['ng_apple_role'], ENT_NOQUOTES);
                $newSign['ng_apple_city'] = htmlspecialchars($form['ng_apple_city'], ENT_NOQUOTES);
                $newSign['ng_apple_year'] = htmlspecialchars($form['ng_apple_year'], ENT_NOQUOTES);

            }

            if ($campaign->id == 34) {

                $newSign['region_name'] = htmlspecialchars($form['region_name'], ENT_NOQUOTES);

            }

        }


        $newSign['utm_list'] = '';
        if (isset($_SESSION['utmList']))
            $newSign['utm_list'] = serialize($_SESSION['utmList']);



        foreach ($requiredFields as $_param)
            $newSign[$_param] = htmlspecialchars($form[$_param], ENT_NOQUOTES);

        if ($newSign['email'] == 'viksem999@gmail.com'){

            print '<pre>' . print_r($newSign, true) . '</pre>';
            exit();
        }

        //print '<pre>' . print_r($newSign, true) . '</pre>';
        //exit();

        foreach ($newSign as $key => $value)
            $newSign[$key] = trim($value);

        $appeal = db_appeals_list::create($newSign);

        $this->output['appeal_id'] = $appeal->id ;

        createGdLogEntry('new_appeal', array(
            'tg_bot_status' => 'inqueue',
            'arg_id' => $appeal->id,
            'subarg_id' => $campaign->id,
        ));

        //print '<pre>' . print_r($newSign, true) . '</pre>';
    }


}




$CReg_user_ajax = new CReg_user_ajax;
?>