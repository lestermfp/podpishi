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

        $this->output['error'] = 'false';

        if (isset($_GET['context'])) {
            switch ($_GET['context']) {
                case 'save__petition':
                    $this->save__petition();
                    break;
                case 'save__user':
                    $this->save__user();
                    break;

                case 'save__author':
                    $this->save__author();
                    break;

                case 'remove__appeal':
                    $this->remove__appeal();
                    break;

                case 'get__campaignById':
                    $this->get__campaignById();
                    break;

                case 'get__campaignStatsById':
                    $this->get__campaignStatsById();
                    break;


                case 'get__appeals':
                    $this->get__appeals();
                    break;

                case 'get_accounts':
                    $this->get_accounts();
                    break;

                case 'list_authors':
                    $this->list_authors();
                    break;

                case 'update__accountRole':
                    $this->update__accountRole();
                    break;

                case 'get__userById':
                    $this->get__userById();
                    break;

                case 'get__authorById':
                    $this->get__authorById();
                    break;

                case 'save__petitionConfirmed':
                    $this->save__petitionConfirmed();
                    break;


                case 'save__editedAppeal':
                    $this->save__editedAppeal();
                    break;

                case 'save__appealWrongEmail':
                    $this->save__appealWrongEmail();
                    break;

                case 'generate__xslxExport':
                    $this->generate__xslxExport();
                    break;

            }
        }

        if (isset($_GET['json']))
            print '<pre>' . print_r($this->output, true) . '</pre>';

        echo json_encode( $this->output );

    }


    /*
     * Arg: campaign_id
     */
    private function generate__xslxExport(){
        Global $_CFG, $_USER ;

        include($_CFG['root'] . 'classes/simpleXLSXGen.php');

        $pdCampaign = new pdPetitions($_POST['campaign_id']);

        if (!$pdCampaign->isExists() OR !$pdCampaign->hasPrevi()){
            $this->output['error'] = 'ntf';
            $this->output['error_text'] = 'Not available';
            return;
        }

        if ($pdCampaign->get()->domain != 'yabloko'){
            $this->output['error'] = 'ntf';
            $this->output['error_text'] = 'Not appropriate';
            return;
        }

        $podpishiXslxFactory = new podpishiXslxFactory();

        $podpishiXslxFactory->setCampaign($pdCampaign->get());

        $filename = $podpishiXslxFactory->generateFilename();

        $podpishiXslxFactory->saveAs($_CFG['root'] . 'cache/xlsx/' . $filename);

        gc_collect_cycles();

        createGdLogEntry('export_xslx', array(
            'tg_bot_status' => 'none',
            'arg_id' => $pdCampaign->get()->id,
            'count_export' => count($podpishiXslxFactory->getAppeals()),
        ));


        //$xlsx_struct = $podpishiXslxFactory->getStruct();

        //$podpishiXslxFactory->downloadAs();

        //print '<pre>' . print_r($xlsx_struct, true) . '</pre>';

        //exit();

        //exit();
        //$this->output['target_url'] = '/cache/xslx/' . basename($target_filename);

        $this->output['filename'] = $filename;

        //exit();

    }

    /*
     * Arg: appeal
     */
    private function save__appealWrongEmail(){
        $this->checkAuthLevel(array('news_editor', 'admin', 'settler', 'coordinator'));
        if ($this->output['error'] != 'false') return;

        if (isset($_POST['context']))
            $_GET = $_POST ;

        $form = $_GET['appeal'] ;

        $appeal = db_appeals_list::find_by_id($form['id']);

        $appeal->is_email_invalid = 1;
        $appeal->suggestion = $form['suggestion'];
        $appeal->save();


    }

    /*
     * Arg: appeal
     */
    private function save__editedAppeal(){
        $this->checkAuthLevel(array('news_editor', 'admin', 'settler', 'coordinator'));
        if ($this->output['error'] != 'false') return;

        if (isset($_POST['context']))
            $_GET = $_POST ;

        $form = $_GET['appeal'] ;

        $appeal = db_appeals_list::find_by_id($form['id']);

        $campaign = new pdPetitions($appeal->destination);

        if (!$campaign->isExists()){
            $this->output['error'] = 'ntf campaign';
            $this->output['error_text'] = 'Не найдена кампания';
            return ;

        }

        if (!$campaign->hasPrevi()){
            $this->output['error'] = 'wrong previ';
            $this->output['error_text'] = 'Недоступная кампания';
            return ;
        }

        $form['phone'] = parseAndformatPhone($form['phone']);

        foreach (['phone', 'full_name', 'city', 'street_name', 'house_number', 'flat', 'email', 'rn_name'] as $param)
            if (isset($form[$param]))
                if ($appeal->read_attribute($param) != $form[$param]){

                    $appeal->assign_attribute($param, $form[$param]) ;


                }


        $appeal->is_checked = 1;
        $appeal->save();



    }

    /*
     * Arg: campaign_id, status
     */
    private function save__petitionConfirmed(){

        $this->checkAuthLevel(array('admin'));
        if ($this->output['error'] != 'false') return;


        $campaign = new pdPetitions($_GET['campaign_id']);

        if (!$campaign->isExists()){
            $this->output['error'] = 'ntf campaign';
            $this->output['error_text'] = 'Не найдена кампания';
            return ;

        }

        $campaign->get()->is_confirmed = $_GET['status'];
        $campaign->get()->save();

    }

    /*
     * Arg: form (with id or without for create new one)
     */
    private function save__user(){
        Global $_USER ;

        $this->checkAuthLevel(array('admin'));
        if ($this->output['error'] != 'false') return;

        $form = @$_GET['form'] ;

        if (isset($_POST['form']))
            $form = $_POST['form'];

        $requiredParams = array('id', 'name', 'surname', 'middlename', 'email', 'phone', 'password', 'role', 'petition_city');

        foreach ($requiredParams as $_param){
            if (!isset($form[$_param])){

                $form[$_param] = '';

                continue;

            }

            $form[$_param] = trim($form[$_param]);
            $form[$_param] = htmlspecialchars($form[$_param], ENT_NOQUOTES);

        }


        $form['phone'] = parseAndformatPhone($form['phone']);

        if ($form['phone'] == ''){
            $this->output['error'] = 'ntf phone';
            $this->output['error_text'] = 'Телефон некорректный';
            return ;
        }

        $form['email'] = mb_strtolower($form['email'], 'UTF-8');

        if(filter_var($form['email'], FILTER_VALIDATE_EMAIL) == false) {
            $this->output['error_text'] = 'Укажите корректный E-mail';
            $this->output['error'] = 'wrong email';
            return ;
        }


        //print '<pre>' . print_r($form, true) . '</pre>';

        if ($form['id'] == '' OR $form['id'] == -1 OR !is_numeric($form['id'])){

            $isSameExists = db_main_users::exists([
                'conditions' => ['email=? OR phone=?', $form['email'], $form['phone']],
            ]);

            if ($isSameExists){

                $this->output['error'] = 'duplicate email or phone';
                $this->output['error_text'] = 'С такой почтой или телефоном существует аккаунт';
                return ;

            }

            if (!in_array($form['role'], ['admin', 'settler', 'guest']))
                $form['role'] = 'guest';

            $newItem = [
                'salt' => md5(json_encode($form) . mt_rand(1, 100)),
                'reg_date' => 'now',
            ];

            foreach ($requiredParams as $_param)
                $newItem[$_param] = $form[$_param] ;


            $newItem['password'] = md5($form['password'] . $newItem['salt']);

            unset($newItem['id']);

            $userItem = db_main_users::create($newItem);

        }
        else {

            $userItem = db_main_users::find_by_id($form['id']);

            $isUpdatable = false;

            foreach ($requiredParams as $_param) {

                if ($_param == 'password')
                    continue ;

                if ($_param == 'id')
                    continue ;

                if ($userItem->$_param == $form[$_param])
                    continue ;

                if ($_param == 'role')
                    if (!in_array($form[$_param], ['admin', 'settler', 'guest', 'coordinator']))
                        continue ;

                if ($_param == 'phone'){

                    $isSameExists = db_main_users::exists([
                        'conditions' => ['phone=?', $form['phone']],
                    ]);

                    if ($isSameExists){

                        $this->output['error'] = 'ntf url';
                        $this->output['error_text'] = 'Такой телефон уже занят';
                        return ;

                    }

                }

                if ($_param == 'email'){

                    $isSameExists = db_main_users::exists([
                        'conditions' => ['email=?', $form['email']],
                    ]);

                    if ($isSameExists){

                        $this->output['error'] = 'ntf url';
                        $this->output['error_text'] = 'Такой email уже занят';
                        return ;

                    }

                }


                $userItem->$_param = $form[$_param] ;

                $isUpdatable = true ;

            }

            if ($form['password'] != '') {
                $userItem->password = md5($form['password'] . $userItem->salt);
                $isUpdatable = true;
            }

            if ($isUpdatable)
                $userItem->save();

        }


        $this->output['user'] = [
            'id' => $userItem->id,
        ];


    }


    /*
     * Arg: form (with id or without for create new one)
     */
    private function save__author(){
        Global $_USER ;

        $this->checkAuthLevel(array('admin', 'coordinator'));
        if ($this->output['error'] != 'false') return;

        $form = @$_GET['form'] ;

        if (isset($_POST['form']))
            $form = $_POST['form'];

        $requiredParams = array('id', 'name', 'surname', 'middlename', 'email', 'phone', 'description', 'socials', 'avatar_url');

        foreach ($requiredParams as $_param){

            if (!isset($form[$_param])){

                $form[$_param] = '';

                continue;

            }

            if ($_param == 'socials')
                continue ;

            $form[$_param] = trim($form[$_param]);
            $form[$_param] = htmlspecialchars($form[$_param], ENT_NOQUOTES);

        }
        
        //print '<pre>' . print_r($form, true) . '</pre>';

        if ($form['id'] == '' OR $form['id'] == -1 OR !is_numeric($form['id'])){

            if (empty($_USER->getMainUser()->getDomains())){
                $this->output['error_text'] = 'Вы не можете создавать авторов кампаний. Свяжитесь с администрацией';
                $this->output['error'] = 'wrong domains';
                return ;
            }

            $domain = $_USER->getMainUser()->getDomains()[0];

            $newItem = [
                'domain' => $domain,
                'created_by_id' => $_USER->getMainUser()->id,
                'date' => 'now',
            ];

            $userItem = db_authors_list::create($newItem);

        }
        else {

            $userItem = db_authors_list::find_by_id($form['id']);

        }

        if (!$userItem->isEditableBy($_USER->getMainUser())){
            $this->output['error'] = 'ntf sd';
            $this->output['error_text'] = 'ntf sd';
            return ;
        }

        $isUpdatable = false;

        foreach ($requiredParams as $_param) {

            if ($_param == 'id')
                continue ;

            if ($_param == 'domain')
                continue ;

            if ($_param == 'socials'){

                //if (!is_array($form[$_param]))
                //    $form[$_param] = [];

                //foreach ($form[$_param] as $socialkey => $social)
                //    if (!is_string($social))
                //        unset($form[$_param][$socialkey]);


                $userItem->socials_raw = json_encode($form[$_param]) ;

                $isUpdatable = true ;
                continue ;

            }

            if ($userItem->$_param == $form[$_param])
                continue ;

            $userItem->$_param = $form[$_param] ;

            $isUpdatable = true ;

        }


        if ($isUpdatable)
            $userItem->save();



        $this->output['user'] = [
            'id' => $userItem->id,
        ];


    }

    /*
     * Arg: author_id
     */
    public function get__authorById(){
        Global $_USER ;

        $this->checkAuthLevel(array('admin', 'coordinator'));
        if ($this->output['error'] != 'false') return;

        $author = db_authors_list::find_by_id($_GET['author_id']);

        if (!$author->isEditableBy($_USER->getMainUser())){
            $this->output['error'] = 'ntf sd';
            return ;
        }

        $this->output['user'] = $author->getPublicInfo();

    }

    /*
     * Arg: user_id
     */
    private function get__userById(){
        Global $_USER ;

        $this->checkAuthLevel(array('admin'));
        if ($this->output['error'] != 'false') return;

        $user = db_main_users::find_by_id($_GET['user_id'], [
            'select' => 'id, name, surname, middlename, phone, email, role, "" as password, petition_city',
        ]);

        $this->output['user'] = $user->to_array();

    }

    /*
     * Arg: user_id, role
     */
    private function update__accountRole(){
        Global $_USER ;

        $this->checkAuthLevel(array('admin'));
        if ($this->output['error'] != 'false') return;

        $user = db_main_users::find_by_id($_GET['user_id']);

        if (empty($user))
            return ;

        if ($user->id == $_USER->getOptions('id'))
            return ;

        $user->role = $_GET['role'];
        $user->save();


    }

    private function list_authors(){
        Global $_USER;

        $this->checkAuthLevel(array('admin', 'coordinator'));

        if ($this->output['error'] != 'false') return;

        $authors = db_authors_list::getListForUser($_USER->getMainUser());

        $this->output['authors'] = [];

        foreach ($authors as $author){

            $item = $author->getPublicInfo();

            $item['created_by'] = $author->getCreatedByInfo();

            $this->output['authors'][] = $item ;

        }

    }

    private function get_accounts(){

        $this->checkAuthLevel(array('admin'));
        if ($this->output['error'] != 'false') return;

        $accounts = db_main_users::find('all', [
            'select' => 'id, name, surname, middlename, phone, email, role, last_activity, petition_city, oauth_from',
            'order' => 'id DESC',
        ]);

        $this->output['accounts'] = [];

        foreach ($accounts as $_account){

            $item = $_account->to_array();

            $item['last_activity_h'] = 'n/a';

            if (is_object($_account->last_activity))
                $item['last_activity_h'] = $_account->last_activity->format('d.m.Y H:i');

            $item['is_editable'] = false ;

            $this->output['accounts'][] = $item ;

        }

    }


    /*
     * Arg: campaign_id
     */
    private function get__appeals(){
        Global $_USER;

        $this->checkAuthLevel(array('news_editor', 'admin', 'settler', 'coordinator'));
        if ($this->output['error'] != 'false') return;


        $campaign = new pdPetitions($_GET['campaign_id']);

        if (!$campaign->isExists()){
            $this->output['error'] = 'ntf campaign';
            $this->output['error_text'] = 'Не найдена кампания';
            return ;

        }

        if (!$campaign->hasPrevi()){
            $this->output['error'] = 'wrong previ';
            $this->output['error_text'] = 'Недоступная кампания';
            return ;
        }


        $this->output['appeals'] = [];

        $appeals = db_appeals_list::find('all', [
            'conditions' => ['destination=?', $campaign->get()->id],
            'select' => 'id, full_name, city, street_name, region_name, house_number, rn_name, flat, phone, email, date, is_checked',
            'order' => 'date DESC',
        ]);

        foreach ($appeals as $_appeal){

            $item = $_appeal->to_array();

            $item['date_h'] = $_appeal->date->format('d.m.Y');
            $item['time_h'] = $_appeal->date->format('H:i');
            $item['phone'] = formatPhone($item['phone']);

            if ($_USER->hasExtraClass('no_contacts'))
                unset($item['phone'], $item['email']);

            //$item['city'] .= ' ' . $item['region_name'] ;

            $this->output['appeals'][] = $item ;

        }


    }

    /*
     * Arg: appeal_id
     */
    private function remove__appeal(){
        Global $_USER ;

        $this->checkAuthLevel(array('news_editor', 'admin', 'settler', 'coordinator'));
        if ($this->output['error'] != 'false') return;

        $appeal = db_appeals_list::find_by_id($_GET['appeal_id']);

        if (empty($appeal))
            return ;

        $campaign = new pdPetitions($appeal->destination);

        if (!$campaign->isExists()){
            $this->output['error'] = 'ntf campaign';
            $this->output['error_text'] = 'Не найдена кампания';
            return ;

        }

        if (!$campaign->hasPrevi()){
            $this->output['error'] = 'wrong previ';
            $this->output['error_text'] = 'Недоступная кампания';
            return ;
        }


        createGdLogEntry('deleted_appeal', array(
            'tg_bot_status' => 'none',
            'arg_id' => $appeal->id,
            'subarg_id' => $appeal->destination,
            'appealDump' => $appeal->to_array(),
        ));

        $appeal->delete();
    }

    /*
     * Arg: campaign_id
     */
    private function get__campaignStatsById(){
        Global $_USER ;

        $this->checkAuthLevel(array('news_editor', 'admin', 'settler', 'coordinator'));
        if ($this->output['error'] != 'false') return;

        $campaign = new pdPetitions($_GET['campaign_id']);

        if (!$campaign->isExists()){
            $this->output['error'] = 'ntf campaign';
            $this->output['error_text'] = 'Не найдена кампания';
            return ;

        }

        if (!$campaign->hasPrevi()){
            $this->output['error'] = 'wrong previ';
            $this->output['error_text'] = 'Недоступная кампания';
            return ;
        }


        $this->output['petition'] = [
            'id' => $campaign->get()->id,
            'title' => $campaign->get()->title,
            'domain' => $campaign->get()->domain,
            'stats' => $campaign->getStats(),
        ];
    }

    /*
     * Arg: campaign_id
     */
    private function get__campaignById(){
        Global $_USER ;

        $this->checkAuthLevel(array('news_editor', 'admin', 'settler', 'coordinator'));
        if ($this->output['error'] != 'false') return;

        $campaign = new pdPetitions($_GET['campaign_id']);

        if (!$campaign->isExists()){
            $this->output['error'] = 'ntf campaign';
            $this->output['error_text'] = 'Не найдена кампания';
            return ;

        }

        if (!$campaign->hasPrevi()){
            $this->output['error'] = 'wrong previ';
            $this->output['error_text'] = 'Недоступная кампания';
            return ;
        }


        $this->output['campaign'] = $campaign->get()->to_array();
        $this->output['campaign']['authors'] = $campaign->get()->getAuthorsPublicCached();
        $this->output['campaign']['domain_name'] = $campaign->get()->getDomainName();

        $authors = db_authors_list::getListForUser($_USER->getMainUser());

        $this->output['authors_collection'] = [];

        foreach ($authors as $author)
            $this->output['authors_collection'][] = $author->getPublicInfo();

        $attaches_list = db_attaches_list::find('all', array(
            'conditions' => array('connected_id=? AND category="petition_attach"', $campaign->get()->id),
        ));

        $this->output['attachList'] = mdFiles::parseAttachesOutput($attaches_list);

    }


    private function save__petition(){
        Global $_USER ;

        $this->checkAuthLevel(array('admin', 'settler', 'coordinator'));
        if ($this->output['error'] != 'false') return;

        $form = @$_GET['form'] ;

        if (isset($_POST['form']))
            $form = $_POST['form'];

        $requiredParams = array('id', 'title', 'subtitle_descr', 'subtitle_title', 'appeal_title', 'onsave_descr', 'appeal_text', 'is_active', 'is_appeal_editable', 'title_image', 'url', 'whom', 'post_address', 'address_reply_full', 'meta_description', 'meta_title', 'meta_image', 'html', 'onsave_popuptext', 'attorney_name', 'attorney_destination', 'attorney_birthday', 'petition_city', 'var_arrowcolor', 'youtube_vid', 'author_id');

        foreach ($requiredParams as $_param){
            if (!isset($form[$_param])){

                $form[$_param] = '';

                continue;

            }



            $form[$_param] = trim($form[$_param]);

            if (!in_array($_param, ['subtitle_descr', 'subtitle_title', 'onsave_descr', 'meta_image', 'title_image', 'html', 'onsave_popuptext', 'whom']))
                $form[$_param] = htmlspecialchars($form[$_param], ENT_NOQUOTES);

        }

        if (!isset($form['authors']))
            $form['authors'] = [];

        $form['authors_ids_new'] = [];
        foreach ($form['authors'] as $author_public_info){

            $author = db_authors_list::find_by_id($author_public_info['id']);

            if (empty($author) OR !$author->isEditableBy($_USER->getMainUser())){
                $this->output['error'] = 'ntf author';
                $this->output['error_text'] = 'Не найден автор петиции';
                return ;
            }

            $form['authors_ids_new'][] = $author_public_info['id'];

        }

        if ($form['author_id'] > 0){

            $author = db_authors_list::find_by_id($form['author_id']);

            if (empty($author) OR !$author->isEditableBy($_USER->getMainUser())){
                $this->output['error'] = 'ntf author';
                $this->output['error_text'] = 'Не найден автор петиции';
                return ;
            }

        }

        if ($form['url'] == ''){
            $this->output['error'] = 'ntf url';
            $this->output['error_text'] = 'Не указана ссылка';
            return ;
        }

        /*
        if (mb_strlen($form['appeal_text'], 'UTF-8') > 1700){
            $this->output['error'] = 'ntf url';
            $this->output['error_text'] = 'Слишком много символов — нужно не более 1700';
            return;
        }
        */

        if (preg_match("/[^A-Za-z0-9-_]/", $form['url'])){
            $this->output['error'] = 'wrong url';
            $this->output['error_text'] = 'Только латинские буквы, цифры, нижнее подчёркивание и дефис разрешены в ссылке для страницы с кампанией<br/>Сейчас вы указали: podpishi.org/<b>' .$form['url'] . '</b>'  ;
            return ;
        }

        $bools = ['is_active', 'is_appeal_editable'];

        foreach ($bools as $_param){
            if ($form[$_param] == 'true' OR $form[$_param] == 1){
                $form[$_param] = 1;
            }
            else {
                $form[$_param] = 0;
            }
        }


        //print '<pre>' . print_r($form, true) . '</pre>';

        $campaignPd = $this->prepareCampaignItem($form, $requiredParams);

        if ($this->output['error'] != 'false')
            return ;

        if (!$campaignPd->hasPrevi()){
            $this->output['error'] = 'wrong previ';
            $this->output['error_text'] = 'Недоступная кампания';
            return ;
        }


        $campaignItem = $campaignPd->get();

        // если петиция утверждена, то менять данные про оферту нельзя обычному человеку

        if ($_USER->hasRole('admin') == false AND $_USER->hasRole('coordinator') == false){

            if ($campaignItem->is_confirmed == 1){

                $lockedFields = ['onsave_descr', 'attorney_birthday', 'attorney_name', 'attorney_destination'];

                foreach ($lockedFields as $_field){

                    if ($campaignItem->$_field != $form[$_field]){

                        $this->output['error'] = 'wrong previ';
                        $this->output['error_text'] = 'Петиция была утверджена администрацией и вы не можете менять поля, связанные с юридической офертой (ФИО ответственного и описание возле кнопки "Подписать петицию")';
                        return ;

                    }

                }


            }

        }

        $isUpdatable = false;

        foreach ($requiredParams as $_param) {

            if ($_param == 'id')
                continue ;

            if ($campaignItem->$_param == $form[$_param])
                continue ;

            if ($_param == 'url'){

                $isSameExists = db_campaigns_list::exists([
                    'conditions' => ['url=?', $form['url']],
                ]);

                if ($isSameExists){

                    $this->output['error'] = 'ntf url';
                    $this->output['error_text'] = 'Такой url уже занят';
                    return ;

                }

            }

            if ($_param == 'petition_city'){
                $campaignItem->center_lat = '';
                $campaignItem->center_lng = '';
            }

            $campaignItem->$_param = $form[$_param] ;

            $isUpdatable = true ;

        }

        if ($campaignItem->setAuthors($form['authors_ids_new']))
            $isUpdatable = true;

        if ($campaignItem->domain != 'yabloko')
            if ($campaignItem->center_lat == '')
                if ($campaignItem->setCenterCoordsByCity() == false){
                    $this->output['error'] = 'wrong setCenterCoordsByCity';
                    $this->output['error_text'] = 'Не удалось определить город';
                    return ;
                }
                else {
                    $isUpdatable = true ;
                }

        if ($isUpdatable)
            $campaignItem->save();




        $this->output['campaign'] = [
            'id' => $campaignItem->id,
            'is_confirmed' => $campaignItem->is_confirmed,
        ];

        //print '<pre>' . print_r($newItem, true) . '</pre>';


    }

    public function prepareCampaignItem($form, $requiredParams){
        Global $_USER ;

        if ($form['id'] == '' OR $form['id'] == -1 OR !is_numeric($form['id'])){

            $isSameExists = db_campaigns_list::exists([
                'conditions' => ['url=?', $form['url']],
            ]);

            if ($isSameExists){

                $this->output['error'] = 'ntf url';
                $this->output['error_text'] = 'Такой url уже занят';
                return ;

            }

            $newItem = [
                'owner_id' => $_USER->getOptions('id'),
            ];

            if (!empty($_USER->getMainUser()->getDomains()))
                $newItem['domain'] = $_USER->getMainUser()->getDomains()[0];

            if ($newItem['domain'] == 'yabloko')
                if ($_USER->hasRole('coordinator'))
                    $newItem['is_confirmed'] = 1;


            foreach ($requiredParams as $_param)
                $newItem[$_param] = $form[$_param] ;


            unset($newItem['id']);

            $campaignItem = db_campaigns_list::create($newItem);

            createGdLogEntry('new_campaign', array(
                'tg_bot_status' => 'inqueue',
                'arg_id' => $campaignItem->id,
            ));

            $campaignPd = new pdPetitions($campaignItem->id);


        }
        else {

            $campaignPd = new pdPetitions($form['id']);

            if (!$campaignPd->isExists()) {
                $this->output['error'] = 'ntf campaign';
                $this->output['error_text'] = 'Не найдена кампания';
                return;

            }

        }


        return $campaignPd;

    }


}

$CReg_user_ajax = new CReg_user_ajax;
