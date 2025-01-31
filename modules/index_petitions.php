<?php

$campaigns = db_campaigns_list::getIndexList(0, 100, 'podpishi');

shuffle($campaigns);

//print '<pre>' . print_r($campaigns, true) . '</pre>';

?>

<!DOCTYPE html>
<html>
<head>
    <title>Активные петици — Podpishi.org</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="description" content="Петиции граждан" />
    <meta name="keywords"    content="" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">

    <meta http-equiv="Cache-Control" content="no-cache" />
    <meta http-equiv="Cache-Control" content="max-age=3600, must-revalidate" />



    <meta property="og:url" content="https://podpishi.org" />
    <meta property="og:title" content="Петиции, которые работают" />
    <meta property="og:type" content="" />
    <meta property="og:description" content="Подпишите обращение и мы его распечатаем, правильно оформим и передадим в государственный орган" />
    <meta property="og:image" content="https://podpishi.org/static/images/opengraph_podpishi.png" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:site" content="" />
    <meta name="twitter:title" content="Петиции, которые работают" />
    <meta name="twitter:description" content="Подпишите обращение и мы его распечатаем, правильно оформим и передадим в государственный орган" />
    <meta name="twitter:image:src" content="https://podpishi.org/static/images/opengraph_podpishi.png" />
    <meta name="twitter:domain" content="https://podpishi.org/" />
    <meta property="vk:image" content="https://podpishi.org/static/images/opengraph_podpishi.png" />

    <!-- bootstrap 4 -->

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/css/bootstrap.min.css" integrity="sha384-PsH8R72JQ3SOdhVi3uxftmaW6Vc51MKb0q5P2rRUpPvrszuE4W1povHYgTpBfshb" crossorigin="anonymous">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.1/css/font-awesome.min.css">

    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,700&amp;subset=cyrillic" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>

    <script src="/static/js/main_gd.js?v3"></script>

    <style>

        body {
            font-family: "PT Sans";
        }

        section.header {
            height: 122px;
            background: #ffc605;

            position: relative;
            overflow: hidden;
        }

        section.header::after {
            content: '';
            position: absolute;
            z-index: 0;
            background: white;
            height: 130px;
            transform: rotateZ(-5deg);
            transform-origin: bottom right;
            top: 28px;
            left: -50px;
            right: -50px;
        }

        section.header h1 {

            line-height: 102px;
            color: black;
            font-size: 35px;
            font-weight: bold;
            letter-spacing: 1px;

        }

        @media screen and (max-width: 420px){

            section.header::after {
                top: 78px;
            }

        }

        @media screen and (max-width: 320px) {

            section.header h1 {
                font-size: 32px;
            }

        }

        @media screen and (min-width: 568px){

            section.header::after {
                top: 47px;
            }

        }

        @media screen and (min-width: 1024px){

            section.header::after {
                top: 27px;
            }

        }

        @media screen and (min-width: 1200px){

            section.header::after {
                top: 20px;
            }

            section.header::after {
                transform: rotateZ(-4deg);
            }


        }

        @media screen and (min-width: 1366px) and (max-width: 1366px){

            section.header::after {
                top: -2px;
            }

        }

        @media screen and (min-width: 1367px){

            section.header::after {
                top: 18px;
            }

            section.header::after {
                transform: rotateZ(-3deg);
            }

        }

        .petitions-list {
            position: relative;
            z-index: 10;
        }

        .petition-img {
            overflow: hidden;
            max-width: 100%;
        }

        .petition-img img {
            max-height: 245px;
        }

        .petition-img::after {
            content: '';
            position: absolute;
            z-index: 0;
            background: white;
            height: 50px;
            width: 270px;
            transform: rotateZ(-81.7deg);
            transform-origin: bottom right;
            top: -48px;
            right: -50px;
        }

        a.petition-item {
            color: #000;
            display: block;
            /*max-width: 375px;*/
            font-family: "PT Sans";
        }

        a.petition-item:hover {
            text-decoration: none;
        }

        .petition-item {
            height: 100%;
            position: relative;
            padding-bottom: 45px;
        }

        .petition-title {

            text-decoration: underline;
            font-size: 18px;
            font-weight: bold;

            width: 85%;
            margin-top: 12px;

        }

        .petition-img {
            border-radius: 0px;
            overflow: hidden;
            transition: all 0.2s ease-in-out;
            box-shadow: 0px 0px 0px 0px #1bbd00;
        }

        .petition-item .text-wrap {
            font-size: 13px;
            line-height: 18px;
        }


        .petition-votes--orange {
            background: #ffc605;
        }

        .petition-votes {
            position: absolute;
            right: 0px;
            top: 172px;
            width: 110px;
            height: 43px;
            line-height: 16px;
            padding-left: 10px;
            padding-top: 6px;
            z-index: 1;

            -webkit-transform: skew(-8deg);
            -moz-transform: skew(-8deg);
            -o-transform: skew(-8deg);
        }

        .petition-votes > div {
            -webkit-transform: skew(8deg);
            -moz-transform: skew(8deg);
            -o-transform: skew(8deg);
        }

        .petition-votes .petition-votes__number {
            font-weight: bold;
        }
        .petition-votes .petition-votes__text {
            font-size: 14px;
            margin-top: 1px;
        }

    </style>

