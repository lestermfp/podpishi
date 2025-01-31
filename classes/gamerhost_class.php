<?php

class gdUser {

    /** @var int */
    protected $userId = 0;

    /** @var db_regions_deputy|null */
    protected $supporterUser = null;

    /** @var db_supporters_enrollment|null */
    protected $enrollmentUser = null;

    /** @var db_supporters_enrollment|null */
    protected $collectorUser = null;

    /** @var db_supporters_enrollment|null */
    protected $observerUser = null;



    /** @var array */
    protected $signs = [];

    /** @var array */
    protected $team = [];

    public function getName($params = array()){
		
        $name = $this->mainUser->name . ' ' . $this->mainUser->surname ;

        return $name ;
    }


    public function getMainUser(){

        return $this->mainUser ;

    }

    public function getEnrollmentUser(){

        if ($this->enrollmentUser == null) {
            $this->enrollmentUser = db_supporters_enrollment::find_by_user_id($this->getMainUser()->id);
        }

        return $this->enrollmentUser;

    }

    public function getCollectorUser(){

        if ($this->collectorUser == null) {
            $this->collectorUser = db_collectors_list::find_by_user_id($this->getMainUser()->id);
        }

        return $this->collectorUser;

    }

    public function getObserverUser(){

        if ($this->observerUser == null) {
            $this->observerUser = db_observers_list::find_by_user_id($this->getMainUser()->id);
        }

        return $this->observerUser ;

    }

    public function getSupporterUser(){

        if ($this->supporterUser == null) {
            $this->supporterUser = db_supporters::find_by_created_account($this->getMainUser()->id);
        }

        return $this->supporterUser;
    }

    /*
     *
     * Отмечаем когда было первый раз открыто задание этим кандидатом
     *
     */
    public function markTaskAsClosed($task_type){

        $taskMark = db_tasks_opening::find('first', array(
            'conditions' => array('user_id=? AND task_type=?', $this->getMainUser()->id, $task_type),
        ));

        if (empty($taskMark))
            return ;


        $taskMark->is_closed = 'true';
        $taskMark->closed_at = 'now';
        $taskMark->save();

    }

    /*
     *
     * Отмечаем когда было первый раз открыто задание этим кандидатом
     *
     */
    public function markTaskAsOpened($task_type){

        $cache_key = 'o_' . $task_type . '_' . $this->getMainUser()->id ;

        $cached = gdCache::get($cache_key);

        if ($cached == 'exists')
            return ;

        $isExists = db_tasks_opening::exists(array(
            'conditions' => array('user_id=? AND task_type=?', $this->getMainUser()->id, $task_type),
        ));

        if ($isExists) {

            gdCache::put($cache_key, 'exists', 5 * 60);
            return;

        }

        $newMark = array(
            'user_id' => $this->getMainUser()->id,
            'task_type' => $task_type,
            'date' => 'now',
        );

        db_tasks_opening::create($newMark);

    }

    /*
     *
     * Выполняются действия при инициализации класса
     *
     */
    public function doBornActions(){

        if ($this->getMainUser()->public_hash == ''){



            $connection = db_attaches_list::connection() ;
            $connection->query('LOCK TABLES `main_users` WRITE');

            while (true){
                $public_hash = md5(time() . $this->getMainUser()->id . mt_rand(1, 999));

                $public_hash = substr($public_hash, mt_rand(1,20) ,7);

                $isExists = db_main_users::exists(array(
                    'conditions' => array('public_hash=?', $public_hash),
                ));

                if ($isExists)
                    continue ;

                $this->getMainUser()->public_hash = $public_hash ;
                $this->getMainUser()->save();

                break ;

            }

            $connection->query('UNLOCK TABLES');

        }

    }

    public function getReferedList(){

    }

    public function getReferers(){
        $supporters = db_supporters::all([
            'conditions'=>['referer_of = ?',$this->getMainUser()->id],
            'select' => 'surname, name, created_account, living_subject_h, reg_ip',
        ]);

        return $supporters;
    }

