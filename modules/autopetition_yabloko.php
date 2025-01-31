<?php

ini_set('display_errors', true);
ini_set('error_reporting',  E_ALL);
error_reporting(E_ALL + E_STRICT);

if (!$petitionInfo->isHostAppropriate($_SERVER['HTTP_HOST']))
    redirect('/');

$petitionItem = $petitionInfo;

$petitionItem->setUrlOpenedBy($possible_petiton);
$petitionInfo = $petitionItem->getFullInfo(['url_key' => $possible_petiton]);

//print '<pre>' . print_r($petitionInfo, true) . '</pre>';
//exit();

$_CFG['meta'][$module_name]['og_url'] = $petitionInfo['page_url'];
$_CFG['meta'][$module_name]['og_title'] = $petitionInfo['meta_title'];
$_CFG['meta'][$module_name]['title'] = $petitionInfo['meta_title'];
$_CFG['meta'][$module_name]['og_description'] = $petitionInfo['meta_description'];
$_CFG['meta'][$module_name]['og_image'] = $petitionInfo['meta_image'];

include($_CFG['root'] . $_CFG['headers']['yabloko']['header']);

?>

<style>

    [v-cloak] {
        opacity: 0;
    }

    .letter-form__error {
        color: red;
        padding-left: 10px;
    }

    .form-radio input[type="radio"]{
        appearance: radio !important;
        -webkit-appearance: radio !important;
        -moz-appearance: radio !important;

        margin-right: 5px;
        width: 16px;
        height: 16px;
        margin-left: 5px;
    }

    #policy-agree {
        width: 16px;
        height: 16px;
        appearance: checkbox;
        -webkit-appearance: checkbox;
    }

    .letter-section__warning {
        cursor: pointer;
    }

    .link-style {
        color: #0056b3;
        text-decoration: underline;
    }

</style>

