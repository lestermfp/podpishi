<?php

//if (is_authed()) $_USER->setOptions('session_hash', $_USER->getOptions('id'));

if (!isset($_SESSION['user'])) $_SESSION['user'] = '';

if (isset($_SESSION['user_real']['id']) AND $_SESSION['user_real']['id'] != ''){
    $_SESSION['user'] = array();
    $_SESSION['user'] = $_SESSION['user_real'] ;
    $_SESSION['user_real']['id'] = '';

    redirect('/dashboard');

}
else {
    $_SESSION['user'] = array();
    $_SESSION['user_real'] = array();
}


redirect('/');
exit();
?>