    public function getReferersAmount(){

        $cache_key = 'ref_amount'.$this->getMainUser()->id;

        $cached = gdCache::get($cache_key);

        if ($cached === false) {

            $amount = db_supporters::count(array(
                'conditions' => array('referer_of=?', $this->getMainUser()->id),
            ));

            gdCache::put($cache_key, $amount, 30);

        }
        else {
            $amount = $cached ;
        }


        return $amount ;

    }

    public function performAuth(){

        $_SESSION['user']['id'] = $this->getMainUser()->id;
        $_SESSION['user']['password'] = $this->getMainUser()->password ;

        $this->getMainUser()->last_visit = 'now';
        $this->getMainUser()->last_ip = GetRealIp();
        $this->getMainUser()->save();

        $authLog = gdHandlerSkeleton::makeAuthLog($this->getMainUser());

        //print '<pre>' . print_r($_POST, true) . '</pre>';

        //exit();

        $output = [];

        /*
            Set up hash
        */

        while (true){
            $session_hash = md5(time() . $this->getMainUser()->read_attribute('id') . md5(serialize($_SERVER['HTTP_USER_AGENT'])) . mt_rand(1,55));

            try {
                $this->getMainUser()->session_hash = $session_hash ;
                $this->getMainUser()->save();

                $output['session_hash'] = $session_hash;

                break ;
            } catch (Exception $e) {

                continue ;
            }
        }

        createGdLogEntry('auth', array(
            'tg_bot_status' => 'inqueue',
            'user_id' => $this->getMainUser()->read_attribute('id'),
            'authLogId' => $authLog->read_attribute('id'),
        ));


        return $output ;


    }

    public function getRefererUrl(){
        Global $_CFG ;

        return '' . $_CFG['domain'] . '/join/' . $this->getMainUser()->public_hash ;

    }

    public function checkCabinetVisit()
    {

        setcookie("last_cabinet_view1", time(), time()+2592000);
    }

    //мероприятия о которых я еще не знаю
    public function countNewEvents(){


        $cache_key = 'countNewEvents11'.$this->getMainUser()->id;
        $count = gdCache::get($cache_key);

        if ($count === false) {

            // Не показыем иностранцам ивенты
            if ($this->getSupporterUser()->foreign_country != ''){
                $count = 0 ;
            }
            else {


                $last_cabinet_visit = '1970-01-01 00:00:00';

                if (isset($_COOKIE['last_cabinet_view1'])) {
                    $cookie = $_COOKIE['last_cabinet_view1'];
                    if (is_numeric($cookie)) {
                        //=(
                        if (date('Y', $cookie) < 2017 || date('Y', $cookie) > 2024) {
                            $last_cabinet_visit = '1970-01-01 00:00:00';
                        } else {
                            $last_cabinet_visit = date('Y-m-d H:i:s', $cookie);
                        }
                    }
                }

                $query_part = 'REGEXP "(^|;){1}' . $this->getSupporterUser()->living_subject . '(;|$)"';
                $allowedEvents = db_events_list::find('all', array(
                    'select' => 'unique_name',
                    'conditions' => array('targeting ' . $query_part . ' and is_active=1 and date_active_till>NOW() and date>?', $last_cabinet_visit),
                    'order' => 'date_active_till asc'
                ));

                $count = 0;
                foreach ($allowedEvents as $_event) {
                    $event = new sbEvent($_event->unique_name);
                    if ($event->isActive()) {

                        if ($event->isThereUser($this->getMainUser()->id)) {
                            continue;
                        }

                        ++$count;
                    }
                }
            }

            gdCache::put($cache_key, $count, 10);
        }

        return $count;
    }


