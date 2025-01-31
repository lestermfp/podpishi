<?php

class pdPetitions {

    public $campaign = [];

    public function __construct($campaign_id){

        if (is_object($campaign_id)){
            $this->campaign = $campaign_id ;
        }
        else {
            $this->campaign = db_campaigns_list::find_by_id($campaign_id);
        }

    }

    public function get(){

        return $this->campaign ;

    }

    static function loadCampaign($campaign_id){

        return new pdPetitions($campaign_id);

    }

    public function isExists(){

        if (empty($this->campaign))
            return false;

        return true ;

    }

    public function getCampaignId(){

        return $this->get()->id;

    }

    public function hasPrevi(){
        Global $_USER ;

        if (!$this->isExists())
            return false ;

        if ($_USER->hasRole('coordinator')) {

            // also check domains
            if (!empty($_USER->getMainUser()->getDomains()))
                if (!in_array($this->campaign->domain, $_USER->getMainUser()->getDomains()))
                    return false;

            //if (!empty($_USER->getMainUser()->getDomains()))
            //    if ($this->campaign->domain == 'yabloko' AND in_array('yabloko', $_USER->getMainUser()->getDomains()))
            //        return false;


            if ($this->campaign->petition_city == $_USER->getOptions('petition_city'))
                return true;

            if ($this->campaign->owner_id == $_USER->getOptions('id'))
                return true;

        }

        if ($_USER->hasRole('admin'))
            return true;

        if ($this->campaign->owner_id == $_USER->getOptions('id'))
            return true;



        return false ;

    }

    public function getStats(){

        $pskov_region_total = db_appeals_list::count([
            'conditions' => ['destination=? AND region_name="Псковская область"', $this->getCampaignId()],
        ]);

        $pskov_total = db_appeals_list::count([
            'conditions' => ['destination=? AND city="Псков"', $this->getCampaignId()],
        ]);

        $luki_total = db_appeals_list::count([
            'conditions' => ['destination=? AND city="Великие Луки"', $this->getCampaignId()],
        ]);

        $abroad_total = db_appeals_list::count([
            'conditions' => ['destination=? AND region_name="не РФ"', $this->getCampaignId()],
        ]);

        $subregions_pskov = [];

        $pskov_subregions = db_appeals_list::find('all', [
            'select' => 'COUNT(*) as amount, rn_name',
            'conditions' => ['destination=? AND region_name="Псковская область" AND rn_name!="" AND city NOT IN ("Псков", "Великие Луки")', $this->getCampaignId()],
            'order' => 'rn_name ASC',
            'group' => 'rn_name',
        ]);

        foreach ($pskov_subregions as $subregion)
            $subregions_pskov[] = [
                'rn_name' => $subregion->rn_name,
                'amount' => $subregion->amount,
            ];


        $regions = [];
        $regions_sum = 0;

        $russia_regions = db_appeals_list::find('all', [
            'select' => 'COUNT(*) as amount, region_name',
            'conditions' => ['destination=? AND region_name!="Псковская область" AND region_name!="не РФ"', $this->getCampaignId()],
            'order' => 'region_name ASC',
            'group' => 'region_name',
        ]);

        foreach ($russia_regions as $region) {
            $regions[] = [
                'region_name' => $region->region_name,
                'amount' => $region->amount,
            ];

            $regions_sum += $region->amount;

        }


        $output = [
            'amount_total' => self::appealsAmountAt($this->getCampaignId()),
            'pskov_region_total' => $pskov_region_total,
            'city_pskov_total' => $pskov_total,
            'city_luki_total' => $luki_total,
            'abroad_total' => $abroad_total,
            'regions' => $regions,
            'regions_sum' => $regions_sum,
            'subregions_pskov' => $subregions_pskov,
        ];

        return $output ;

    }

    static function appealsAmountAt($campaign_id){

        $amount = db_appeals_list::count([
            'conditions' => ['destination=?', $campaign_id],
        ]);


        return $amount;

    }

    static function formatParagraph($text){

        $tmpText = nl2br($text);

        $tmpText = str_replace("<br />\n<br />", '<br />', $tmpText);
        $tmpText = str_replace("<br />", '</p><p>', $tmpText);

        $tmpText = '<p>' . $tmpText . '</p>';

        return $tmpText;

    }


    /*
     * По умоланию доступны только созданные самим человеком петици
     * Для админа все
     * Для координатора все из его города или созданные им
     */
    static function getListForUser(db_main_users $user){

        $params = [];
        $conditions = ['owner_id=?', $user->id];

        if ($user->role == 'admin')
            $conditions = ['1=1'];

        if ($user->role == 'coordinator'){

            $conditions = ['(petition_city=? OR owner_id=?)', $user->petition_city, $user->id];

            if (!empty($user->getDomains())) {
                $conditions[0] .= ' AND domain IN (?)';
                $conditions[] = $user->getDomains();
            }
        }

        $campaigns = db_campaigns_list::find('all', [
            'order' => 'id DESC',
            'select' => 'id, owner_id, url, title, is_active, is_confirmed, petition_city, domain',
            'conditions' => $conditions,
        ]);

        $petitions = [];

        $usersIds = gdHandlerSkeleton::collectKeys($campaigns, ['owner_id']);

        $users = [];

        if (!empty($usersIds)){

            $users = db_main_users::find('all', [
                'select' => 'id, name, surname',
                'conditions' => ['id IN (?)', $usersIds],
            ]);

            $users = gdHandlerSkeleton::collectKeys($users, ['id'], [
                'mapScope' => ['name', 'surname'],
            ]);

        }

        foreach ($campaigns as $_campaign) {

            $item = $_campaign->to_array();
            $item['abs_url'] = $_campaign->getAbsUrl();

            $item['owner'] = $users[$item['owner_id']];

            $item['people'] = $_campaign->getAppealsAmount();

            $petitions[] = $item;
        }

        return $petitions ;


    }
}

class mdFiles {

    static function inner__saveImageSizeIfRequired(&$attach){
        Global $_CFG ;

        // get image size, if required

        if (in_array($attach->extension, ['png', 'jpg', 'gif']) AND $attach->img_width == ''){

            $attachInfo = self::getAttachInfo($attach);

            $imgInfo = getimagesize($_CFG['root'] . $attachInfo['absLink']);

            $attach->img_width = $imgInfo[0];
            $attach->img_height = $imgInfo[1];
            $attach->save();

            //print '<pre>' . print_r($imgInfo, true) . '</pre>';

        }

    }

    static function getAttachLink($attach){

        self::inner__saveImageSizeIfRequired($attach);

        $attachInfo = self::getAttachInfo($attach);

        return $attachInfo['absLink'];

    }

    static function getAttachInfo($attach, $params = []){

        $link = substr($attach->read_attribute('download_hash_md5'), 0, 10) . '/' . $attach->read_attribute('download_hash_md5') . '.' . $attach->read_attribute('extension') ;

        $details = [];

        if ($attach->img_width != ''){

            $details['cropClass'] = 'crop-height';

            if ($attach->img_width > $attach->img_height)
                $details['cropClass'] = 'crop-width';
        }

        $attachInfo = array_merge($attach->to_array(), array('link' => $link, 'absLink' => '/static/attach/' . $link), $details);

        return $attachInfo;
    }