</head>
<body>

<section class="header">

    <div class="container">

        <h1>Активные петиции</h1>

    </div>

</section>

<section class="petitions-list">
    <div class="container">

        <div class="widget last-posts-widget mt-5">

            <div class="row">

                <?php

                    foreach ($campaigns as $_key => $_campaign){

                        $extra_class = '';

                        if ($_key % 2)
                            $extra_class = 'offset-lg-1';

                        $arrowcolor = '#ffc605';

                        if ($_campaign['var_arrowcolor'] != '')
                            $arrowcolor = $_campaign['var_arrowcolor'];

                        ?>

                        <div class="col-lg-5 <?=$extra_class;?>">

                            <a class="petition-item" href="/<?=$_campaign['url']?>">

                                <div class="petition-votes" style="background: <?=$arrowcolor;?>;">
                                    <div class="petition-votes__number"><?=format_money($_campaign['appeals_amount'])?></div>
                                    <div class="petition-votes__text"><?=getNumEnding($_campaign['appeals_amount'], ['подписал', 'подписало', 'подписали'])?></div>
                                </div>

                                <div class="petition-img">

                                    <img src="<?=$_campaign['image_preview_url'];?>">

                                </div>
                                <h3 class="petition-title"><?=$_campaign['title']?></h3>
                                <div class="text-wrap">
                                    <?=$_campaign['subtitle_descr']?>
                                </div>
                                <small class="text-muted"><?=$_campaign['petition_city']?></small>

                            </a>

                        </div>

                        <?php

                    }

                ?>



            </div>
        </div>
    </div>
</section>


<section class="footer ">

    <div class="container text-center">

        <div class="row">

            <div class="col-12 mb-5">

                <div class="social sharings-block">
                    <div class="container">
                        <div class="social__title">Расскажите друзьям</div>
                        <div class="likely">
                            <div class="twitter">Твитнуть</div>
                            <div class="facebook">Поделиться</div>
                            <div class="vkontakte">Поделиться</div>
                        </div>
                    </div>
                </div>

            </div>

        </div>

        <div class="row mt-5">

        </div>

    </div>

</section>


<link rel="stylesheet" type="text/css" href="/static/podpishi/v3/css/likely.css?v" media="screen, projection, print">
<script type="text/javascript" src="/static/podpishi/v3/js/likely.js?v"></script>

<link rel="stylesheet" type="text/css" href="/static/libs/sweetalert2/sweetalert2.min.css">
<script src="/static/libs/sweetalert2/sweetalert2.min.js"></script>

</body>
</html>