    /*
     * Мероприятия в моем городе (выводим в кабинете, если человек не записан на мероприятие)
     */
    public function getAvailableEvents($subscribed=true,$limit=10) {



        $cache_key = 'getAvailableEvents5'.$limit.'_'.$this->getMainUser()->id;

        $events_list = gdCache::get($cache_key);

        if ($events_list === false) {
            $query_part = 'REGEXP "(^|;){1}' . $this->getSupporterUser()->living_subject . '(;|$)"';
            $allowedEvents = db_events_list::find('all', array(
                'select' => 'unique_name',
                'conditions' => array('targeting ' . $query_part . ' and is_active=1 and date_active_till>NOW()' ),
                'limit'=>$limit,
                'order'=>'date_active_till asc'
            ));
            $events_list = [];

            foreach($allowedEvents as $_event) {
                $event = new sbEvent($_event->unique_name);

                if($event->isActive()) {

                    if($subscribed) {

                        if($event->isThereUser($this->getMainUser()->id)) {

                            continue;
                        }

                    } else {

                    }
                    $events_list[] = $event->getDetails();
                }
            }


            gdCache::put($cache_key, $events_list, 10);
        }


        return empty($events_list)?false:$events_list;
    }

    /*
     * Находим на какие из мероприятий мы записаны
     */
    public function getEventsRememberers(){

        $output = array();

        $myMeetings = db_events_people::find('all', array(
            'select' => 'event_unique_name',
            'conditions' => array('user_id=? AND date>=NOW() - INTERVAL 30 DAY', $this->getMainUser()->id),
        ));


        foreach ($myMeetings as $_meeting){

            $event = new sbEvent($_meeting->event_unique_name);
            if(!$event->isActive()) continue;


            $details = $event->getDetails();

            $output[] = array(
                'title_clear' => $details['title_clear'],
                'date' => $details['date_start_h'] . ', ' . $details['time_start_h'],
                'unique_name' => $details['unique_name'],
                'external_link' => $details['external_link'],
            );

        }

        return $output ;
    }

    public function getLivingParams(){

        $output = gdHandlerSkeleton::getOrmVars('living_address, vote_address, foreign_country, living_subject_h, living_addr_h, vote_subject, vote_subject_h', $this->getSupporterUser());

        foreach(array('living_address', 'vote_address') as $_param){

            $value = '';

            if ($output[$_param] != 0){

                $address = db_yd_address::find_by_id($output[$_param], array(
                    'select' => 'full_address',
                ));

                $value = $address->full_address ;

            }

            $output[$_param . '_h'] = $value ;


        }

        return $output ;

    }

    public function getLivingSubject($params = array()){

        $cache_key = 'user_living_subject_h_'.$this->getMainUser()->id;

        $address = gdCache::get($cache_key);

        if ($address === false) {
            if ($this->getSupporterUser()->foreign_country != '')
                return $this->getSupporterUser()->foreign_country ;

            $address = $this->getSupporterUser()->living_subject_h ;

            if (!in_array('subject_only', $params))
                if ($this->getSupporterUser()->living_addr_h != '')
                    $address .= ', ' . $this->getSupporterUser()->living_addr_h ;

            gdCache::put($cache_key, $address, 300);
        }

        return $address ;

    }

    public function cacheFlush($mode){

        if ($mode == 'getLivingSubject')
            gdCache::remove('user_living_subject_h_'. $this->getMainUser()->id);

    }

    public function hasRole($roles){

        if (!is_array($roles))
            $roles = [$roles] ;

        if (in_array($this->getMainUser()->role, $roles))
            return true ;

        return false ;
    }

    public function hasExtraClass($extra_class) {

        return classFactory::c_isset($this->getOptions('extra_class'), $extra_class);
    }

    public function isObserver($extra_class) {

        return $this->hasExtraClass('is_observer');
    }



    public function addExtraClass($extra_class) {
        if(!$this->hasExtraClass($extra_class)) {

            $extra_class = classFactory::c_add($this->getOptions('extra_class'),$extra_class);
            $this->getMainUser()->extra_class = $extra_class;

            return $this->setOptions('extra_class',$extra_class);
        }
    }