    static function parseAttachesOutput($attaches_list, $params = []){
        Global $_CFG ;

        $attachList = array();

        foreach ($attaches_list as $_attach){

            self::inner__saveImageSizeIfRequired($_attach);

            $attachInfo = self::getAttachInfo($_attach, $params);

            $attachList[] = $attachInfo ;
        }

        return $attachList ;

    }

    static function removeAttachedFile($attach = false){
        Global $_USER, $_CFG ;

        $output = [];

        if (empty($attach)){
            $output['error'] = 'ntf attach';
            $output['error_text'] = 'Прикреплённый файл не найден';
            return ;
        }

        $attach_id = $attach->read_attribute('attach_id');

        list($attach_dir, $filename) = self::getPathToAttach($attach);

        //print $attach_dir . $filename;

        //exit();
        if (!file_exists($attach_dir . $filename)){
            //$this->output['error'] = 'attach file ntf';
            //$this->output['error_text'] = 'Прикреплённый файл не найден №2';
            //return ;

            $output['deleted_id'] = -1 ;
        }
        else {

            $attach->delete();

            unlink($attach_dir . $filename);

            //remove resized
            $cdirFiles = scandir($attach_dir);
            foreach ($cdirFiles as $_key => $_file) {
                if (in_array($_file, array(".","..")))
                    continue ;

                if (is_dir($attach_dir.$_file)) continue;

                if(strpos(basename($_file),basename($filename)) !== false)
                    unlink($attach_dir.$_file);


            }

            // remove dir if it is empty now
            $files_in_dir = count(scandir($attach_dir));
            if ($files_in_dir == 2) rmdir($attach_dir);

            $output['deleted_id'] = $attach_id ;
        }

    }

    static function getPathToAttach($attach){
        Global $_CFG ;

        $subdir_name = substr($attach->read_attribute('download_hash_md5'), 0, 10);
        $attach_dir = $_CFG['root'] . 'static/attach/' . $subdir_name . '/';

        $filename = $attach->read_attribute('download_hash_md5') . '.' . $attach->read_attribute('extension');

        return array($attach_dir, $filename);
    }

}

function postFile($file_url, $params){

    $tmpfile = $file_url;
    $filename = $params['file_name'];

    // deprecated for 5.6
    $data = array(
        'fileupload' => '@'.$tmpfile.';filename='.$filename,
    );

    // actual for 5.6 PHP
    $data = [
        'fileupload' => new \CurlFile($tmpfile, 'image/jpg', $filename)
    ];

    if (isset($params['debug'])){

        print '<pre>' . print_r($data, true) . '</pre>';
        print '<pre>' . print_r($params, true) . '</pre>';

    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $params['upload_url']);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
    curl_setopt($ch, CURLOPT_HTTPHEADER,array('Content-Type: multipart/form-data'));
    curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
    curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 100);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

    $result = curl_exec ($ch);

    return $result ;

}

class gdCache {


    static function initMemcache(){
        Global $_CFG ;

        if (isset($_CFG['memcache']))
            return ;

        $_CFG['memcache'] = new Memcache;
        $_CFG['memcache']->connect('127.0.0.1', 11211) or die ("Could not connect to main five server");

    }

    static function closure($cached_key, $closure_or_value, $time = 60){

        $cached_content = gdCache::get($cached_key);

        if ($cached_content !== false)
            return $cached_content ;

        if (is_callable($closure_or_value))
            $cached_content = $closure_or_value();

        if (!is_callable($closure_or_value))
            $cached_content = $closure_or_value();

        gdCache::put($cached_key, $cached_content, $time);

        return $cached_content;
    }

    static function put($cached_key, $value, $time = 60){
        Global $_CFG ;

        gdCache::initMemcache();


        $cached_result = $_CFG['memcache']->set($cached_key, $value, MEMCACHE_COMPRESSED, $time);

        if (isset($_GET['standartOil'])){
            //print 'Set up cache ' . $cached_key ;
            //var_dump($cached_result);
        }

    }

    static function get($cached_key, $format = 'string'){
        Global $_CFG ;

        gdCache::initMemcache();

        $cache_result = $_CFG['memcache']->get($cached_key);

        if (isset($_GET['standartOil'])){
            //print 'Desired ' . $cached_key ;
            //var_dump($cache_result);
        }

        if ($cache_result === false)
            return false ;


        if ($format == 'string'){
            return $cache_result ;
        }

        if ($format == 'json'){

            $cache_result = json_decode($cache_result, true);

            if (!is_array($cache_result))
                return false ;

            return $cache_result;

        }

        if ($format == 'serialized'){

            $cache_result = unserialize($cache_result);

            if (!is_array($cache_result))
                return false ;

            return $cache_result;

        }

    }

    static function remove($cached_key){
        Global $_CFG ;

        gdCache::initMemcache();

        return $_CFG['memcache']->delete($cached_key);


    }
}


class gdUserTask {

    static function isFinalAdvancewithChosed($user_id, $desired_advancewith = '', $params = array()){

        /*
            Проверяем выдвигается ли этот человек от Яблока
        */

        $cache_key = 'advwith_' . $user_id . $desired_advancewith ;
        $cached = gdCache::get($cache_key);

        if (!in_array('nocache', $params))
            if ($cached !== false)
                if ($cached == 'b:1;'){
                    return true ;
                }
                else {
                    return false ;
                }




        $conditions = array('created_account=? AND advancewith_final="N/A"', $user_id);

        if ($desired_advancewith != '')
            $conditions = array('created_account=? AND advancewith_final=?', $user_id, $desired_advancewith);


        $hasAdvancewith = db_regions_deputy::exists(array(
            'conditions' => $conditions,
        ));

        $returnValue = $hasAdvancewith ;

        gdCache::put($cache_key, serialize($returnValue), 60 * 1);

        return $returnValue ;

    }

    static function isChosedYabloko($user_id){

        /*
            Проверяем выдвигается ли этот человек от Яблока
        */

        $finished_task2_yabloko = db_extra_variables::exists(array(
            'conditions' => array('name=? AND connected_to=? AND value_large!="[]" AND (value_large LIKE "%yabloko\":\"5\"%" OR value_large LIKE "%yabloko\":\"4\"%")', 'task2Data', $user_id),
        ));

        if (!$finished_task2_yabloko)
            return false;

        return true ;
    }

    static function isVisitedTraining($user_id, $event_type, $params = array()){


        if ($user_id == 1143 AND $event_type == 'training')
            return true ;

        /*
            Проверяем прошёл ли человек тренинг
        */

        $cache_key = 'visited_' . $user_id . $event_type ;
        $cached = gdCache::get($cache_key);


        if (!in_array('nocache', $params))
            if ($cached !== false)
                if ($cached == 'b:1;'){
                    return true ;
                }
                else {
                    return false ;
                }

        $joiningFound = db_extra_variables::exists(array(
            'conditions' => array('name=? AND connected_to=? AND value LIKE "%visited_training;%"', 'task_' . $event_type . '_join', $user_id),
        ));

        $returnValue = $joiningFound ;

        gdCache::put($cache_key, serialize($returnValue), 60 * 1);

        return $returnValue ;

    }

