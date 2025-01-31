<?php
//print '<pre>' . print_r($email_data, true) . '</pre>';

$no_session = true;
$no_functions = true;

include('../../config.php');
include($_CFG['root'] . 'classes/gd_mailgun.php');

if (!isset($_GET['debug'])){
    //print 'Debug';
    //exit();
}

$mass_handler_key = 0 ;

if (isset($_GET['mass_handler_key']))
    $mass_handler_key = $_GET['mass_handler_key'] ;


$cache_key = 'email_daemon_' . $mass_handler_key ;

if ($mass_handler_key != 10)
    if (gdCache::get($cache_key) !== false) {
        print 'exit cause cache';
        exit();
    }

gdCache::put($cache_key, 'true', 10 * 60);

$mailQueue = db_mailQueue::find('all', array(
    'limit' => 250,
    'order' => 'priority DESC',
    'conditions' => array('result="inqueue" AND mass_handler_key=?', $mass_handler_key),
));

//print '<pre>' . print_r($mailQueue, true) . '</pre>';

//exit();

gdMailgun::sendCollection($mailQueue);


gdCache::remove($cache_key);


?>
