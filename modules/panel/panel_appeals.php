<?php

$campaignItem = db_campaigns_list::find_by_id($_GET['parts'][3], [
    'select' => 'id, title, owner_id, domain, petition_city',
]);

$campaign = new pdPetitions($campaignItem);

if (!$campaign->hasPrevi())
    redirect('/panel');


$campaignInfo = $campaignItem->to_array();
$campaignInfo['appeals_amount'] = $campaignItem->getAppealsAmount();

?>

<style>

    small small {
        font-size: 90%;
    }

    .phone-tag {
        white-space: nowrap;
    }

    textarea.editable-mode, input.editable-mode {
        font-size: 12px;
        padding: 4px;
        min-width: 110px;
    }

    textarea.editable-mode {
        min-height: 75px;

    }

</style>


<!-- MAIN CONTENT
================================================== -->
<div class="main-content" id="vue-page">



    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-12 col-xl-12">

                <!-- Header -->
                <div class="header mt-md-5">
                    <div class="header-body">
                        <div class="row align-items-center">
                            <div class="col">

                                <!-- Title -->
                                <h2 class="header-title">
                                    Подписанты «{{campaign.title}}»
                                </h2>

                                <!-- Title -->
                                <h5 class="">
                                    Всего подписантов: {{campaign.appeals_amount}}
                                </h5>

                                <div class="row">

                                    <div class="col-12 col-md-6">

                                        <p>Подписавшие петицию граждане порой допускают ошибки в написании ФИО либо E-mail адреса. Предлагаем удобно проверить их руками.<br/>Для этого можно <a href="" @click.prevent="state.modeEditable = !state.modeEditable;">активировать режим правок <span v-if="state.modeEditable">✅</span></a></p>

                                        <p>Также вы можете показать в списке только тех подписантов,<br/> которые ещё <a href="" @click.prevent="state.modeIsNotCheckedOnly = !state.modeIsNotCheckedOnly;">️не проверялись ранее <span v-if="state.modeIsNotCheckedOnly">✅</span></a></p>

                                        <p>Показать подписантов <a href="" @click.prevent="state.modeEmailInvalid = !state.modeEmailInvalid;">с опечатками в E-mail <span v-if="state.modeEmailInvalid">✅</span></a></p>

                                        <p>Если же вы хотите удалить кого-то из подписантов, то можете<br/><a href="" @click.prevent="state.modeRemovable = !state.modeRemovable;">активировать режим удаления <span v-if="state.modeRemovable">✅</span></a></p>

                                    </div>

                                    <div class="col-12 col-md-6">

                                        <div class="btn btn-block btn-primary" v-cloak v-if="campaign.domain == 'yabloko'" @click="generateExport">Export as Excel</div>

                                    </div>

                                </div>



                            </div>
                        </div> <!-- / .row -->
                    </div>
                </div>

                <!-- Form -->
                <form class="mb-12">

                    <table class="table">
                        <thead>
                        <tr>
                            <th scope="col">ФИО</th>
                            <th scope="col" v-if="is_contacts_allowed">Телефон</th>
                            <th scope="col" v-if="is_contacts_allowed">Email</th>
                            <th scope="col">Город</th>
                            <th scope="col">Адрес</th>
                            <th scope="col">Дата</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="item in appeals" v-if="inFilter(item)">

                            <td>

                                <a v-if="state.modeRemovable" href="" onclick="return false;" target="_blank" @click="removeAppeal(item);"><i class="fe fe-trash"></i></a>

                                <template v-if="isEditable">
                                    <textarea type="text" v-model="item.full_name" class="form-control editable-mode"></textarea>
                                </template>
                                <small v-else>{{item.full_name}}</small>

                                <div class="btn btn-block btn-primary btn-sm" v-if="isEditable" @click.prevent="saveEditedAppeal(item)">сохранить изменения <span v-if="item.is_saved">✅</span></div>

                            </td>
                            <td  v-if="is_contacts_allowed">

                                <template v-if="isEditable">
                                    <input type="text" v-model="item.phone" class="form-control editable-mode"></input>
                                </template>

                                <small class="phone-tag" v-else>{{item.phone}}</small></td>
                            <td  v-if="is_contacts_allowed">

                                <template v-if="isEditable">
                                    <input type="text" v-model="item.email" class="form-control editable-mode"></input>
                                </template>

                                <small v-else>{{item.email}}</small>

                                <small v-if="item.is_email_valid === false"><br/>❗️ возможно опечатка</small>

                            </td>
                            <td>

                                <template v-if="isEditable">
                                    <input type="text" v-model="item.city" class="form-control editable-mode">
                                    <br/>
                                    <input type="text" v-model="item.rn_name" v-if="item.rn_name != ''" class="form-control editable-mode">
                                </template>
                                <template v-else>

                                    <small>{{item.city}}</small>

                                    <div v-if="item.rn_name != ''">
                                        <small style="color: gray;">
                                            <small>{{item.rn_name}}</small>
                                        </small>
                                    </div>

                                </template>



                            </td>
                            <td>

                                <template v-if="isEditable">
                                    <small>улица</small>
                                    <input type="text" v-model="item.street_name" class="form-control editable-mode">

                                    <small>дом</small>
                                    <input type="text" v-model="item.house_number" class="form-control editable-mode">

                                    <small>кв.</small>
                                    <input type="text" v-model="item.flat" class="form-control editable-mode">
                                </template>
                                <small v-else>{{item.street_name}}, {{item.house_number}} {{item.flat}}</small>
                            </td>
                            <td>
                                <small>{{item.date_h}}<br/>{{item.time_h}}</small>
                            </td>

                        </tr>

                        </tbody>
                    </table>


                </form>

            </div>
        </div> <!-- / .row -->
    </div>