    static function isQuenedTraining($user_id, $event_type){


        if ($user_id == 1143 AND $event_type == 'training')
            return true ;

        /*
            Проверяем записан ли человек на тренинг
        */

        $joiningFound = db_extra_variables::exists(array(
            'conditions' => array('name=? AND connected_to=? AND value_timestamp>NOW()', 'task_' . $event_type . '_join', $user_id),
        ));

        return $joiningFound;

        if (!$joiningFound)
            return false;

        return true ;
    }

}

class gdTemplate {

    public function __construct($name, $parentVars = []){
        Global $_CFG ;

        $this->root = $_CFG['root'];
        $this->name = $name ;

        return $this->open($name, $parentVars);

    }
    public function open($name, $parentVars = []){
        Global $_CFG ;

        $tpl_path = $_CFG['root'] . 'modules/templates/';
        ob_start ();

        $path = $this->root . 'modules/templates/' . $name . '.php';

        include($path);

        $this->content = ob_get_contents();
        ob_end_clean();


    }

    public function draw(){
        return $this->content ;
    }

    public function getTemplateConditionsChunks(&$html){

        $listBlocks = array();

        $offset = 0;

        $prev = array();

        $limit = 0;
        while (true){

            $startedWithOffset = $offset  ;

            $position_start = strpos($html, '<!--[!template=if:', $offset);

            //print 'position_start ' . $position_start . '<br/>';

            if ($position_start === false)
                break ;



            $offset = $position_start ;

            $position_start_c = strpos($html, ']-->', $offset) + 4;

            $offset = $position_start_c;


            $position_end_c = strpos($html, '<!--/[!template=if:', $offset);
            $offset = $position_end_c;

            $position_end = strpos($html, ']-->', $offset) + 4;

            $offset = $position_end;

            //print 'position_end ' . $position_end . '<br/>';

            $content = substr($html, $position_start, $position_end - $position_start);

            if ($startedWithOffset == 0){
                $listBlocks[] = substr($html, $startedWithOffset, $position_start);
            }


            //if ($startedWithOffset != 0)
            //if ($startedWithOffset == $previousWithOffset)





            if ($startedWithOffset != 0){
                //print 'From end ' . $prev['end'] . '==' . $position_start;
                $listBlocks[] = substr($html, $prev['end'], $position_start - $prev['end']);
            }

            $listBlocks[] = array(
                'start' => $position_start,
                'start_c' => $position_start_c,
                'end' => $position_end,
                'end_c' => $position_end_c,
                'content' => $content,
            );

            $prev = array(
                'start' => $position_start,
                'start_c' => $position_start_c,
                'end' => $position_end,
                'end_c' => $position_end_c,
            );

            if ($limit > 10)
                break ;

            $limit++;
        }

        $listBlocks[] = substr($html, $startedWithOffset);

        return $listBlocks ;
    }

    public function executeTemplateConditions($params, &$listBlocks){

        foreach ($listBlocks as $_key => $_block){

            if (!is_array($_block))
                continue ;

            $condition = str_replace('<!--[!template=if:', '', $_block['content']);


            $condition = explode(']-->', $condition);

            $condition = $condition[0];

            if (strpos($condition, '==') !== false){
                list ($c_var, $c_value) = explode('==', $condition);


                // condition failed
                if ($params[$c_var] != $c_value){
                    // cut it

                    unset($listBlocks[$_key]);

                    //$lengthChanged = $lengthChanged - strlen($html) ;
                    //print 'failed';

                }
                // condition success
                else {
                    //print 'success '  ;

                    $listBlocks[$_key]['content'] = substr($_block['content'], $_block['start_c'] - $_block['start'], $_block['end_c'] - $_block['start_c']);

                }
            }

            if (strpos($condition, '!=') !== false){
                list ($c_var, $c_value) = explode('!=', $condition);

                $c_value = explode(',', $c_value);


                // succcess by default
                $is_failed = false ;

                foreach ($c_value as $_c_key => $_c_sub) {
                    // condition failed
                    if ($params[$c_var] == $_c_sub) {
                        // cut it

                        unset($listBlocks[$_key]);

                        $is_failed = true ;

                    }

                }

                if ($is_failed == false)
                    $listBlocks[$_key]['content'] = substr($_block['content'], $_block['start_c'] - $_block['start'], $_block['end_c'] - $_block['start_c']);
            }



        }

    }

    public function processTemplateConditions($params, $html = ''){

        /*
            Вырезаем все условия
        */

        if ($html == '')
            $html = $this->content ;


        $listBlocks = $this->getTemplateConditionsChunks($html);

        $this->executeTemplateConditions($params, $listBlocks);


        // Соединяем страницу со списка частей в единое целое

        $html_new = '';

        $offset = 0;

        foreach ($listBlocks as $_block){
            if (is_array($_block)){
                $html_new .= $_block['content'];

                $offset = $_block['start_c'];
            }
            else {
                $html_new .= $_block ;
            }
        }

        //print $html ;

        //print '<pre>' . print_r($listBlocks, true) . '</pre>';

        //exit();

        return $html_new ;

    }

    private function replaceInTemplate($name, $value){

        $this->content = str_replace($name, $value, $this->content);
    }

    public function set($params, $extra = array()){

        $this->content = $this->processTemplateConditions($params);

        if (is_array($params)){

            foreach ($params as $_key => $_value)
                if ($_key[0] == '{')
                    $this->replaceInTemplate($_key, $_value);
        }
        else {
            $this->replaceInTemplate($params, $extra);
        }

    }
}

class gdPanel {

    static function flushMetaFor($url){

        $cache_key = 'meta4_' . $url ;

        gdCache::remove($cache_key);
    }

    static function getMetaFor($url){

        if ($url == 'against')
            $url = 'index';


        $cache_key = 'meta4_' . $url ;

        $cached = gdCache::get($cache_key);

        if ($cached !== false) {

            return json_decode($cached, true);

        }
        else {

            $metaInfo = db_meta_tags::find_by_inner_url($url);

            $details = '[]';

            if (!empty($metaInfo)) {
                gdCache::put($cache_key, $metaInfo->details, 60 * 5);
                $details = $metaInfo->details ;
            }
        }

        return json_decode($details, true);

    }

    static function getStringLength($text){


        return strlen(iconv('utf-8', 'windows-1251', $text));

    }

    static function forceTypograph($text, $indesign_mode = false){

        Global $_CFG ;

        include_once($_CFG['root'] . "classes/EMT_typograph.php");

        $typographed = EMTypograph::fast_apply($text);

        //$typographed = str_replace('&nbsp;', ' ', $typographed);


        $typographed = str_replace(array('<p>', '</p>'), '', $typographed);

        if ($indesign_mode){
            $typographed = html_entity_decode($typographed);
            $typographed = str_replace('<br />', "", $typographed);
        }
        //$typographed = str_replace('&nbsp;', ' ', $typographed);

        return $typographed ;
    }



