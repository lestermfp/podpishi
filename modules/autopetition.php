<?php

if (!$petitionInfo->isHostAppropriate($_SERVER['HTTP_HOST']))
    redirect('/');

$petitionInfo = $petitionInfo->to_array();

if ($petitionInfo['id'] == 33){
    redirect('');
}

$amountAppeals = db_campaigns_list::getAppealsAmountById($petitionInfo['id']);

$mapObjectsRaw = db_appeals_list::find('all', [
    'select' => 'lat, lng',
    'conditions' => ['is_hidden="false" AND destination=? AND lat!=""', $petitionInfo['id']],
    'group' => 'yd_addr_hash',
]);

$mapObjects = [];

foreach ($mapObjectsRaw as $_object)
    $mapObjects[] = $_object->to_array();

$petitionInfo['onsave_descr'] = str_replace(['публичной оферты', 'публичной оферты'], '<a target="_blank" href="/' . $petitionInfo['url'] . '/offer">публичной оферты</a>', $petitionInfo['onsave_descr']);

if (isset($_GET['dump_ma'])){

    //print '<pre>' . print_r($mapObjects, true) . '</pre>';
    //exit();
}

//print '<pre>' . print_r($petitionInfo, true) . '</pre>';

$regions_list = [];

if (strpos($petitionInfo['extra_class'], 'moscow_regions') !== false){

    $regions_listRaw = db_regions_list::find('all', [
        'select' => 'id, region_name',
        'conditions' => ['city="Москва"'],
    ]);
    $regions_list = gdHandlerSkeleton::collectKeys($regions_listRaw, ['region_name']);

}

$subscribesNg = [];
$subscribesNg_groupped = [];

if ($petitionInfo['id'] == -33 OR isset($_GET['forced_ng'])){

    $appeals = db_appeals_list::find('all', [
        'conditions' => ['destination=33 AND full_name!="Бесединой Дарьи Станиславовны"'],
        'select' => 'ng_apple_role, ng_apple_year, ng_apple_city, full_name',
        'order' => 'date ASC',
    ]);

    $subscribesNg_groupped = [];

    $subscribesNg_groupped['Депутат от Яблока'] = ['Бесединой Дарьи Станиславовны<small>, депутат от Яблока (Московская городская Дума)</small>'];


    foreach ($appeals as $_appeal){

        if (!isset($subscribesNg_groupped[$_appeal->ng_apple_role]))
            $subscribesNg_groupped[$_appeal->ng_apple_role] = [];

        $line = '' . $_appeal->full_name . '<small>' ;

        if ($_appeal->ng_apple_role == 'Депутат от Яблока'){

            $line .= ', депутат от Яблока (' . $_appeal->ng_apple_city . ')';
        }
        else {

            if ($_appeal->ng_apple_role == 'Член Яблока')
                $line .= ', член Яблока с ' . $_appeal->ng_apple_year;

            if ($_appeal->ng_apple_role == 'Сторонник партии')
                $line .= ', сторонник партии с ' . $_appeal->ng_apple_year;

        }


        $line .= '</small>';

        $subscribesNg_groupped[$_appeal->ng_apple_role][] = $line ;

    }

    //var_dump($subscribesNg_groupped);

}

$subscribersList = [];


if ($petitionInfo['id'] == 34){

    $appeals = db_appeals_list::find('all', [
        'conditions' => ['destination=34'],
        'select' => 'full_name, region_name',
        'order' => 'date ASC',
    ]);


    foreach ($appeals as $_appeal){


        $subscribersList[] = $_appeal->full_name . ' <small>(' . $_appeal->region_name . ')</small>';

    }

    //var_dump($subscribesNg_groupped);

}

$spbmo_regions = [];

if ($petitionInfo['id'] == 34){

    $regions = db_regions_list::find('all', [
        'select' => 'region_name',
        'conditions' => ['city="Санкт-Петербург"'],
        'order' => 'region_name ASC',
    ]);

    foreach ($regions as $_region)
        $spbmo_regions[] = $_region->region_name ;

}

$readonly = false;

if ($possible_petiton == $petitionInfo['url_readonly']) {
    $readonly = true;

    $petitionInfo['url'] = $petitionInfo['url_readonly'];
}

unset($petitionInfo['url_readonly']);