    public function getConnectedSocials(){

        $social_auth = db_social_auth::find('all', array(
            'conditions' => array('user_id=?', $this->getMainUser()->id),
            'select' => 'social_type',
        ));

        $authTypes = array(
            array(
                'name' => 'Вконтакте',
                'link' => '/auth/vk',
                'required_key' => 'vk',
            ),
            array(
                'name' => 'Фейсбук',
                'link' => '/auth/fb',
                'required_key' => 'fb',
            ),
            array(
                'name' => 'Твиттер',
                'link' => '/auth/tw',
                'required_key' => 'tw',
            ),
            array(
                'name' => 'Google',
                'link' => '/auth/google',
                'required_key' => 'google',
            ),
            array(
                'name' => 'Одноклассники',
                'link' => '/auth/ok',
                'required_key' => 'ok',
            ),
        );

        foreach ($authTypes as $_key => $_auth){

            foreach ($social_auth as $_social)
                if ($_social->read_attribute('social_type') == $_auth['required_key']){
                    $authTypes[$_key]['connected'] = true ;
                    continue 2;
                }

            $authTypes[$_key]['connected'] = false ;

        }

        return $authTypes ;

    }


    public function getAvatar(){
        return '/static/attach/' . $this->getMainUser()->read_attribute('avatar');
    }


}

class gh_user extends gdUser {
	
	public $mainUser = array(); //Объект activerecord с информцией о пользователе,
	private $db_object = '';

    public static function findById($id){

        $user = new gh_user('db_main_users');

        //если сломается, вернуть это
        //$user->loadById($id);
        //return $user ;
        return $user->loadById($id)?$user:false;
    }

    public static function findByPhone($phone){

        $user = new gh_user('db_main_users');

        return $user->loadByPhone($phone)?$user:false;
    }

    public function __construct($db_object = 'db_main_users') {
		
		if (!in_array($db_object, array('db_main_users'))){
			print 'Critical db_error';
			exit();
		}
		
		$this->db_object = $db_object ;

	}

	/*
		Принимает строку с одним или несколькими параметрами и их новыми значениями.
		В случае если первый аргумент массив, то второй необязателен
	*/
	public function setOptions($keys, $value = false){
	
		//Если пришёл не массив, а ключ=значение
		if (!is_array($keys)){
			//Всё-равно создаём массив, так как запись в БД проходит только одним методом и через массив
			$keys = array($keys => $value);
		}

		return $this->mainUser->update_attributes($keys);

	}

	/*
		Принимает строку с одним или несколькими параметрами, которые получать
	*/
	public function getOptions($_option_name, $cache = true){
		$db_object = $this->db_object ;
		
		if ($cache == false){
			$_user = $db_object::find_by_login($this->mainUser->read_attribute('login'), array(
				'select' => $_option_name,
			));	
		}
		else {
			$_user = $this->mainUser ;
		}
		
		//Несколько параметров
		if (strpos($_option_name,',') != false){
			$params = explode(',', str_replace(' ', '', $_option_name));
			
			$out_array = array();
			foreach ($params as $_param){
				$out_array[$_param] = $_user->$_param ;
			}
			
			return $out_array ;
		}
		//Один параметр
		else {
			if (in_array($_option_name, array('info')))return $this->base64_formatted($_option_name, $_user);

			return $_user->$_option_name ;
		}
	}

	/*
		Загружает в объект информацию о пользователе по его айди. Если такого логина не существует, тогда возвращается false.
	*/
	private function init($_user) {

        if (empty($_user)) return false ;

        $this->mainUser = $_user ;

        $this->doBornActions();

        return true ;
    }

    public function loadById($_id){
        $db_object = $this->db_object ;

        $_user = $db_object::find_by_id(array(
            'id' => $_id,
        ));

        return $this->init($_user);
    }

    public function loadByPhone($_phone){
        $db_object = $this->db_object ;

        $_user = $db_object::find('first',['conditions'=>['phone = ?',$_phone]]);

        return $this->init($_user);
    }

    public function loadByObject($user_object){

        $user = new gh_user('db_main_users');

        $user->mainUser = $user_object ;

        return $user ;

    }

}

?>