    static function inner__removeAttachFromSbmachine($external_attach_hash, $refresh_key = ''){
        Global $_CFG ;

        $url = 'https://.ru/ajax/ajax_ext_attach.php?context=dmachine__attempToRemove&external_attach_hash=%s&action_signature=%s&refresh_key=' . $refresh_key;

        $url = sprintf($url, $external_attach_hash, md5($external_attach_hash . $_CFG['dmachine_flkey']));

        $output = file_get_contents($url);

        $output = @json_decode($output, true);

        if (!is_array($output)){
            $output = array(
                'error' => 'impossible',
                'error_text' => 'unable to parse json',
            );
        }

        return $output ;
    }

    static function utfToLower($text){

        return (mb_convert_case($text, MB_CASE_LOWER, 'utf-8'));

    }

    static function sendToTelegram($telegram_id, $question_reply, $from_site_id = -1, $params = array()){

        $status = ['error' => 'false'];

        $question_reply = str_replace(['<br>', '<br/>'], '', $question_reply);

        $encoded_message = base64_encode($question_reply);

        $messageAdded = db_massbot_messages::find_by_message($encoded_message, array(
            'select' => 'message_id',
        ));

        if (!empty($messageAdded)){

        }
        else {

            $newMessage = array(
                'message' => $encoded_message,
                'from_site_id' => $from_site_id,
                'date' => 'now',
            );

            $messageAdded = db_massbot_messages::create($newMessage);
            if (null === $messageAdded) {
                $status['error'] = true;
                $status['error_text'] = 'не удалось создать сообщение в телеграм';
                return $status;
            }

        }

        $newDeliver = array(
            'message_id' => $messageAdded->read_attribute('message_id'),
            'chat_id' => $telegram_id,
            'status' => 'inqueue',
            'date' => 'now',
        );


        if (isset($params['priority']))
            $newDeliver['priority'] = $params['priority'] ;



        $deliver = db_massbot_deliver::create($newDeliver);

        if (null === $deliver) {
            $status['error'] = true;
            $status['error_text'] = 'не удалось отправить созданное сообщение в телеграм';
            return $status;
        }

        return $status;
    }


    static function addSocialDomain($social_type, $social){

        if ($social == '')
            return $social ;

        $social_type = str_replace('social_', '', $social_type);

        $socialsPattern = array(
            'tw' => array(
                'required' => 'twitter.com',
                'tpl' => 'https://twitter.com/%s',
            ),
            'fb' => array(
                'required' => 'facebook.com',
                'tpl' => 'https://facebook.com/%s',
            ),
            'vk' => array(
                'required' => 'vk.com',
                'tpl' => 'https://vk.com/%s',
            ),
            'lj' => array(
                'required' => 'twitter.com',
                'tpl' => 'https://%s.livejournal.com',
            ),
        );

        if (!isset($socialsPattern[$social_type]))
            return $social ;

        $pattern =  $socialsPattern[$social_type];

        if (strpos($social, $pattern['required']) === false)
            $social = sprintf($pattern['tpl'], $social);


        return $social ;
    }

}


function mainTimer($action){
    Global $_CFG ;

    if ($action == 'set'){
        $_CFG['mainTimer'] = microtime(true);
    }
    else if ($action == 'get'){
        return microtime(true) - $_CFG['mainTimer'];
    }

}


function createGdLogEntry($type, $params, $extraParams = array()){
    Global $_USER ;

    $user_id = '';
    if (isset($params['user_id'])){
        $user_id = $params['user_id'];
        unset($params['user_id']);
    }
    else if (is_authed()){
        $user_id = $_USER->getOptions('id');
    }

    $tg_bot_status = 'none';
    if (isset($params['tg_bot_status'])){
        $tg_bot_status = $params['tg_bot_status'] ;
        unset($params['tg_bot_status']);
    }

    $newEntry = array(
        'type' => $type,
        'user_id' => $user_id,
        'tg_bot_status' => $tg_bot_status,
        'details' => serialize($params),
    );

    if (isset($params['arg_id']))
        $newEntry['arg_id'] = $params['arg_id'];

    if (isset($params['subarg_id']))
        $newEntry['subarg_id'] = $params['subarg_id'];

    if (isset($params['details2'])) {
        $newEntry['details2'] = $params['details2'];
    }

    if (isset($extraParams['targetTable']) AND $extraParams['targetTable'] == 'stats'){
        db_main_activityLog_stats::create($newEntry);
    }
    else {
        db_main_activityLog::create($newEntry);
    }

}


class classFactory {

    static function c_add($target, $key, $delimiter = ';'){

        if (classFactory::c_isset($target, $key) == false)
            $target = implode($delimiter, array_merge(array($key), explode($delimiter, $target)));

        return $target ;
    }

    static function c_remove($target, $key, $delimiter = ';'){

        $target = str_replace(array($key . $delimiter), '', $target);

        return $target ;
    }

    static function c_isset($target, $key, $delimiter = ';'){
        return in_array($key, explode($delimiter, $target));
    }

}


/*
	Universal handler for ajax classes
*/

class gdHandlerSkeleton {

    public $timeTypes = array(
        'created_at' => 'd.m', 'deadline_at' => 'd.m', 'reg_date' => 'd.m', 'date' => 'd.m в H:i',
    );

    static function downloadFile($path, $filename){

        $contentDisposition = 'filename*=UTF-8\'\'' . rawurlencode($filename);

        header('Content-Description: File Transfer');
//header('Content-Type: application/pdf');
        header ('Content-Type: ' . mime_content_type($path));
        header('Content-Disposition: attachment; ' . $contentDisposition);
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($path));
        readfile($path);

