<?php

$campaigns = db_campaigns_list::getIndexList(0, 25, 'yabloko');
$is_more_campaigns_btn = (count($campaigns) > 6);
$is_more_campaigns_btn = false;
//$campaigns = array_slice($campaigns, 0, 6);

?>

<main class="page-main">
    <section class="hero">
        <div class="container">
            <!--h1 class="hero__title">Активные петиции</h1-->
            <div class="hero__text">
                <p>Мы, команда Псковского &laquo;Яблока&raquo;, создаём петиции (общественные обращения) по&nbsp;важным для жителей вопросам и&nbsp;предлагаем гражданам поддержать их&nbsp;своей подписью. На&nbsp;странице петиции, которую вы&nbsp;поддерживаете, вы&nbsp;оставляете свои личные данные. После сбора подписей граждан мы&nbsp;печатаем коллективные обращения и&nbsp;передаем их&nbsp;адресатам в&nbsp;органы власти, а&nbsp;вас информируем о&nbsp;ходе и&nbsp;результатах рассмотрения петиции.</p>
                <p>
                    Начать перемены и&nbsp;влиять на&nbsp;жизнь родного края может каждый из&nbsp;нас. Участвуйте в&nbsp;жизни Псковской области! Это наша земля, нам здесь жить.</p>
            </div>
        </div>
    </section>
    <section class="petitions-list" id="vue-petitions">
        <div class="container">
            <div class="row">



                <div class="col-12 col-md-6 col-xl-4" v-for="campaign in campaigns" v-cloak>
                    <a :href="'/' + campaign.url" class="petition-card">
                        <div class="petition-card__image">
                            <img :src="campaign.image_preview_url" alt="">
                        </div>
                        <div class="petition-card__content">
                            <h3 class="petition-card__title">{{campaign.title}}</h3>
                            <div class="petition-card__text" v-html="'<p>' + campaign.subtitle_descr + '</p>'">

                            </div>
                            <p class="petition-card__count">
                                <b>{{window.mainSite.format_money(campaign.appeals_amount)}}</b>
                                <span>{{campaign.appeals_amount_skl}}</span>
                            </p>
                        </div>
                    </a>
                </div>





            </div>
            <!--div class="row justify-content-center" v-if="is_more_campaigns_btn">
                <div class="col-12 col-md-6 col-xl-4">
                    <a href="" @click.prevent="loadMore()" class="btn btn_primary petitions-list__toggle">Ещё петиции</a>
                </div>
            </div-->
        </div>
    </section>
</main>

<script>

    podpishimain = new Vue({
        el: '#vue-petitions',

        data: {

            campaigns: <?=json_encode($campaigns)?>,

            is_more_campaigns_btn: <?=json_encode($is_more_campaigns_btn)?>,

        },

        methods: {

            loadMore: function(){

                $('.petitions-list__toggle').addClass('animate__animated animate__heartBeat');

                var component = this ;

                var data = {};

                data.context = 'list_petitions';
                data.offset = component.campaigns.length ;

                smartAjax('/ajax/ajax_appeal.php', data, function(msg){

                    setTimeout(function(){

                        for (var i = 0; i < msg.campaigns.length; i++)
                            component.campaigns.push(msg.campaigns[i]);

                        if (!msg.has_more)
                            component.is_more_campaigns_btn = false;

                        $('.petitions-list__toggle').removeClass('animate__animated animate__heartBeat');

                    }, 1500);





                }, function(msg){



                }, data.context);

            },

        },
    });



</script>