</div> <!-- / .main-content -->

<script src="/static/libs/mailcheckjs/mailchekcjs.js"></script>

<script>

    vuePage = new Vue({
        el: '#vue-page',

        data: {

            campaign: <?=json_encode($campaignInfo);?>,

            appeals: [],

            state: {
                modeRemovable: false,
                modeEditable: false,
                modeIsNotCheckedOnly: false,
                modeEmailInvalid: false,
            },

            is_contacts_allowed: true,


        },

        computed: {

            modeEmailInvalid: function(){

                return this.state.modeEmailInvalid ;

            },

            modeIsNotCheckedOnly: function(){

                return this.state.modeIsNotCheckedOnly ;

            },

            isEditable: function(){

                return this.state.modeEditable ;

            },

        },

        mounted: function () {

            if (mainSite.profile.extra_class.includes('no_contacts;'))
                this.is_contacts_allowed = false;

        },


        methods: {

            generateExport: function(){

                var component = this;

                var data = {};

                data.context = 'generate__xslxExport';

                data.campaign_id = this.campaign.id;

                mainSite.swalLoading.enable();

                smartAjax('/ajax/ajax_petition_admin.php?context=' + data.context, data, function(msg){

                    mainSite.swal('<a href="/downloadExport/' + msg.filename + '" target="_blank">Загрузить файл</a>');

                }, function(msg){

                    mainSite.swal(msg.error_text);


                }, undefined, 'POST');

            },

            inFilter: function(appeal){

                if (this.modeIsNotCheckedOnly)
                    if (appeal.is_checked)
                        return false ;

                if (this.modeEmailInvalid)
                    if (appeal.is_email_valid)
                        return false ;



                return true;

            },

            saveEditedAppeal: function(appeal){


                var component = this;

                var data = {};

                data.context = 'save__editedAppeal';

                data.appeal = appeal;


                smartAjax('/ajax/ajax_petition_admin.php?context=' + data.context, data, function(msg){

                    appeal.is_saved = true;
                    appeal.is_checked = true;


                }, function(msg){

                    mainSite.swal(msg.error_text);


                }, 'save__editedAppeal' + appeal.id, 'POST');


            },

            init: function(){

                mainSite.swalLoading.enable();

                this.loadPeople();

            },

            removeAppeal: function(appeal){

                var data = {};

                data.context = 'remove__appeal';

                data.appeal_id = appeal.id;

                var newAppeals = [];

                $.each(this.appeals, function(key, item){

                    if (item.id == appeal.id)
                        return ;

                    newAppeals.push(item);

                });

                this.appeals = newAppeals ;


                smartAjax('/ajax/ajax_petition_admin.php?context=remove__appeal', data, function(msg){

                    mainSite.swalLoading.disable();



                }, function(msg){

                    mainSite.swalLoading.disable();

                    if (msg.hasOwnProperty('field')) {
                        vueProject.addError(msg.field, msg.error_text);
                    }
                    else {
                        mainSite.swal(msg.error_text);
                    }


                }, 'save__project', 'GET');

            },

            applyEmailValidation: function(){

                var component = this ;

                $.each(this.appeals, function(key, appeal){

                    Mailcheck.run({
                        email: appeal.email,
                        domains: ["gmail.com","mail.ru","yandex.ru","bk.ru","list.ru","rambler.ru","inbox.ru","ya.ru","icloud.com","yahoo.com","hotmail.com","me.com","outlook.com","live.ru","mail.com","yandex.com","narod.ru","ro.ru","phystech.edu","lenta.ru","live.com","protonmail.com","ngs.ru","pm.me","edu.hse.ru"],                       // optional
                        suggested: function(suggestion) {
                            appeal.is_email_valid = false;
                        },
                        empty: function() {
                            appeal.is_email_valid = true;

                        }
                    });

                });

            },

            loadPeople: function(){

                var component = this;

                var data = {};

                data.context = 'get__appeals';

                data.campaign_id = this.campaign.id;


                smartAjax('/ajax/ajax_petition_admin.php?context=get__appeals', data, function(msg){

                    mainSite.swalLoading.disable();

                    $.each(msg.appeals, function(key, appeal){

                        appeal.is_email_valid = null;
                        appeal.is_saved = false;

                    });

                    component.appeals = msg.appeals ;

                    component.applyEmailValidation();

                }, function(msg){

                    mainSite.swalLoading.disable();

                    if (msg.hasOwnProperty('field')) {
                        vueProject.addError(msg.field, msg.error_text);
                    }
                    else {
                        mainSite.swal(msg.error_text);
                    }


                }, 'save__project', 'GET');

            },


        },

    });



    pPanel.page = {

        initForm: function(){

            vuePage.init();

        },

        bindings: function(){

        },

    };

</script>