        exit();
    }

    static function post_query($url, $params, $header = array()){

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);

        if (!empty($params)) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        }

        // debug purpose
        //curl_setopt($ch, CURLOPT_HEADER, 1);
        //curl_setopt($ch, CURLINFO_HEADER_OUT, true);

        if (!empty($header)){
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $server_output = curl_exec($ch);

        return $server_output ;

    }

    static function printMetaTags($module_name){
        Global $_CFG ;

        $_meta_name = 'default';
        if (isset($_CFG['meta'][$module_name]))
            $_meta_name = $module_name ;

        $forceCustomMeta = false ;
        if (isset($_GET['parts'][1]) AND $_GET['parts'][1] == 'against' AND isset($_GET['parts'][2]) AND $_GET['parts'][2] != ''){

            $sharingText = beautyAgainstOG::getFromCollection($_GET['parts'][2]);

            if ($sharingText != '')
                $forceCustomMeta = true ;
        }

        $meta_string = '';

        foreach ($_CFG['meta'][$_meta_name] as $_key => $_value) {

            if (in_array($_key, ['page_title', 'property=']))
                continue ;

            if ($forceCustomMeta) {
                if (in_array($_key, ['name="twitter:image:src"', 'property="og:image"']))
                    $_value = 'https://' . $_CFG['domain'] . '/ajax/ajax_opengraph.php?context=get__indexImage&onimg_id=' . $_GET['parts'][2] . '.png';

                if (in_array($_key, ['property="og:url"']))
                    $_value = 'https://' . $_CFG['domain'] . '/against/' . $_GET['parts'][2] ;
            }

            $meta_string .= '		<meta ' . $_key . ' content="' . $_value . '" />' . "\r\n";
        }

        return $meta_string;
    }

    static function rus2translit($string) {
        $converter = array(
            'а' => 'a',   'б' => 'b',   'в' => 'v',
            'г' => 'g',   'д' => 'd',   'е' => 'e',
            'ё' => 'e',   'ж' => 'zh',  'з' => 'z',
            'и' => 'i',   'й' => 'y',   'к' => 'k',
            'л' => 'l',   'м' => 'm',   'н' => 'n',
            'о' => 'o',   'п' => 'p',   'р' => 'r',
            'с' => 's',   'т' => 't',   'у' => 'u',
            'ф' => 'f',   'х' => 'h',   'ц' => 'c',
            'ч' => 'ch',  'ш' => 'sh',  'щ' => 'sch',
            'ь' => '\'',  'ы' => 'y',   'ъ' => '\'',
            'э' => 'e',   'ю' => 'yu',  'я' => 'ya',

            'А' => 'A',   'Б' => 'B',   'В' => 'V',
            'Г' => 'G',   'Д' => 'D',   'Е' => 'E',
            'Ё' => 'E',   'Ж' => 'Zh',  'З' => 'Z',
            'И' => 'I',   'Й' => 'Y',   'К' => 'K',
            'Л' => 'L',   'М' => 'M',   'Н' => 'N',
            'О' => 'O',   'П' => 'P',   'Р' => 'R',
            'С' => 'S',   'Т' => 'T',   'У' => 'U',
            'Ф' => 'F',   'Х' => 'H',   'Ц' => 'C',
            'Ч' => 'Ch',  'Ш' => 'Sh',  'Щ' => 'Sch',
            'Ь' => '\'',  'Ы' => 'Y',   'Ъ' => '\'',
            'Э' => 'E',   'Ю' => 'Yu',  'Я' => 'Ya',
        );
        return strtr($string, $converter);
    }

    static function str2url($str) {
        // переводим в транслит
        $str = self::rus2translit($str);
        // в нижний регистр
        $str = strtolower($str);
        // заменям все ненужное нам на "-"
        $str = preg_replace('~[^-a-z0-9_]+~u', '-', $str);
        // удаляем начальные и конечные '-'
        $str = trim($str, "-");
        return $str;
    }

    static function getOrmVars($_option_name, $target){

        //Несколько параметров
        if (strpos($_option_name,',') != false){
            $params = explode(',', str_replace(' ', '', $_option_name));

            $out_array = array();
            foreach ($params as $_param){
                $out_array[$_param] = $target->$_param ;
            }

            return $out_array ;
        }
        //Один параметр
        else {
            return $target->$_option_name ;
        }

    }

    public function syncValuesInORMObject($key, $value, &$object){

        if ($object->__isset($key) == true AND $object->read_attribute($key) != $value){
            $object->$key = $value ;

        }
    }

    public function recursiveFind(array $array, $needle)
    {
        $iterator  = new RecursiveArrayIterator($array);
        $recursive = new RecursiveIteratorIterator(
            $iterator,
            RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($recursive as $key => $value) {
            if ($key === $needle) {
                return $value;
            }
        }
    }

    static function get__csvWithCache($url = '', $ingoreCache = false){

        if ($ingoreCache)
            return $csv = gdHandlerSkeleton::getWithHeaderByUrl($url);

        $cached_key = md5($url) . 2;

        $_CFG['memcache'] = new Memcache;
        $_CFG['memcache']->connect('127.0.0.1', 11211) or die ("Could not connect to main five server");

        $cachedContent = $_CFG['memcache']->get($cached_key);

        if ($cachedContent !== false){
            $csv = unserialize($cachedContent);
        }
        else {

            $csv = gdHandlerSkeleton::getWithHeaderByUrl($url);

            $_CFG['memcache']->set($cached_key, serialize($csv), MEMCACHE_COMPRESSED, 60 * 5);

        }



        return $csv;

    }


    static function makeAuthLog($user){

        $authLog = db_authLogs::create(array(
            'user_id' =>  $user->read_attribute('id'),
            'ip' => GetRealIp(),
            'log_time' => 'now',
            'user_agent' => htmlspecialchars($_SERVER['HTTP_USER_AGENT']),
            'country' => '-1',
            'is_dangerous' => gdHandlerSkeleton::isLoginDangerous($user, GetRealIp()),
        ));

        return $authLog ;

    }

    static function isLoginDangerous($user, $ip){
        $count = db_authLogs::count([
            'conditions' => array(
                'user_id=? AND ip=?', $user->read_attribute('id'), $ip
            ),
        ]);

        return !($count > 0);
    }


    static function getTemplateFor($page_name, $params = array()){

        $select = 'content_name, content';
        if (in_array('allInfo', $params))
            $select = '*';

        $_page_content = db_pages_content::find('all', array(
            'conditions' => array('page_name=?', $page_name),
            'select' => $select,
        ));

        $_TEMPLATE = array();

        if (in_array('allInfo', $params)){
            foreach ($_page_content as $_element)
                $_TEMPLATE[$_element->read_attribute('content_name')] = $_element->to_array();

        }
        else {
            foreach ($_page_content as $_element)
                $_TEMPLATE[$_element->read_attribute('content_name')] = $_element->read_attribute('content');

            if (in_array('nl2bbbbr', $params))
                foreach ($_TEMPLATE as $_key => $_element)
                    $_TEMPLATE[$_key] = nl2br($_element) ;

        }

        return $_TEMPLATE ;
    }

    static function generateSimpleHtmlTable ($array){
        $filename = 'exportTable';
        if(isset($_GET['context'])) {
            $filename = $_GET['context'];
        }

        $use_datatables = '';
        if(isset($_GET['sortable'])) {
            $use_datatables = 'initDataTables(".table-datatable");';
        }


        $html = '<meta content="text/html; charset=utf-8" http-equiv="Content-Type">


    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/css/bootstrap.min.css" integrity="sha384-PsH8R72JQ3SOdhVi3uxftmaW6Vc51MKb0q5P2rRUpPvrszuE4W1povHYgTpBfshb" crossorigin="anonymous">
    
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
    
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">

    
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs4/dt-1.10.16/fh-3.1.3/r-2.2.1/sc-1.4.4/datatables.min.css"/>
    

    <script type="text/javascript" src="https://cdn.datatables.net/v/bs4/dt-1.10.16/fh-3.1.3/r-2.2.1/sc-1.4.4/datatables.min.js"></script>


<script src="/static/js/table2xls.js"></script>
<script>
function dblclick() {
    tableToExcel(\'exportTable\', \'Лист 1\', \''.$filename.'\'); 
}

$().ready(function(){
        '.$use_datatables.'
    });

    function initDataTables(placeholder) {
        
        if($(placeholder).length){
            $(placeholder).each(function(){
                $(this).DataTable({
                    fixedHeader: true,
                    bPaginate:false,
                    "order": [],
                    "language": {
                        "url": "//cdn.datatables.net/plug-ins/1.10.16/i18n/Russian.json"
                    },
                    dom:\'<"table-wrapper"<t>>\',

                });
            });
        }
    }
</script>



<style>
    body {
        padding-left: 10px;
    }

    table tr:hover {
        background: gainsboro;
    }

    table {
        border-spacing: 0;
        border-collapse: collapse;
        overflow: hidden;
        z-index: 1;
    }

    td, th {
        cursor: pointer;
        padding: 5px;
        position: relative;
    }

    td:hover::after {
        background-color: #ffa;
        content: \'\00a0\';
        height: 10000px;
        left: 0;
        position: absolute;
        top: -5000px;
        width: 100%;
        z-index: -1;
    }
</style>
<a href="#" onclick="dblclick();return false;">excel</a> | <a href="'.$_SERVER['REQUEST_URI'].'&sortable">sortable</a>

<table border="1" id="exportTable" class="table-datatable">';

        foreach ($array as $_key => $_item){


            if ($_key == 0){
                $html .= '<thead><tr>';

                foreach ($_item as $_name => $_value)
                    $html .= '<th >' . $_name . '</th>';

                $html .= '</thead><tbody>';
            }

            $html .= '<tr>';
            foreach ($_item as $_name => $_value)
                $html .= '<td>' . $_value . '</td>';

            $html .= '</tr>';

            //if ($_key == 0)
            //	$html .= '</tbody>';

        }

        $html .= '</tbody></table>';


        return $html ;
    }

    static function findInArrayByValue_($key, $value, $target){

        foreach ($target as $_target){
            if (is_object($_target)){
                if ($_target->__isset($key) AND $_target->read_attribute($key) == $value)
                    return $_target ;
            }
            else {
                if (isset($_target[$key]) AND $_target[$key] == $value)
                    return $_target ;
            }
        }

        return false;
    }

    public static function getHumanMonth($unixtime, $params = array()){

        $monthsList = array('', 'январь', 'февраль', 'март', 'апрель', 'май', 'июнь', 'июль', 'август', 'сентябрь', 'октябрь', 'ноябрь', 'декабрь');

        return $monthsList[(int) date('m', $unixtime)];


    }

    public static function getHumanDate($unixtime, $params = array()){

        $monthsList = array('', 'января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря');

        if (in_array('short_months', $params))
            $monthsList = array("", "Янв", "Фев", "Мар", "Апр", "Май", "Июн", "Июл", "Авг", "Сен", "Окт", "Ноя", "Дек");

        if (in_array('month_only', $params))
            return $monthsList[(int) date('m', $unixtime)];

        if (in_array('comment', $params)){
            return date('j ' . $monthsList[(int) date('m', $unixtime)] . ', H:i', $unixtime);
        }
        else {
            return date('j ' . $monthsList[(int) date('m', $unixtime)], $unixtime);
        }

    }

    public function mksurvey_query($destUrl){
        Global $_CFG ;

        $headers = array(
            'Authorization: ' . $_CFG['mksurvey_token'],
            'Accept: application/json',
        );

        $ch = curl_init();

        curl_setopt($ch,CURLOPT_URL,$destUrl);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch,CURLOPT_HEADER,true);
        curl_setopt($ch,CURLINFO_HEADER_OUT,true);
        curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
        $result=curl_exec($ch);

        $output = array();
        list($output['header'], $output['result']) = explode("\r\n\r\n", $result, 2);

        return $output ;
    }

    static function collectKeys($target, $keys, $params = array()){

        $collection = array();

        if (isset($params['mapScope'])){

            foreach ($target as $_target){

                if (is_object($_target)){
                    foreach ($keys as $_key)
                        foreach ($params['mapScope'] as $_param => $_p_value)
                            $collection[$_target->read_attribute($_key)][$_p_value] = $_target->read_attribute($_p_value)  ;
                }
                else {
                    foreach ($keys as $_key)
                        foreach ($params['mapScope'] as $_param => $_p_value)
                            $collection[$_target[$_key]][$_p_value] = $_target[$_p_value] ;
                }
            }

        }
        else {
            foreach ($target as $_target){

                if (is_object($_target)){
                    foreach ($keys as $_key)
                        $collection[] = $_target->read_attribute($_key);
                }
                else {
                    foreach ($keys as $_key)
                        $collection[] = $_target[$_key];
                }
            }

            if (in_array('unique', $params))
                $collection = array_unique($collection);

            if (in_array('no_zero', $params))
                foreach ($collection as $_key => $_value)
                    if ($_value == 0)
                        unset($collection[$_key]);

        }

        return $collection ;
    }

    public function saveArrayAsCSV_($filename, $target, $params = array()){

        //print '<pre>' . print_r($target, true) . '</pre>';

        $header = '';
        $result = '';

        $i = 0;
        foreach ($target as $_value){

            foreach ($_value as $_key => $_subvalue){
                //header
                if ($i == 0){
                    $header .= $_key . ',';
                }

                $result .= $_subvalue . ',';

            }

            if (count($_value) == 1)
                $result = substr($result, 0, -1);

            $result = rtrim($result, ',');

            $result .= "\r\n";

            $i++ ;
        }

        //$result = str_replace('<br/>', chr(10), $result);

        $extension = '.csv';
        if (in_array('extension', $params))
            $extension = $params['extension'] ;

        // отдаём файл на скачивание
        header('Content-type: application/zip');
        header('Content-Disposition: attachment; filename="'.$filename.$extension.'"');

        $header = $header . "\r\n";

        if (in_array('no_header', $params))
            $header = '';

        print iconv('utf8', 'windows-1251//IGNORE', $header . $result) ;

        exit();
    }

    static function getWithHeaderByUrl($file_url){

        $csv = gdHandlerSkeleton::getCsvByUrl_($file_url);

        if (is_bool($csv))
            return false ;

        $csvFine = array();

        $headerCsv = $csv[0];

        foreach ($headerCsv as $_key => $_value)
            $headerCsv[$_key] = mb_convert_case($_value, MB_CASE_LOWER, 'utf-8');

        unset($csv[0]);

        foreach ($csv as $_line){


            $newLine = array();

            foreach ($_line as $_key => $_param){

                $name = $headerCsv[$_key];

                $newLine[$name] = $_param ;

            }


            $csvFine[] = $newLine ;

        }

        return $csvFine ;

    }

    static function getCsvByUrl_($file_url){

        $content = explode("\r\n", file_get_contents($file_url));

        $csv = array_map('str_getcsv', $content);

        if (!is_array($csv)){
            return false;
        }

        return $csv ;
    }


    public function translateUnixInArray($target, $timeTypes){
        Global $_CFG ;

        foreach ($timeTypes as $_key => $_mask){
            if (isset($target[$_key])) $target[$_key] = date($_mask, $target[$_key] + $_CFG['time_diff']);
        }

        return $target ;
    }

    public function checkAuthLevel($required_role, $req_extra_class = array()){
        Global $_USER ;

        if (!is_array($required_role)) $required_role = array($required_role);

        if (!is_authed() OR !in_array($_USER->getOptions('role'), $required_role)){
            $this->output['error'] = 'is_authed';
            $this->output['error_text'] = 'Для выполнения данного действия необходимо авторизоваться';
            return ;
        }

        if (!empty($req_extra_class)){

            foreach ($req_extra_class as $_extra_class)
                if (classFactory::c_isset($_USER->getOptions('extra_class'), $_extra_class) == false){
                    $this->output['error'] = 'is_authed extra_class';
                    $this->output['error_text'] = 'Для выполнения данного действия необходимо авторизоваться';
                    return ;
                }

        }

    }

    public function hasExtraClass($req_extra_class = array(), $result_as_boolean = false){
        Global $_USER ;

        if (!is_authed())
            return ;

        if (!empty($req_extra_class)){

            foreach ($req_extra_class as $_extra_class)
                if (classFactory::c_isset($_USER->getOptions('extra_class'), $_extra_class) == true){

                    if ($result_as_boolean)
                        return true ;

                    $this->output['error'] = 'extra class blocked by';
                    $this->output['error_text'] = 'Для выполнения данного действия необходимы специальные права';
                    return ;
                }

        }

        return false ;

    }

    public function sksort(&$array, $subkey="id", $sort_ascending=false) {

        if (count($array))
            $temp_array[key($array)] = array_shift($array);

        foreach($array as $key => $val){
            $offset = 0;
            $found = false;
            foreach($temp_array as $tmp_key => $tmp_val)
            {
                if(!$found and strtolower($val[$subkey]) > strtolower($tmp_val[$subkey]))
                {
                    $temp_array = array_merge(    (array)array_slice($temp_array,0,$offset),
                        array($key => $val),
                        array_slice($temp_array,$offset)
                    );
                    $found = true;
                }
                $offset++;
            }
            if(!$found) $temp_array = array_merge($temp_array, array($key => $val));
        }

        if ($sort_ascending) $array = array_reverse($temp_array);

        else $array = $temp_array;
    }

}