?>


<!DOCTYPE html>
<html class="mdl-js">
<head>
    <title><?=$petitionInfo['title'];?></title>

    <meta property="og:url" content="https://podpishi.org/<?=$petitionInfo['url']?>">
    <meta property="og:type" content="article">
    <meta property="og:title" content="<?=$petitionInfo['meta_title']?>">
    <meta property="og:description" content="<?=$petitionInfo['meta_description']?>">
    <meta property="og:image" content="<?=$petitionInfo['meta_image']?>">
    <meta property="og:image:width" content="900" />
    <meta property="og:image:height" content="605" />

    <meta name="twitter:card" content="summary_large_image">  <!-- Тип окна -->
    <meta name="twitter:site" content="">
    <meta name="twitter:title" content="<?=$petitionInfo['meta_title']?>">
    <meta name="twitter:description" content="<?=$petitionInfo['meta_description']?>">
    <meta name="twitter:image:src" content="<?=$petitionInfo['meta_image']?>">
    <meta name="twitter:domain" content="https://podpishi.org/">

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="description" content="">
    <meta name="keywords" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="shortcut icon" href="/static/podpishi/favicon.ico" type="image/x-icon">

    <link href='https://fonts.googleapis.com/css?family=PT+Sans:400,700&subset=latin,cyrillic' rel='stylesheet' type='text/css'>

    <link rel="stylesheet" type="text/css" href="/static/podpishi/v3/css/style_tabs.css?v<?=time();?>" media="screen, projection, print">
    <link rel="stylesheet" type="text/css" href="/static/podpishi/v3/css/likely.css?v" media="screen, projection, print">





    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <script type="text/javascript" src="/static/podpishi/v3/js/likely.js?v"></script>

    <script src="/static/js/main.js?3" type="text/javascript"></script>

    <script src="/static/js/vue.min.js"></script>


    <style>

        [v-cloak] {
            display: none;
        }

        .swal-gd-loader.swal-gd-clean {
            background-color: transparent !important;
            border:0;
        }
        .swal2-modal {
            border-radius: 0;
            min-height: 130px;
        }

        .footer {
            padding-left: 0px;
        }

        @media (max-width: 400px) {
            .splash__description {
                font-size: 22px !important;
                text-align: left;
            }
        }

        <?php

        if ($petitionInfo['var_arrowcolor'] != ''){

            ?>

        :root {
            --style-color: <?=$petitionInfo['var_arrowcolor']?>;
        }

        <?php

        }
 ?>


    </style>

    <script>
        (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
            (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
            m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
        })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

        ga('create', 'UA-49182086-2', 'auto');
        ga('send', 'pageview');

    </script>


<body>
<div class="top-outer">
    <div class="top velo">
        <div class="top__overlay"></div>
        <div class="top-left">

            <div class="splash">
                <div class="splash__text">
                    <div class="splash__text-wrap">
                        <h1 class="splash__title"><?=$petitionInfo['subtitle_title'];?></h1>
                        <p class="splash__description"><?=html_entity_decode($petitionInfo['subtitle_descr']);?></p>
                    </div>
                </div>
                <div class="splash__image" style="background-image: url('<?=$petitionInfo['title_image']?>');">
                </div>
            </div>
            <p></p>

        </div>
    </div>
