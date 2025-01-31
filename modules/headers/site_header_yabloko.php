<?php

if (!isset($_CFG['meta'][$module_name]['title']))
    $_CFG['meta'][$module_name]['title'] = 'Гражданские петиции - Псковская область';

if (!isset($_CFG['meta'][$module_name]['og_url']))
    $_CFG['meta'][$module_name]['og_url'] = 'https://podpishi.org/';

if (!isset($_CFG['meta'][$module_name]['og_image']))
    $_CFG['meta'][$module_name]['og_image'] = 'https://podpishi.org/static_yabloko/dist/images/content/sharing_yabloko.jpg';

if (!isset($_CFG['meta'][$module_name]['og_title']))
    $_CFG['meta'][$module_name]['og_title'] = 'Гражданские петиции - Псковская область';

if (!isset($_CFG['meta'][$module_name]['title']))
    $_CFG['meta'][$module_name]['title'] = $_CFG['meta'][$module_name]['og_title'];

if (!isset($_CFG['meta'][$module_name]['og_description']))
    $_CFG['meta'][$module_name]['og_description'] = 'Создаём общественные обращения по важным для жителей вопросам и предлагаем вам подписать их';

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?=$_CFG['meta'][$module_name]['title']?></title>
    <meta name="description" content="<?=$_CFG['meta'][$module_name]['title']?>">
    <meta name="keywords" content="">

    <meta property="og:url" content="<?=$_CFG['meta'][$module_name]['og_url']?>">
    <meta property="og:type" content="article">
    <meta property="og:title" content="<?=$_CFG['meta'][$module_name]['og_title']?>">
    <meta property="og:description" content="<?=$_CFG['meta'][$module_name]['og_description']?>">
    <meta property="og:image" content="<?=$_CFG['meta'][$module_name]['og_image']?>">
    <meta property="og:image:width" content="900" />
    <meta property="og:image:height" content="605" />

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="">
    <meta name="twitter:title" content="<?=$_CFG['meta'][$module_name]['og_title']?>">
    <meta name="twitter:description" content="<?=$_CFG['meta'][$module_name]['og_description']?>">
    <meta name="twitter:image:src" content="<?=$_CFG['meta'][$module_name]['og_image']?>">
    <meta name="twitter:domain" content="<?=$_CFG['meta'][$module_name]['og_url']?>">


    <link rel="apple-touch-icon" href="/static_yabloko/dist/images/content/favicons/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/static_yabloko/dist/images/content/favicons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/static_yabloko/dist/images/content/favicons/favicon-16x16.png">
    <link href="/static_yabloko/dist/css/app.css?vv2" rel="stylesheet"></head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <script src="/static/js/main.js?3" type="text/javascript"></script>
    <script src="/static/js/vue.min.js"></script>

    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-T5N5QE7H5K"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'G-T5N5QE7H5K');
    </script>

<body>

<header class="page-header">
    <div class="container">
        <a href="/" class="page-header__logo" aria-label="Перейти на страницу петиций">
            <img src="/static_yabloko/dist/images/content/icon-petition.svg" alt="">
            <span>Гражданские петиции – Псковская область</span>
        </a>
        <a href="https://donate.shlosberg.ru/" target="_blank" class="page-header__yabloko-logo">
            <img src="/static_yabloko/dist/images/content/logo-small.svg" alt="">
            <span class="page-header__yabloko-logo-text">Псковское Яблоко
                <small class="page-header__yabloko-logo-text-sub">поддержать</small>
            </span>
        </a>
    </div>
</header>