function addEmailToQueue($newMail, $force = false){
    Global $_CFG ;

    if ($force == false) {
        $isExists = db_mailQueue::exists(array(
            'conditions' => array('receiver=? AND type=?', $newMail['receiver'], $newMail['type']),
        ));


        if ($isExists)
            return false;
    }

    if (isset($newMail['lk_activation_hash'])) {

        $newMail['text'] = str_replace('{unsubscribe_link}', 'https://' . $_CFG['domain'] . '/unsubscribe/' . $newMail['user_id'] . '/' . $newMail['lk_activation_hash'] . '/' . $newMail['type'], $newMail['text']);
        unset($newMail['lk_activation_hash']);

    }


    // it is for tracking
    $newMail['hook_arg'] = md5($newMail['type'] . $newMail['receiver'] . time() . mt_rand(1,100));

    $createdMail = db_mailQueue::create($newMail);

    /*

        $hookParams = array(
            'campaign_name' => $createdMail->type,
            'hook_arg' => $createdMail->hook_arg,
            'connected_to' => $createdMail->id,
            'status' => 'wait',
        );

        $hookObject = db_mailhook_list::create($hookParams);

    */

    return $createdMail ;
}


/*
	Берёт ссылку на соц. сеть и сокращает её (http://vk.com/durov => durov)
*/
function normalizeSocial($name){

    $name = str_replace('http://', 'https://', $name);
    if (strpos($name, 'https://') === false)
        $name = 'https://' . $name ;

    $parts = parse_url($name);

    $_new = $parts['host'];
    if (isset($parts['path']))
        $_new = substr($parts['path'], 1);

    if (isset($parts['query']))
        $_new .= '?' . $parts['query'];

    if (is_null($_new)) $_new = '';

    return $_new ;
}