<main class="page-main" id="vue-page">
    <div class="page-container">
        <section class="petition-section">
            <h2 class="petition-section__title" v-html="campaign.title" v-cloak></h2>
            <div class="petition-section__date" v-html="campaign.publish_date_h" v-cloak></div>

            <div>
                <div class="petition-info d-block d-md-none mb-4">
                    <div class="petition-info__total">
                        <b v-html="campaign.appeals_amount"></b>
                        <span class="desktop-only" v-html="appeals_amountSkl2 + ' ' + appeals_amountSkl">человек подписал</span>
                        <span class="mobile-only" v-html="appeals_amountSkl">подписали</span>
                    </div>
                    <a href="#letter" onclick="mainSite.waveScroll($('#letter-head').offset().top, undefined, 250); return false;" class="btn btn_secondary">К петиции</a>
                </div>
            </div>

            <div class="petition-section__wrapper">
                <div class="petition-section__main">

                    <div class="petition-section__article petition-article">
                        <figure class="petition-article__media">
                            <img :src="campaign.meta_image" alt="">
                            <!--figcaption>Фото: </figcaption-->
                        </figure>
                        <span v-html="'<p>' + campaign.subtitle_title + '</p>'"></span>
                        <h3 v-html="campaign.subtitle_descr"></h3>
                        <figure class="petition-article__media" v-if="campaign.youtube_vid != ''">

                            <iframe width="100%" height="340" :src="'https://www.youtube.com/embed/' + campaign.youtube_vid" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>

                            <!--figcaption>Видео: <a href="www.youtube.com/user/PskovYablokoTV/videos" target="_blank">www.youtube.com/user/PskovYablokoTV/videos</a></figcaption!-->
                        </figure>
                    </div>
                    <div class="petition-section__letter" id="letter-head">
                        <div class="letter-section">
                            <form action="#" method="post" class="letter-section__form">
                                <div class="letter-section__overlay letter">
                                    <div class="letter__head">
                                        <div class="row">
                                            <div class="col-12 col-sm-6 col-xl-7">
                                                <h2 class="letter__headline">Обращение</h2>
                                            </div>
                                            <div class="col-12 col-sm-6 col-xl-5">
                                                <div class="letter-form">
                                                    <p  class="letter-form__text" v-html="campaign.whom"></p>
                                                    <fieldset class="letter-form__fieldset">
                                                        <label class="letter-form__field-label" for="name-field"></label>
                                                        <div class="letter-form__name">
                                                            <input class="letter-form__field" type="text" id="name-field" name="name" v-model="appeal.surname" autocomplete="new-password" placeholder="Спегальский*" @change="onSurnameChanged">
                                                            <input class="letter-form__field" type="text" id="lastname-field" name="lastname" v-model="appeal.name" autocomplete="new-password" placeholder="Юрий*">
                                                            <input class="letter-form__field" type="text" id="secondname-field" name="secondname" v-model="appeal.middlename" autocomplete="new-password" placeholder="Павлович">
                                                        </div>
                                                        <span class="letter-form__note">Отчество по&nbsp;желанию</span>
                                                    </fieldset>
                                                    <fieldset class="letter-form__fieldset">
                                                        <label class="letter-form__field-label" for="place-field">проживающий(ая):</label>

                                                        <fieldset class="form-radio">

                                                            <label><input name="dzen" type="radio" value="Псков" v-model="city_type"> город Псков</label>
                                                            <label><input name="dzen" type="radio" value="Великие Луки" v-model="city_type"> город Великие Луки</label>
                                                            <label><input name="dzen" type="radio" value="Псковская область" v-model="city_type"> Псковская область</label>
                                                            <label><input name="dzen" type="radio" value="other_russia_region" v-model="city_type"> Другой регион РФ</label>
                                                            <label><input name="dzen" type="radio" value="abroad_russia" v-model="city_type"> Не Россия</label>

                                                        </fieldset>

                                                        <select class="form-cont1rol letter-form__field" v-model="tmp.rn_name" v-show="city_type == 'Псковская область'">
                                                            <!--option value="Псков">Псков</option-->
                                                            <option value="choose">Выберите район из списка</option>
                                                            <option :value="rn_name" v-for="rn_name in pskov_rn_list">{{rn_name}}</option>
                                                        </select>

                                                        <select class="form-cont1rol letter-form__field" v-model="appeal.region_name" v-if="city_type == 'other_russia_region'" v-cloak>
                                                            <option :value="region_name" v-for="region_name in federal_regions">{{region_name}}</option>
                                                        </select>

                                                        <template v-if="appeal.region_name != 'none' || appeal.rn_name != 'none'">

                                                            <template v-if="(appeal.city != 'Псков' && appeal.city != 'Великие Луки') && ((appeal.city != 'Москва' && appeal.city != 'Санкт-Петербург') || (city_type != 'Псковская область' && (appeal.region_name != 'Москва' && appeal.region_name != 'Санкт-Петербург')))">

                                                                <input class="letter-form__field" type="text" id="place-field" name="place" v-model="appeal.city" placeholder="Населённый пункт*"  autocomplete="new-password">
                                                            </template>

                                                            <template v-if="is_fulladdress_asking">

                                                                <input class="letter-form__field" type="text" id="street-field" name="street" v-model="appeal.street_name" placeholder="Улица"  autocomplete="new-password">

                                                                <div class="row">
                                                                    <div class="col-6 col-sm-12 col-md-6">
                                                                        <input class="letter-form__field" type="text" id="building-field" name="building" v-model="appeal.house_number" placeholder="Дом/корпус"  autocomplete="new-password">
                                                                    </div>
                                                                    <div class="col-6 col-sm-12 col-md-6">
                                                                        <input class="letter-form__field" type="text" id="apartment-field" name="apartment"  v-model="appeal.flat" placeholder="Квартира"  autocomplete="new-password">
                                                                    </div>
                                                                </div>

                                                            </template>

                                                            <div class="text-right" v-else>

                                                                <a href="" @click.prevent="is_fulladdress_asking = true;"><small>+ полный адрес по желанию<br/>(не обязательно)</small></a>

                                                            </div>

                                                        </template>

                                                        <!--span class="letter-form__note">Адрес обязателен</span-->
                                                    </fieldset>
                                                    <fieldset class="letter-form__fieldset">
                                                        <input class="letter-form__field" type="tel" id="tel-field" name="tel" placeholder="Телефон*" onFocus="$(this).formatPnoneNumber(); if (this.value == '') this.value = '+7';">
                                                        <input class="letter-form__field" type="email" id="email-field" name="email" placeholder="E-mail*" v-model="appeal.email">
                                                        <span class="letter-form__note">* Для верификации и&nbsp;уточнений</span>
                                                    </fieldset>

                                                    <div class="letter-form__error" v-if="hasError('full_name') || hasError('email') || hasError('phone')|| hasError('city')|| hasError('street_name')|| hasError('house_number') || hasError('flat')" v-cloak>
                                                        {{errorText('full_name')}}{{errorText('email')}}{{errorText('phone')}}{{errorText('city')}}{{errorText('street_name')}}{{errorText('house_number')}}{{errorText('flat')}}
                                                    </div>
                                                    <div class="text-info" v-else-if="isMainInputsFilled()" v-cloak>
                                                        <p class="text-dark" v-if="appeal.city != ''"><span class=""><b>!</b> Ваш город:</span><br/>{{appeal.city}} <small v-if="appeal.region_name != appeal.city && appeal.region_name != 'none'">({{appeal.region_name}})</small> <span v-if="appeal.rn_name != 'none'">{{appeal.rn_name}}</span></p>
                                                        <p>✓ Вы заполнили личные данные, но ваша подпись ещё не поставлена и обращение не отправлено</p>
                                                        <div>
                                                            <a href="#letter-sign" onclick="mainSite.waveScroll($('#letter-date').offset().top, undefined, 1000); return false;" class="btn btn_secondary">Перейти к подписанию</a>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="letter__body" id="letter">
                                        <div class="row">
                                            <div class="col-12">
                                                <h3 v-html="campaign.appeal_title"></h3>

                                                <?php

                                                    print pdPetitions::formatParagraph($petitionInfo['appeal_text']);

                                                ?>

                                            </div>
                                        </div>
                                    </div>
                                    <div class="row" id="letter-date">
                                        <div class="col-12 col-sm-4"></div>
                                        <div class="col-12 col-sm-8">
                                            <div class="letter-form">
                                                <fieldset class="letter-form__fieldset" style="text-align: right;">
                                                    <p class="letter-form__text" v-show="!campaign.is_readonly">Дата: <?=date('d')?> <?=gdHandlerSkeleton::getHumanDate(time(), ['month_only'])?> <?=date('Y')?></p>
                                                </fieldset>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="letter-is_sent">

                                    <div class="letter-section__overlay" v-if="is_sent">

                                        <div class="letter__head">

                                            <div class="col-12">

                                                <h2>Спасибо! Ваша подпись поставлена</h2>

                                                <p>
                                                    <?=nl2br($petitionInfo['onsave_popuptext']);?>
                                                </p>

                                                <p>
                                                    <a href="https://donate.shlosberg.ru/" target="_blank" class="link-style">Поддержите</a> команду Псковского «Яблока», это усилит наши возможности в работе для граждан России. Спасибо!
                                                </p>

                                            </div>

                                        </div>

                                    </div>

                                </div>

                                <div class="letter-section__submit" v-if="!is_sent">
                                    <div class="row">
                                        <div class="col-12 col-sm-6"></div>
                                        <div class="col-12 col-sm-6">
                                            <button class="btn btn_secondary" @click.prevent="saveAppeal();" type="submit">Подписать</button>
                                            <p class="letter-section__warning">
                                                <input type="checkbox" id="policy-agree" v-model="appeal.policy_confirm">
                                                <span @click.self="appeal.policy_confirm = !appeal.policy_confirm;">Я даю согласие Псковскому региональному отделению Политической партии «Российская объединенная демократическая партия «ЯБЛОКО», на обработку моих персональных данных в объеме и на условиях, определенных <a href="https://pskov.yabloko.ru/ppd/" target="_blank">Политикой</a>.</span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="petition-section__share">
                        <div class="share-section">
                            <div class="share-block share-block_nested">
                                <h2 class="share-block__title">Расскажите о&nbsp;петиции в&nbsp;соцсетях</h2>
                                <div class="share-block__content">
                                    <div class="likely">
                                        <!--div class="facebook" tabindex="0" role="link" aria-label="Поделиться на Фейсбуке"></div-->
                                        <div class="twitter"tabindex="0" role="link" aria-label="Поделиться в Твиттере"></div>
                                        <div class="vkontakte" tabindex="0" role="link" aria-label="Поделиться ВКонтакте"></div>
                                        <div class="odnoklassniki" tabindex="0" role="link" aria-label="Поделиться в Одноклассниках"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="petition-section__aside">
                    <div class="petition-section__floating-block">
                        <div class="petition-section__info petition-section__info_floating">
                            <div class="petition-info">
                                <div class="petition-info__total">
                                    <b v-html="campaign.appeals_amount"></b>
                                    <span class="desktop-only" v-html="appeals_amountSkl2 + ' ' + appeals_amountSkl">человек подписал</span>
                                    <span class="mobile-only" v-html="appeals_amountSkl">подписали</span>
                                </div>
                                <a href="#letter" onclick="mainSite.waveScroll($('#letter').offset().top, undefined, 250); return false;" class="btn btn_secondary">К петиции</a>
                            </div>
                        </div>
                        <div class="petition-section__info petition-section__info_author" v-cloak v-if="campaign.author_exists">
                            <div class="petition-author">
                                <h3 class="petition-author__headline" v-if="campaign.authors.length == 1">Автор петиции</h3>
                                <h3 class="petition-author__headline" v-else>Авторы петиции</h3>

                                <template v-for="author in campaign.authors">

                                    <div class="petition-author__header">
                                        <div class="petition-author__photo">
                                            <img :src="author.avatar_url" alt="">
                                        </div>
                                        <h4 class="petition-author__name">{{author.surname}} <br>{{author.name}} {{author.middlename}}</h4>
                                    </div>
                                    <div class="petition-author__desc" v-html="'<p>' + author.description + '</p>'">

                                    </div>
                                    <div class="petition-author__contacts">
                                        <h4>Контакты</h4>
                                        <a :href="'tel:+' + author.phone" class="petition-author__tel">{{author.phone_formatted}}</a>
                                        <br/>
                                        <a :href="'mailto:' + author.email" class="petition-author__link">{{author.email}}</a>
                                    </div>

                                    <div class="petition-author__social mb-3">
                                        <div class="social-block">

                                            <?php

                                            include_once($_CFG['root'] . 'modules/templates/social_block_svg_yabloko.php');
                                            ?>


                                        </div>
                                    </div>

                                </template>


                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>


    <script src="/static/libs/mailcheckjs/mailchekcjs.js"></script>

    <script>

        fAppeal = new Vue({
            el: '#vue-page',

            data: {

                appeal: {

                    surname: '',
                    name: '',
                    middlename: '',

                    full_name: '',
                    rn_name: 'none',
                    city: 'Псков',
                    street_name: '',
                    house_number: '',
                    flat: '',
                    phone: '',
                    email: '',

                    appeal_text: '',
                    region_name: 'Псковская область',

                    signature: '',

                    policy_confirm: false,

                },

                pskov_rn_list: ["Бежаницкий район","Великолукский район","Гдовский район","Дедовичский район","Дновский район","Красногородский район","Куньинский район","Локнянский район","Невельский район","Новоржевский район","Новосокольнический район","Опочецкий район","Островский район","Палкинский район","Печорский район","Плюсский район","Порховский район","Псковский район","Пустошкинский район","Пушкиногорский район","Пыталовский район","Себежский район","Струго-Красненский район","Усвятский район"],

                city_type: 'Псков',

                tmp: {
                    rn_name: 'Псков',
                },


                federal_regions: <?=json_encode(db_federal_regions::getListArray())?>,

                campaign: <?=json_encode($petitionInfo, true)?>,

                texts: {
                    saveAppealBtn: 'Подписать петицию',
                },

                is_editable: false,
                is_sending: false,
                is_fulladdress_asking: false,
                is_sent: false,

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


                }

            },

            mounted: function () {

                $(document).ready(function(){

                    if (fAppeal.campaign.is_active == false){

                        mainSite.swal('Петиция неактивна и новые подписи не принимаются, так как сбор был завершён');

                        return ;
                    }

                    if (fAppeal.campaign.is_confirmed == false){

                        mainSite.swal('Петиция пока не была активирована');

                        fAppeal.campaign.is_active = false;

                        return ;

                    }




                });

            },

            created: function(){

                if (document.location.href.includes('is_sent_mode'))
                    this.is_sent = true;

            },

            watch: {

                'appeal.city': function(value){

                    if (value == '')
                        return ;


                },

                'tmp.rn_name': function(value){

                    if (this.city_type != 'Псковская область')
                        return ;

                    this.appeal.region_name = 'Псковская область';

                    if (typeof(value) != "undefined" && value.includes(' район')){
                        this.appeal.city = '';
                        this.appeal.rn_name = value;
                    }
                    else {

                        this.appeal.city = value;
                        this.appeal.rn_name = '';

                    }

                },

                'city_type': function(value){

                    if (value == '')
                        return ;

                    if (value == 'Псков') {
                        this.appeal.city = 'Псков';
                        this.appeal.region_name = 'Псковская область';
                        this.appeal.rn_name = '';
                    }

                    if (value == 'Великие Луки') {
                        this.appeal.city = 'Великие Луки';
                        this.appeal.region_name = 'Псковская область';
                        this.appeal.rn_name = '';
                    }

                    if (value == 'Псковская область') {
                        this.appeal.city = '';
                        this.appeal.region_name = 'Псковская область';
                        this.appeal.rn_name = 'Бежаницкий район';
                        this.tmp.rn_name = 'Бежаницкий район';
                    }

                    if (value == 'other_russia_region') {
                        this.appeal.city = '';
                        this.appeal.region_name = 'Москва';
                        this.appeal.rn_name = 'none';

                        this.$nextTick(function () {
                            $('#street-field').focus();
                        });

                    }

                    if (value == 'abroad_russia') {
                        this.appeal.city = '';
                        this.appeal.region_name = 'не РФ';
                        this.appeal.rn_name = 'none';

                        this.$nextTick(function () {
                            $('#place-field').focus();
                        });

                    }


                },

                'appeal.rn_name': function(value){

                    if (value == '')
                        return ;

                    if (value != 'Псков' && value != 'Великие Луки') {
                        this.appeal.city = '';
                    }


                },

                'appeal.region_name': function(value){

                    if (value == 'Псковская область'){
                        this.city_type = 'Псковская область';
                        return ;
                    }


                    if (value == 'Москва' || value == 'Санкт-Петербург'){

                        this.appeal.city = value;

                        return ;
                    }
                    else {

                        this.appeal.city = '';

                        this.$nextTick(function () {
                            $('#place-field').focus();
                        });

                    }

                    this.appeal.rn_name = 'none';


                },

                'appeal.surname': function(value){

                    this.checkOnChange('full_name');

                },

                'appeal.name': function(value){

                    this.checkOnChange('full_name');

                },

                'appeal.email': function(value){

                    this.checkOnChange('email');

                },


            },

            computed: {

                appeals_amountSkl: function(){


                    return niceEnding(this.campaign.appeals_amount, ['подписал', 'подписало', 'подписали']);

                },

                appeals_amountSkl2: function(){

                    return niceEnding(this.campaign.appeals_amount, ['человек', 'человека', 'человек']);

                },

            },

            methods: {

                isMainInputsFilled: function(){

                    if (this.appeal.name == '') return false;
                    if (this.appeal.surname == '') return false;
                    //if (this.appeal.phone.length < 8) return false;
                    if (!this.appeal.email.includes('@')) return false;

                    return true;


                },

                onSurnameChanged: function(){

                    var surname_string = this.appeal.surname ;

                    if (surname_string == '')
                        return ;

                    //alert(this.appeal.name + ' = ' + this.appeal.middlename);

                    if (this.appeal.name != '' || this.appeal.middlename != '' )
                        return ;

                    var parts = $.trim(surname_string).split(' ');

                    if (parts.length != 3)
                        return ;

                    this.appeal.surname = parts[0];
                    this.appeal.name = parts[1];
                    this.appeal.middlename = parts[2];

                },

                showSuccessPopup: function(){

                    mainSite.waveScroll($('.letter-is_sent').offset().top, undefined, 250);

                },

                getSocialInfo: function(item) {

                    var icons = ['facebook', 'instagram', 'ok', 'twitter', 'vk', 'youtube'];

                    item = item.replace('http://', '');
                    item = item.replace('https://', '');
                    item = item.replace('www.', '');

                    item = item.replace('m.vk.com', 'vk.com');
                    item = item.replace('fb.com', 'facebook.com');

                    var output = {
                        icon: '',
                        url: '',
                    };

                    if (/^@[a-zA-Z0-9\_]{5,32}/g.test(item)){ // telegram @username

                        output['icon'] = 'tg';
                        output['url'] = 'tg://resolve?domain=' + item ;

                        return output;
                    }

                    if (item.search('.com') === -1 && item.search('.ru') === -1) return item;

                    for (var i in icons){

                        var regex = new RegExp(icons[i], 'i');

                        if (item.search(regex) === 0){

                            output['icon'] = icons[i];
                            output['url'] = 'http://' + item ;

                            return output;

                        }
                    }


                    output['icon'] = 'facebook';
                    output['url'] = 'http://' + item ;

                    return output;

                },

                getOnsaveDescr: function(){

                    var string = this.campaign.onsave_descr ;

                    string = string.replace('публичной оферты', '<a target="_blank" href="/zanazemku/offer"><a target="_blank" href="/zanazemku/offer">публичной оферты</a>');

                    return string ;

                },

                checkOnChange: function(target){

                    var component = this;

                    if (target == 'full_name'){

                        component.appeal.name = $.trim(component.appeal.name);
                        component.appeal.surname = $.trim(component.appeal.surname);

                        if (component.appeal.name == '' || component.appeal.surname == ''){
                            component.addError('full_name', 'Укажите имя и фамилию, пожалуйста');
                            return ;
                        }

                        if (component.appeal.name.length < 3 || (component.appeal.middlename + component.appeal.name + component.appeal.surname).indexOf('.') != -1){
                            component.addError('full_name', 'Инициалы недопустимы в официальном обращении, укажите полностью ФИО');
                            return ;
                        }

                        component.flushError('full_name');


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

                    if (this.campaign.is_active == false){

                        return ;

                        mainSite.swal('Кампания была завершена ранее, поэтому оставить подпись невозможно');
                        return ;
                    }

                    if (this.appeal.policy_confirm == false){

                        alertify.alert('Уважение к персональным данным', 'Вы должны дать согласие на обработку персональных данных');

                        return ;
                    }


                    if (this.city_type == 'other')
                        if (this.appeal.rn_name == 'none'){
                            mainSite.swal('Пожалуйста, выберите название района из списка');
                            return;
                        }

                    if (this.appeal.house_number.length > 15){

                        var html = 'Вы точно не вписали ничего лишнего в поле с номером дома?<br/><br/>Улица: ' + this.appeal.street_name + '<br/>Дом:' + this.appeal.house_number + '<br/>Квартира:' + this.appeal.flat;

                        html = '<div style="text-align: left;">' + html + '</div>';

                        mainSite.swal(html, 'Упс!');
                        return ;
                    }

                    if (this.appeal.middlename === undefined)
                        this.appeal.middlename = '';

                    if (this.appeal.name === undefined || this.appeal.name == ''){
                        mainSite.swal('Укажите обязательно имя, пожалуйста');
                        return;
                    }

                    if (this.appeal.surname === undefined || this.appeal.surname == ''){
                        mainSite.swal('Укажите обязательно фамилию, пожалуйста');
                        return;
                    }

                    if (this.is_sending)
                        return ;

                    this.is_sending = true ;

                    mainSite.swalLoading.enable();

                    var data = {};

                    this.appeal.full_name = this.appeal.surname + ' ' + this.appeal.name + ' ' + this.appeal.middlename;
                    this.appeal.phone = $('#tel-field').val();

                    data.form = this.appeal;
                    data.destination = this.campaign;
                    data.appeal_title = this.campaign.appeal_title ;
                    data.appeal_text = this.campaign.appeal_text ;

                    smartAjax('/ajax/ajax_appeal.php?context=save__appeal', data, function(msg){

                        mainSite.swalLoading.disable();

                        console.log(msg);

                        fAppeal.showSentMessage();

                        setTimeout(function(){

                            likely.initiate();

                        }, 500);

                        fAppeal.is_sending = false;

                    }, function(msg){
                        
                            //mainSite.swal(msg.error_text);

                        swal.close();
                        alertify.alert('Упс', msg.error_text).set({onshow:null, onclose:function(){

                            if (msg.error != 'policy_confirm')
                            mainSite.waveScroll($('.letter__headline').offset().top - 25, undefined, 250);

                        }});


                        fAppeal.is_sending = false;

                    }, 'saveAppeal', 'POST');

                },

                showSentMessage: function(){

                    this.is_sent = true;

                    this.showSuccessPopup();

                    this.campaign.appeals_amount++;


                },

                isEditable: function(){

                    return this.is_editable ;

                },

            },

        });


        $(document).ready(function(){

        });
    </script>

    <script src="/static/libs/alertify/alertify.min.js"></script>

    <!-- CSS -->
    <link rel="stylesheet" href="/static/libs/alertify/alertify.min.css"/>

<?php
include($_CFG['root'] . $_CFG['headers']['yabloko']['footer']);
?>