<?php

$authEndpoins = [
    'spb2019' => [
        'url' => 'https://spb2019.city4people.ru/ajax/ajax_auth.php',
        'oauth_from' => 'spb2019',
        'onCreateUserVars' => [
            'petition_city' => 'Санкт-Петербург',
        ],
    ],
    'mundep' => [
        'url' => 'https://deputies.maxkatz.ru/ajax/ajax_auth.php',
        'oauth_from' => 'mundep',
        'onCreateUserVars' => [
            'petition_city' => 'Москва',
        ],
    ],
    'c4prussia' => [
        'url' => 'https://russia.city4people.ru/ajax/ajax_auth.php',
        'oauth_from' => 'c4prussia',
        'onCreateUserVars' => [],
    ],
];

if (!@isset($authEndpoins[$_GET['origin']])){
    $_GET['origin'] = 'mundep';
}

$endpoint = $authEndpoins[$_GET['origin']] ;

$external_token = $_GET['parts'][2];


if (strlen($external_token) != 32)
    redirect('/login/#1');

// auth by

$url = $endpoint['url'] . '?context=getExternalAuthInfo&external_token=' . $external_token;

//print $url;

//exit();

$content = json_decode(file_get_contents($url), true);

if (empty($content['user']))
    redirect('/login/#2');

//print '<pre>' . print_r($content, true) . '</pre>';
//exit();


$user = db_main_users::find_by_phone($content['user']['phone']);

if (empty($user)){

    $newUser = [
        'name' => $content['user']['name'],
        'surname' => $content['user']['surname'],
        'phone' => $content['user']['phone'],
        'email' => $content['user']['email'],
        'role' => 'guest',
        'oauth_from' => $endpoint['oauth_from'],
    ];

    if (isset($content['user']['region_name']))
        $newUser['region_name'] = $content['user']['region_name'];

    if (isset($content['user']['city']))
        $newUser['petition_city'] = $content['user']['city'];

    foreach ($endpoint['onCreateUserVars'] as $_name => $_value){

        $newUser[$_name] = $_value ;

    }

    $newUser['password'] = md5(json_encode($newUser) . mt_rand(1,9999));
    $newUser['salt'] = md5(mt_rand(1, 9999999999) / mt_rand(1,10));


    //print '<pre>' . print_r($newUser, true) . '</pre>';
    //exit();

    $user = db_main_users::create($newUser);


    createGdLogEntry('new_user', array(
        'tg_bot_status' => 'inqueue',
        'arg_id' => $user->id,
    ));

}
else {

    if ($user->oauth_from != '' AND $user->oauth_from != $endpoint['oauth_from']){
        redirect('/login/#Oauth wrong domain');
    }

}

//print '<pre>' . print_r($user, true) . '</pre>';

$_oUser = new gh_user('db_main_users');
$_oUser->loadById($user->id);

$outputAuth = $_oUser->performAuth();


redirect('/cabinet');

//print '<pre>' . print_r($content, true) . '</pre>';

?>