/*
	Берёт номер длиной 11 символов и заменяет код на 7 вначале
*/
function parseAndformatPhone($phone){

    $phone = preg_replace('~\D+~', '', $phone);

    if (isset($phone[0]) AND $phone[0] == 8) $phone[0] = 7 ;

    return $phone ;
}
function formatPhone($phone) {
    $phone = preg_replace(
        "/([0-9a-zA-Z]{1})([0-9a-zA-Z]{3})([0-9a-zA-Z]{3})([0-9a-zA-Z]{2})([0-9a-zA-Z]{2})/",
        "+$1 $2 $3-$4-$5",
        $phone
    );
    return $phone;
}
function validatePhone($phone) {
    if (!preg_match("/^[0-9]{10,15}+$/", $phone)) {
        return false;
    }
    return true;
}

function validateDate($format, $date, $normalize = false){

    if ($format == 'd.m.Y'){

        $parts = explode(".", $date);

        if (count($parts) != 3)
            return false ;

        if (strlen($parts[2]) != 4)
            return false ;

        if (strlen($parts[1] . $parts[0]) != 4)
            return false;

        $normalized_date = $parts[2] . '-' . $parts[1] . '-' . $parts[0] ;

        if ($normalize)
            return $normalized_date ;

    }
    else {
        return false ;
    }

    return true ;
}

function normalizeDate($format, $date){

    $normalized_date = $date ;

    if ($format == 'd.m.Y') {

        $parts = explode(".", $date);

        $normalized_date = $parts[2] . '-' . $parts[1] . '-' . $parts[0];

    }

    return $normalized_date ;
}

function validateEmail($email) {

    if (strpos($email, '@') === false)
        return false ;

    return true;
}

function mergeORMArrayByKey($connector_key, $target_to, $target_from){

    $output = array();

    foreach ($target_to as $_key => $_to){

        if (is_array($_to)){
            foreach ($target_from as $_from){

                if ($_to[$connector_key] == $_from->read_attribute($connector_key)){

                    $output[$_key] = array_merge($_to, $_from->to_array());

                }

            }
        }
        else {
            foreach ($target_from as $_from){

                if ($_to->read_attribute($connector_key) == $_from->read_attribute($connector_key)){

                    $output[$_key] = array_merge($_to->to_array(), $_from->to_array());

                }

            }
        }
    }

    return $output ;
}



function is_admin(){
    Global $_CFG ;

    if (in_array($_SESSION['user']['login'], $_CFG['admins'])) return true ;

    return false ;
}

function redirect($url, $timeout = 0, $params = []){

    $withhash = '';

    if (in_array('withhash', $params))
        $withhash = ' + document.location.hash ';

    print "
	<script type='text/javascript'>
	
		setTimeout(function(){
		    document.location.href = '" . $url . "'" . $withhash .  ";
		}, " . ($timeout * 1000) . ");	
		
	</script>";
    exit();
}

function is_authed($type = "helper"){
    Global $_USER ;

    if ($type == 'helper')
        if (isset($_SESSION['user']['id']))
            return true;

    if ($type == 'admin')
        if (isset($_SESSION['user']['id']) AND $_USER->getOptions('role') == 'admin')
            return true;

    return false;
}


