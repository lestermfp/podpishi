<?php
include('./tg_botBase.php');

//print '<pre>' . print_r($user, true) . '</pre>';

ini_set('display_errors', true);
ini_set('error_reporting',  E_ALL);
error_reporting(E_ALL + E_STRICT);

if (!isset($_GET['debug'])){
    //print 'debug';
    //exit();
}

define('WEBHOOK_URL', 'https://podpishi.org/ajax/tg/tg_pdSendMessages.php');

if (php_sapi_name() == 'cli') {
    // if run from console, set or delete webhook
    apiRequest('setWebhook', array('url' => isset($argv[1]) && $argv[1] == 'delete' ? '' : WEBHOOK_URL));
    exit;
}


$chat_id = $_POST['botChannels']['my'];
$chat_id = $_POST['botChannels']['main_chat'];



class tgHandler {

    public function initiateVars($params){

        foreach ($params as $_key => $_param)
            $this->$_key = $_param ;

    }

}

/*
 * Класс, занимающийся обработкой логов из таблицы main_activityLog
 */
class sendilogFactory {

    public $entries = [];
    public $entries_types_to_except = [];

    static $cache_key = 'podpishitg';

    /*
     * Проверяем, можно ли выполнить
     */
    public function prepareLogs()
    {

        if (!$this->bindFactoryForWorking())
            return;


        $this->executeLogs();

        $this->releaseFactoryBinding();

    }

    public function releaseFactoryBinding()
    {

        gdCache::remove(self::$cache_key);

    }

    public function bindFactoryForWorking()
    {

        $cache_key = self::$cache_key;

        if (gdCache::get($cache_key) !== false) {
            print 'Ignored cause cache';
            //return false;
        }

        gdCache::put($cache_key, 'true', 75);

        return true;

    }

    public function loadLogs(){

        $entries = db_main_activityLog::find('all', array(
            'conditions' => 'tg_bot_status="inqueue"',
            'order' => 'date ASC',
            'limit' => 17,
        ));

        //$entries = array_merge($entries);

        print 'Entries: ' . count($entries) . '<br/>';

        if (isset($_GET['logs_dump']))
            print '<pre>' . print_r($entries, true) . '</pre>';

        //exit();

        $this->entries = $entries;

    }

    public function executeLogs(){
        Global $_CFG;

        $this->loadLogs();

        if (empty($this->entries))
            return;


        foreach ($this->entries as $_entry){

            if (in_array($_entry->type, $this->entries_types_to_except)){

                print 'Entry excepted due to previous exception captured #' . $_entry->id . "<br/>";
                continue ;

            }

            try {

                $this->raiseHandlerForEntry($_entry);

            } catch (Exception $e) {

                var_dump($e);

                $exceptionPublicMsg = 'Exception captured at entries #' . $_entry->id ;

                print $exceptionPublicMsg . '<br/>';

                $this->notifyTechSupport($exceptionPublicMsg);

                $this->entries_types_to_except[] = $_entry->type ;
            }

        }

    }

    public function notifyTechSupport($text){

        apiRequest("sendMessage", array('chat_id' => $_POST['botChannels']['my'], "text" => $text));

    }

    public function raiseHandlerForEntry($_entry){
        Global $_CFG ;

        $details = unserialize($_entry->read_attribute('details'));

        if ($_entry->type != '100book_order'){
            print 'awaiting: ' . $_entry->type . '<br/>';
            //continue ;
        }

        $target_type = $_entry->read_attribute('type');

        $target_handler_file = './log_handlers/' . $target_type . '.php';
        $target_handler_class = 'tgHandler_' . $_entry->type ;

        // INCLUDE handler by its type
        if (file_exists($target_handler_file)){

            print 'Handling: ' . $target_handler_class . ' (#' . $_entry->id . ')<br/>';

            if (!class_exists($target_handler_class, $autoload = false))
                include_once($target_handler_file);

            if (class_exists($target_handler_class)){

                $logHandler = new $target_handler_class();

                $logHandler->initiateVars([
                    '_entry' => $_entry, 'details' => $details, 'chat_id' => $chat_id = $_POST['botChannels']['main_chat'],
                    '_CFG' => $_CFG,
                ]);
                $logHandler->run();

            }

        }
        else {
            $publicErorrMsg = 'Handler file not found: ' . basename($target_handler_file);

            print $publicErorrMsg . '<br/>';

            $this->notifyTechSupport($publicErorrMsg);
        }

    }

}

if (isset($_GET['send_messages'])) {
    $sendilogFactory = new sendilogFactory();
    $sendilogFactory->prepareLogs();
}



?>