</div>
<div class="page page-troll active">
    <div class="petition" id="vue-sign">
        <form onsubmit="return false;">
            <div class="container">

                <div class="petition__paper">
                    <div class="wrote">


                        <div class="wrote__text">
                            <span v-html="appeals_amountSkl">Подписали</span>

                        </div>
                        <div class="wrote__num" v-html="window.mainSite.format_money(current().amount)"><?=$amountInfo['mayor']?></div>
                        <div class="wrote__text" v-html="appeals_amountSkl2">
                            человек
                        </div>
                    </div>
                    <div class="container_inner">

                        <div class="form__block" v-if="readonly" v-cloak>

                            <div style="min-height: 100px;">

                            </div>
                            
                        </div>

                        <div class="form__block" v-if="readonly == false">





                            <div class="whom" ><?=nl2br($petitionInfo['whom'])?></div>

                            <div class="form__row" style="position: relative;">

                                <div v-if="info.url == 'bryuhanova2021_all'">
                                    <div class="form__row">от члена партии:</div>
                                </div>

                                <div class="before-input" v-else>от</div><input class="form__input ajax_arg" name="name" :readonly="!info.is_active" placeholder="Булгакова Михаила Афанасьевича" v-model="appeal.full_name" @click="flushError('full_name');" @change="checkOnChange('full_name');" type="text">

                                <div class="form__comment more_margin" v-show="!hasError('full_name')">
                                    Впишите фамилию и имя<br> (отчество по желанию)
                                </div>
                                <div class="form__error more_margin" v-show="hasError('full_name')" v-cloak>
                                    {{errorText('full_name')}}
                                </div>
                            </div>

                            <div v-show="moscow_regions_displayed" v-cloak>
                                <div class="form__row" v-if="info.url == 'bryuhanova2021_all'">депутата в районе:</div>
                                <div class="form__row" v-else>проживающего в районе:</div>


                                <div class="form__row">
                                    <select class="form__input" v-model="appeal.region_name">
                                        <option v-for="region in regions_list" :value="region">{{region}}</option>
                                    </select>

                                    <div class="form__error more_margin" v-show="hasError('region_name')" v-cloak>
                                        {{errorText('region_name')}}
                                    </div>

                                </div>

                            </div>

                            <div v-show="spbmo_deputies_displayed">

                                <p>депутата ВМО:</p>
                                <div class="form__row">
                                    <select class="form__input" v-model="appeal.region_name">
                                        <option :value="region" v-for="region in spbmo_regions">{{region}}</option>
                                    </select>
                                </div>

                            </div>

                            <div v-show="ng_deputies_displayed">
                                <div class="form__row">
                                    <select class="form__input" v-model="appeal.ng_apple_role">
                                        <option value="Член Яблока">Член Яблока</option>
                                        <option value="Депутат от Яблока">Депутат от Яблока</option>
                                        <option value="Сторонник партии">Сторонник партии</option>
                                    </select>

                                    <template v-if="appeal.ng_apple_role == 'Сторонник партии' || appeal.ng_apple_role == 'Член Яблока'">

                                        <div class="form__row">
                                            <p>с какого года:</p>
                                            <input class="form__input ajax_arg" type="text" placeholder="1997" value="" v-model="appeal.ng_apple_year" @click="flushError('ng_apple_year');" name="ng_apple_year">

                                            <div class="form__error more_margin" v-show="hasError('ng_apple_year')" v-cloak>
                                                {{errorText('ng_apple_year')}}
                                            </div>
                                        </div>

                                    </template>

                                    <template v-if="appeal.ng_apple_role == 'Депутат от Яблока'">

                                        <div class="form__row">
                                            <p>в каком городе и районе:</p>
                                            <input class="form__input ajax_arg" type="text" placeholder="Северное Тушино, Москва" value="" v-model="appeal.ng_apple_city" @click="flushError('ng_apple_city');" name="ng_apple_city">

                                            <div class="form__error more_margin" v-show="hasError('ng_apple_city')" v-cloak>
                                                {{errorText('ng_apple_city')}}
                                            </div>
                                        </div>

                                    </template>
                                </div>


                            </div>

                            <div v-show="address_displayed">
                                <div class="form__row">проживающего по адресу:</div>
                                <div class="form__row">
                                    <input class="form__input ajax_arg" :readonly="!info.is_active" type="text" placeholder="Город" value="Москва" v-model="appeal.city" @click="flushError('city');" @change="checkOnChange('city');" name="city">
                                    <div class="form__comment more_margin city_comment" v-show="!hasError('city')">На этот адрес придет ответ</div>

                                    <div class="form__error more_margin" v-show="hasError('city')" v-cloak>
                                        {{errorText('city')}}
                                    </div>
                                </div>
                                <div class="form__row">
                                    <input class="form__input ajax_arg" :readonly="!info.is_active" type="text" placeholder="Улица" v-model="appeal.street_name" name="street_name">

                                    <div class="form__error more_margin" v-show="hasError('street_name')" v-cloak>
                                        {{errorText('street_name')}}
                                    </div>

                                </div>
                                <div class="form__row more_margin">
                                    <input class="form__input form__input--short ajax_arg" :readonly="!info.is_active" type="text" v-model="appeal.house_number" placeholder="Дом/корпус" @click="flushError('house_number');" @change="checkOnChange('house_number');" name="house_number">
                                    <input class="form__input form__input--short ajax_arg" :readonly="!info.is_active" type="text" v-model="appeal.flat" placeholder="Квартира" @click="flushError('flat');" @change="checkOnChange('flat');" name="">


                                    <div class="form__error more_margin" v-show="hasError('flat') || hasError('house_number')" v-cloak>
                                        {{errorText('flat')}}{{errorText('house_number')}}
                                    </div>

                                </div>
                            </div>


                            <div class="form__row more_margin">
                                <input class="form__input form__input--short ajax_arg" :readonly="!info.is_active" type="text" placeholder="Номер телефона" v-model="appeal.phone" @click="flushError('phone');" name="phone" onFocus="$(this).formatPnoneNumber(); if (this.value == '') this.value = '+7';">
                                <input class="form__input form__input--short ajax_arg" :readonly="!info.is_active" type="text" placeholder="e-mail" v-model="appeal.email" @change="checkOnChange('email');"  @click="flushError('email');" name="email">
                                <div class="form__comment more_margin" v-show="!(hasError('phone') || hasError('email'))">
                                    Для верификации и уточнений
                                </div>

                                <div class="form__error more_margin" v-show="hasError('phone') || hasError('email')" v-cloak>
                                    {{errorText('phone')}}{{errorText('email')}}
                                </div>
                            </div>


                        </div>
                        <div class="petition__text">
                            <div class="" id="petition__text">
                                <strong v-bind:contenteditable="isEditable()" v-if="current().type == 'initial'" class="whom_header_petition"><?=nl2br($petitionInfo['appeal_title'])?></strong>




                                <div class="text_link edit_text" @click="makeEditable()" v-if="info.is_appeal_editable">Редактировать текст</div>


                                <div class="petition__textblock appeal-text-main" v-bind:contenteditable="isEditable()" v-if="current().type == 'initial'">

                                    <?php

                                        print pdPetitions::formatParagraph($petitionInfo['appeal_text']);


                                    ?>

                                </div>


                                <div class="petition__date" v-show="readonly == false">Дата: <?=date('d')?> <?=gdHandlerSkeleton::getHumanDate(time(), ['month_only'])?> <?=date('Y')?>, подпись</div>
                            </div>

                            <div class="form__row" v-show="readonly == false">
                                <div class="sign_wrap">
                                    <canvas id="sign_sketch" class="withOpacity" width="340" height="125"></canvas>
                                    <div class="sketch-line"></div>
                                    <div class="text_link" onclick="podpishi.clearSketch();">Очистить</div>
                                </div>
                                <div class="form__comment">
                                    <p>
                                        {{comments.signatureBlock}}

                                    </p>
                                </div>
                            </div>


                        </div>

                        <?php

                        if (!empty($subscribersList)){

                            ?>

                            <div class="form__row" style="text-align: left;">

                                <h3>Подписи под манифестом от:</h3>

                                <ol>


                                    <?php

                                        foreach ($subscribersList as $_line) {

                                            print '<li>' . $_line . '</li>';

                                        }

                                    ?>


                                </ol>

                            </div>

                            <?php

                        }

                        if (!empty($subscribesNg_groupped)) {

                            ?>

                            <div class="form__row" style="text-align: left;">

                                <h3>Подписи под обращением от:</h3>

                                <ol>


                                    <?php

                                    if (isset($subscribesNg_groupped['Депутат от Яблока']))
                                        foreach ($subscribesNg_groupped['Депутат от Яблока'] as $_line) {

                                            print '<li>' . $_line . '</li>';

                                        }

                                    if (isset($subscribesNg_groupped['Член Яблока']))
                                        foreach ($subscribesNg_groupped['Член Яблока'] as $_line) {

                                            print '<li>' . $_line . '</li>';

                                        }

                                    if (isset($subscribesNg_groupped['Сторонник партии']))
                                        foreach ($subscribesNg_groupped['Сторонник партии'] as $_line) {

                                            print '<li>' . $_line . '</li>';

                                        }

                                    ?>


                                </ol>

                            </div>

                            <?php
                        }
                        ?>

                    </div>
                </div>
                <div class="wrap" v-if="readonly == false">
                    <div class="petition__send" style="position: relative;">
                        <button class="button send__button send__button--yellow" @click="saveAppeal();">{{texts['saveAppealBtn']}}</button>
                        <div class="button-comment" v-if="current().type == 'initial'">
                            <?=$petitionInfo['onsave_descr'];?>
                        </div>

                    </div>
                </div>

            </div>
        </form>
    </div>


    <div class="line"></div>
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
    <a href="" name="map" id="map"></a>

    <div id="map-container" style="height: 610px;">

    </div>

    <div class="footer" style1="display: none;">
        <div class="wrapper">

            <div class="container">

                <div class="row">

                    <div class="col-12">
                        Политика обработки персональных данных и обеспечения безопасности при их обработке через сайт podpishi.org доступна по ссылке <a href="https://podpishi.org/policy" target="_blank">podpishi.org/policy</a>
                    </div>

                </div>

            </div>


        </div>
    </div>

    <div class="modal_wrap none_added" style="display: none;">
        <div class="modal">
            <div class="modal__text">

                <?=$petitionInfo['onsave_popuptext'];?>

                <div class="sharings-block">
                    <div class="">
                        <div class="likely">
                            <div class="twitter">Твитнуть</div>
                            <div class="facebook">Поделиться</div>
                            <div class="vkontakte">Поделиться</div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="modal__close">
                <div class="text_link" onclick="$(this).closest(&#39;.modal_wrap&#39;).fadeOut();">Закрыть</div>
            </div>
        </div>
    </div>