/**
 * ‘ункци¤ возвращает окончание дл¤ множественного числа слова на основании числа и массива окончаний
 * @param  $number Integer „исло на основе которого нужно сформировать окончание
 * @param  $endingsArray  Array ћассив слов или окончаний дл¤ чисел (1, 4, 5),
 *         например array('¤блоко', '¤блока', '¤блок')
 * @return String
 */
function getNumEnding($number, $endingArray){
    $number = $number % 100;
    if ($number>=11 && $number<=19) {
        $ending=$endingArray[2];
    }
    else {
        $i = $number % 10;
        switch ($i) {
            case (1): $ending = $endingArray[0]; break;
            case (2):
            case (3):
            case (4): $ending = $endingArray[1]; break;
            default: $ending = $endingArray[2];
        }
    }
    return $ending;
}


if (!function_exists('getWordCase')) {
    function getWordCase($type, $origin_word, $default=null) {
        $case = db_sklonenie_profession::find('first', [
            'select' => 'target_word_skl',
            'conditions' => ['origin_word=? AND LOWER(target_word)=?', $type, mb_strtolower($origin_word, 'utf-8')],
            'order' => 'id DESC'
        ]);

        if (isset($_GET['wtf'])){
            $condition = ['origin_word=? AND target_word LIKE CONCAT("%", ?, "%")', $type, $origin_word];
            print '<pre>' . print_r($condition, true) . '</pre>';
            print '<pre>' . print_r($case, true) . '</pre>';
        }
        if (null !== $case) {
            return $case->read_attribute('target_word_skl');
        } else {
            if (null !== $default) {
                return $default;
            } else {
                return $origin_word;
            }

        }
    }
}

if (!function_exists('mb_lcfirst')) {
    function mb_lcfirst($string, $encoding='utf-8')
    {
        $strlen = mb_strlen($string, $encoding);
        $firstChar = mb_substr($string, 0, 1, $encoding);
        $then = mb_substr($string, 1, $strlen - 1, $encoding);
        return mb_strtolower($firstChar, $encoding) . $then;
    }
}

if (!function_exists('mb_ucfirst')) {
    function mb_ucfirst($string, $encoding='utf-8')
    {
        $strlen = mb_strlen($string, $encoding);
        $firstChar = mb_substr($string, 0, 1, $encoding);
        $then = mb_substr($string, 1, $strlen - 1, $encoding);
        return mb_strtoupper($firstChar, $encoding) . $then;
    }
}

if (function_exists('format_money') == false){
    function format_money($money){

        return number_format($money, 0, ' ', ' ');
    }
}



//пережатая картинка
function getImage($filepath,$width,$height,$crop=true,$cache_folder='news'){

    global $_CFG;

    $full_path = $filepath;
    $full_path = str_replace('https://podpishi.org/','',$full_path);
    $full_path = str_replace('https://podpishi60.ru/','',$full_path);
    $full_path = $_CFG['root'].$full_path;

    $cache_folder = 'static/images/'.$cache_folder.'/thumbs/';

    $method = 4;
    if(!$crop) $method = 3;

    try {
        if(!file_exists($full_path)) return $full_path;

        $ext = pathinfo($filepath, PATHINFO_EXTENSION);
        $resized_file_name = md5($filepath).'_'.$width.'x'.$height.'-'.$method.'.'.$ext;
        //exit($full_path);


        if(!file_exists($_CFG['root'].$cache_folder.$resized_file_name)) {

            require_once $_CFG['root'].'/classes/'.'php_image_magician.php';

            $magicianObj = new imageLib($full_path);
            $magicianObj -> resizeImage($width, $height, $method);
            $magicianObj -> saveImage($_CFG['root'].$cache_folder.$resized_file_name, 100);

        }
    } catch(Exception $e) {
        return $filepath;
    }

    return '/'.$cache_folder.$resized_file_name;
}




function num2str($num,$only_num=false,$gender=0) {
    $nul='ноль';
    $ten=array(
        array('','один','два','три','четыре','пять','шесть','семь', 'восемь','девять'),
        array('','одна','две','три','четыре','пять','шесть','семь', 'восемь','девять'),
    );
    $a20=array('десять','одиннадцать','двенадцать','тринадцать','четырнадцать' ,'пятнадцать','шестнадцать','семнадцать','восемнадцать','девятнадцать');
    $tens=array(2=>'двадцать','тридцать','сорок','пятьдесят','шестьдесят','семьдесят' ,'восемьдесят','девяносто');
    $hundred=array('','сто','двести','триста','четыреста','пятьсот','шестьсот', 'семьсот','восемьсот','девятьсот');
    $unit=array( // Units
        array('копейка' ,'копейки' ,'копеек',    1),
        array('рубль'   ,'рубля'   ,'рублей'    ,0),
        array('тысяча'  ,'тысячи'  ,'тысяч'     ,1),
        array('миллион' ,'миллиона','миллионов' ,0),
        array('миллиард','милиарда','миллиардов',0),
    );
    //
    //list($rub,$kop) = explode('.',sprintf("%015.2f", floatval($num)));
    $t=explode('.',sprintf("%015.2f", floatval($num)));
    $rub=$t[0];
    if(count($t)==2) {
        $kop = $t[1];
    } else {
        $kop=0;
    }
    $out = array();
    if (intval($rub)>0) {
        foreach(str_split($rub,3) as $uk=>$v) { // by 3 symbols
            if (!intval($v)) continue;
            $uk = sizeof($unit)-$uk-1; // unit key
            //$gender = $unit[$uk][3];
            list($i1,$i2,$i3) = array_map('intval',str_split($v,1));
            // mega-logic
            $out[] = $hundred[$i1]; # 1xx-9xx
            if ($i2>1) $out[]= $tens[$i2].' '.$ten[$gender][$i3]; # 20-99
            else $out[]= $i2>0 ? $a20[$i3] : $ten[$gender][$i3]; # 10-19 | 1-9
            // units without rub & kop
            if ($uk>1) $out[]= morph($v,$unit[$uk][0],$unit[$uk][1],$unit[$uk][2]);
        } //foreach
    }
    else $out[] = $nul;

    if($only_num) return join(' ',$out);

    $out[] = morph(intval($rub), $unit[1][0],$unit[1][1],$unit[1][2]); // rub
    //$out[] = $kop.' '.$this->morph($kop,$unit[0][0],$unit[0][1],$unit[0][2]); // kop
    return trim(preg_replace('/ {2,}/', ' ', join(' ',$out)));
}
function morph($n, $f1, $f2, $f5) {
    $n = abs(intval($n)) % 100;
    if ($n>10 && $n<20) return $f5;
    $n = $n % 10;
    if ($n>1 && $n<5) return $f2;
    if ($n==1) return $f1;
    return $f5;
}

/*
	ѕолучить айпи адрес
*/
function GetRealIp(){

    if (isset($_SERVER['HTTP_CF_CONNECTING_IP']))
        return $_SERVER['HTTP_CF_CONNECTING_IP'] ;

    return $_SERVER['REMOTE_ADDR'] ;

}
?>