</div>
</body>

</html>

<script src="/static/libs/mailcheckjs/mailchekcjs.js"></script>

<script>


    if (document.location.href.includes('sofia_sepega'))
        document.location.href = 'https://podpishi.org/sofia_sapega';


    podpishi = {

        is_sending: false,

        init: function(){

            podpishi.sketchInit();

        },

        bindings: function(){


        },

        sketchInit: function(){

            var canvas = document.getElementById('sign_sketch');

            podpishi.signaturePad = new SignaturePad(canvas, {
                maxWidth: 1.5,
                minWidth: 0.2,
                penColor: "#000080",

                onBegin: function(){

                    $('#sign_sketch').removeClass('withOpacity');

                },
            });

        },

        clearSketch: function(){

            podpishi.signaturePad.clear();

            $('#sign_sketch').addClass('withOpacity');

        },

    };


    fAppeal = new Vue({
        el: '#vue-sign',

        data: {

            appeal: {

                full_name: '',
                city: 'Москва',
                street_name: '',
                house_number: '',
                flat: '',
                phone: '',
                email: '',

                appeal_text: '',
                region_name: '',

                signature: '',

                ng_apple_role: 'Член Яблока',
                ng_apple_city: '',
                ng_apple_year: '',

            },

            readonly: <?=json_encode($readonly);?>,
            info: <?=json_encode($petitionInfo, true)?>,
            spbmo_regions: <?=json_encode($spbmo_regions);?>,

            texts: {
                saveAppealBtn: 'Подписать петицию',
            },

            destinations: {

                initial: {

                    id: <?=$petitionInfo['id']?>,
                    amount: <?=$amountAppeals?>,
                },


            },

            comments: {

                signatureBlock: 'В этом окне просьба расписаться. Без вашей подписи обращение не будет рассматриваться',

            },

            moscow_regions_displayed: false,
            address_displayed: true,
            ng_deputies_displayed: false,
            spbmo_deputies_displayed: false,


            regions_list: <?=json_encode($regions_list);?>,

            current_dest: 'initial',

            is_editable: false,
            is_sending: false,

            errors: {

                full_name: {
                    yes: false,
                    text: '',
                },

                city: {
                    yes: false,
                    text: '',
                },
                street_name: {
                    yes: false,
                    text: '',
                },
                house_number: {
                    yes: false,
                    text: '',
                },
                flat: {
                    yes: false,
                    text: '',
                },
                phone: {
                    yes: false,
                    text: '',
                },
                email: {
                    yes: false,
                    text: '',
                },
                job_name: {
                    yes: false,
                    text: '',
                },
                reason_name: {
                    yes: false,
                    text: '',
                },

                region_name: {
                    yes: false,
                    text: '',
                },

                ng_apple_role: {
                    yes: false,
                    text: '',
                },

                ng_apple_city: {
                    yes: false,
                    text: '',
                },

                ng_apple_year: {
                    yes: false,
                    text: '',
                },

            }

        },

        mounted: function () {

            this.appeal.city = this.info.petition_city ;

            $(document).ready(function(){

                if (fAppeal.info.is_active == false){

                    mainSite.swal('Петиция неактивна и новые подписи не принимаются, так как сбор был завершён');

                    return ;
                }

                if (fAppeal.info.is_confirmed == false){

                    mainSite.swal('Петиция пока не была активирована');

                    fAppeal.info.is_active = false;

                    return ;

                }




            });


            if (this.info.id == 11 || this.info.id == 12){
                this.appeal.city = '';
            }
        },

        created: function(){

            if (this.info.url == 'bryuhanova2021_all')
                this.address_displayed = false;

            if (this.info.extra_class.search('moscow_regions') !== -1){
                this.moscow_regions_displayed = true;
                this.address_displayed = false;

                this.texts.saveAppealBtn = 'Подписать обращение';

                $('#map-container').hide();
                $('.sharings-block').hide();
            }
            else if (this.info.id == 33){
                this.ng_deputies_displayed = true;
                this.address_displayed = false;

                this.comments.signatureBlock = 'В этом окне просьба расписаться';

                this.texts.saveAppealBtn = 'Подписать обращение';

                $('#map-container').hide();
                $('.sharings-block').hide();
            }
            else if (this.info.id == 34){
                this.spbmo_deputies_displayed = true;
                this.address_displayed = false;

                this.comments.signatureBlock = 'В этом окне просьба расписаться';

                $('#map-container').hide();
                $('.sharings-block').hide();
            }
            else if (this.info.id == 35){

                this.address_displayed = false;

                $('#map-container').hide();
            }
            else {


                window.ydMapZoom = 10 ;
                window.ydMapLocation = [55.76, 37.64];

                if (this.info.id == 11 || this.info.id == 12){
                    window.ydMapLocation = [57.18417531829109, 54.70154152624503];
                    window.ydMapZoom = 5 ;

                }
                else if (this.info.petition_city == 'Санкт-Петербург') {
                    window.ydMapLocation = [59.945359, 30.276963];
                    window.ydMapZoom = 11 ;
                }
                else if (this.info.petition_city == 'Москва') {
                    window.ydMapLocation = [55.76, 37.64];
                    window.ydMapZoom = 10 ;
                }
                else if (this.info.id == 104) {
                    window.ydMapLocation = [55.76, 37.64];
                    window.ydMapZoom = 5 ;
                }
                else if (this.info.petition_city == 'Брянск') {

                    if (this.info.center_lat == '')
                        window.ydMapLocation = [53.295955, 34.314340];

                    window.ydMapZoom = 11 ;
                }
                else {
                    window.ydMapLocation = [this.info.center_lat, this.info.center_lng];
                    window.ydMapZoom = 12 ;
                }

                if (this.info.id == 104) {
                    window.ydMapLocation = [53.51897495111963, 52.05193642402455];
                    window.ydMapZoom = 5 ;
                }


                $(document).ready(function(){
                    mapInit();
                });

            }

        },

        watch: {

            'appeal.full_name': function(full_name){



            },

            'appeal.ng_apple_role': function(){



            },
        },

        computed: {

            appeals_amountSkl: function(){


                return niceEnding(this.current().amount, ['подписал', 'подписало', 'подписали']);

            },

            appeals_amountSkl2: function(){

                return niceEnding(this.current().amount, ['человек', 'человека', 'человек']);

            },

        },

        methods: {

            isSignatureValid: function(){


                var c = document.getElementById("sign_sketch");
                var ctx = c.getContext("2d");

                var imageData = ctx.getImageData(0, 0, c.width, c.height);

                var totalPixels = 0 ;
                var whitePixels = 0;

                for (var i = 0; i < imageData.data.length; i += 4) {

                    var rgb = [imageData.data[i], imageData.data[i + 1], imageData.data[i + 2]];

                    if (rgb[0] == 0 && rgb[1] == 0 && rgb[2] == 0)
                        whitePixels++ ;

                    totalPixels++;

                }

                var percent = (whitePixels / totalPixels * 100);

                console.log(totalPixels + ' / ' + whitePixels + ' (' + percent  + '%)');

                if (percent >= 99.4)
                    return false;

                return true ;

            },

            checkOnChange: function(target){

                var component = this;

                if (target == 'full_name'){

                    full_name = component.appeal[target] ;

                    var name_parts = full_name.split(' ');

                    if (name_parts.length < 2){
                        component.addError('full_name', 'Вы забыли указать имя или фамилию');
                        return ;
                    }

                    if (name_parts[0].length < 3 || name_parts[1].length < 3 || full_name.indexOf('.') != -1){
                        component.addError('full_name', 'Инициалы недопустимы в официальном обращении, укажите полностью ФИО');
                        return ;
                    }


                }

                if (target == 'email'){

                    Mailcheck.run({
                        email: component.appeal.email,
                        domains: ["gmail.com","mail.ru","yandex.ru","bk.ru","list.ru","rambler.ru","inbox.ru","ya.ru","icloud.com","yahoo.com","hotmail.com","me.com","outlook.com","live.ru","mail.com","yandex.com","narod.ru","ro.ru","phystech.edu","lenta.ru","live.com","protonmail.com","ngs.ru","pm.me","edu.hse.ru"],
                        topLevelDomains:["com","com.au","com.tw","ca","co.nz","co.uk","de","fr","it","ru","net","org","edu","gov","jp","nl","kr","se","eu","ie","co.il","us","at","be","dk","hk","es","gr","ch","no","cz","in","net","net.au","info","biz","mil","co.jp","sg","hu","uk", "ml"],
                        suggested: function(suggestion) {

                            component.addError('email', 'Возможно, вы имели в виду ' + suggestion.full + '');


                        },
                        empty: function() {

                            component.flushError('email');

                        }
                    });

                }

            },

            errorText: function(desired){

                if (!this.hasError(desired))
                    return '';

                return this.errors[desired].text ;

            },


            hasErrorClass: function(desired){

                var classes = {};

                if (this.hasError(desired)){
                    classes['is-invalid'] = true ;
                }

                return classes ;

            },

            hasError: function(desired){

                if (this.errors[desired].yes)
                    return true ;

                return false ;

            },

            flushError: function(desired){

                this.errors[desired].yes = false ;

            },

            addError: function(desired, text){

                this.errors[desired].yes = true ;
                this.errors[desired].text = text ;

            },

            saveAppeal: function(){

                if (this.info.is_active == false){

                    return ;

                    mainSite.swal('Кампания была завершена ранее, поэтому оставить подпись невозможно');
                    return ;
                }

                if (podpishi.signaturePad.isEmpty() || this.isSignatureValid() == false){
                    mainSite.swal('Вероятно, вы забыли оставить свою подпись');
                    return ;
                }

                if (this.appeal.house_number.length > 15){

                    var html = 'Вы точно не вписали ничего лишнего в поле с номером дома?<br/><br/>Улица: ' + this.appeal.street_name + '<br/>Дом:' + this.appeal.house_number + '<br/>Квартира:' + this.appeal.flat;

                    html = '<div style="text-align: left;">' + html + '</div>';

                    mainSite.swal(html, 'Упс!');
                    return ;
                }

                if (this.is_sending)
                    return ;

                this.is_sending = true ;

                mainSite.swalLoading.enable();

                var data = {};

                data.sign = podpishi.signaturePad.toDataURL();

                data.form = this.appeal;
                data.destination = this.current();
                data.appeal_title = $('.whom_header_petition').text();
                data.appeal_text = $('.appeal-text-main:visible').text();

                smartAjax('/ajax/ajax_appeal.php?context=save__appeal', data, function(msg){

                    mainSite.swalLoading.disable();

                    console.log(msg);

                    fAppeal.showSentMessage();

                    setTimeout(function(){

                        likely.initiate();

                    }, 500);

                    fAppeal.is_sending = false;

                }, function(msg){

                    mainSite.waveScroll($('#vue-sign').offset().top - 25, undefined, 250);


                    if (msg.hasOwnProperty('field')) {
                        fAppeal.addError(msg.field, msg.error_text);
                        mainSite.swalLoading.disable();
                    }
                    else {
                        mainSite.swal(msg.error_text);
                    }

                    fAppeal.is_sending = false;

                }, 'saveAppeal', 'POST');

            },

            showSentMessage: function(){

                this.is_sent = true;

                $('.none_added').fadeIn(500);

                this.current().amount++;


            },

            current: function(){

                this.destinations[this.current_dest]['type'] = this.current_dest;

                return this.destinations[this.current_dest];
            },

            isEditable: function(){

                return this.is_editable ;

            },

            makeEditable: function(){

                mainSite.waveScroll($('.appeal-text-main:visible').first().offset().top - 50, undefined, 250);

                this.is_editable = true;

            },


        },

    });


    window.mapObjects = <?=json_encode($mapObjects);?>;

    mapInit = function(){

        var myMap;

        // Waiting for the API to load and DOM to be ready.
        ymaps.ready(init);

        function init () {
            /**
             * Creating an instance of the map and binding it to the container
             * with the specified ID ("map").
             */
            window.myMap = new ymaps.Map('map-container', {
                /**
                 * When initializing the map, you must specify
                 * its center and the zoom factor.
                 */
                center: window.ydMapLocation, // Moscow
                controls: ['zoomControl'],
                zoom: window.ydMapZoom
            }, {
                searchControlProvider: 'yandex#search'
            });

            ymaps.option.presetStorage.add('islands#vnIsland', {
                iconLayout: 'default#imageWithContent',
                iconImageHref: '/static/podpishi/v3/images/Ellipse_clusterer@2x.png',
                iconImageSize: [50, 50],
                iconImageOffset: [-20, 0],

                hideIconOnBalloonOpen: false,
                clusterIcons: [

                    {
                        href: "/static/podpishi/v3/images/Ellipse_clusterer@2x.png",
                        size: [30, 30],
                        offset: [-20, -20],
                        shape: {
                            type: 'Circle',
                            coordinates: [0, 0],
                            radius: 20
                        }
                    },

                    {
                        href: "/static/podpishi/v3/images/Ellipse_clusterer@2x.png",
                        size: [40, 40],
                        offset: [-20, -20],
                        shape: {
                            type: 'Circle',
                            coordinates: [0, 0],
                            radius: 20
                        }
                    },

                    {
                        href: "/static/podpishi/v3/images/Ellipse_clusterer@2x.png",
                        size: [50, 50],
                        offset: [-20, -20],
                        shape: {
                            type: 'Circle',
                            coordinates: [0, 0],
                            radius: 20
                        }
                    },

                ]
            });

            window.clusterer = new ymaps.Clusterer({
                preset: 'islands#vnIsland',
                groupByCoordinates: false,
                clusterDisableClickZoom: true,
                clusterHideIconOnBalloonOpen: false,
                geoObjectHideIconOnBalloonOpen: false,
                gridSize: 60,
                maxZoom: 13,
                hasBalloon: false,
            });


            window.myMap.behaviors.disable('scrollZoom');

            var geoObjects = [];


            $.each(window.mapObjects, function(key, item){

                var position = [
                    parseFloat(item.lat),
                    parseFloat(item.lng),
                ];

                myPlacemark = new ymaps.Placemark(position, {}, {

                    iconLayout: 'default#image',
                    // Custom image for the placemark icon.
                    iconImageHref: '/static/podpishi/v3/images/Ellipse 1@2x.png',
                    // The size of the placemark.
                    iconImageSize: [14, 14],
                    iconImageOffset: [0, 0],
                });

                geoObjects.push(myPlacemark);

            });

            window.clusterer.add(geoObjects);
            window.myMap.geoObjects.add(window.clusterer);



        }

    };



    $(document).ready(function(){

        podpishi.init();

    });
</script>

<?php

    print $petitionInfo['html'] ;

?>


<script src="https://api-maps.yandex.ru/2.1/?lang=en_RU&apikey=9e89ef7e-7675-4324-ba7b-5f2c2fcc91fa" type="text/javascript"></script>

<link rel="stylesheet" type="text/css" href="/static/libs/sweetalert2/sweetalert2.min.css">
<script src="/static/libs/sweetalert2/sweetalert2.min.js"></script>

<script src="/static/libs/signature_pad/signature_pad.min.js"></script>


