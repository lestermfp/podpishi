<?php

?>


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
                            <div class="col-6">

                                <!-- Title -->
                                <h2 class="header-title">
                                    Аккаунты
                                </h2>

                                <!-- Title -->
                                <h5 class="">
                                    Количество: {{accounts.length}}
                                </h5>

                            </div>
                            <div class="col-6 text-right">


                                <a href="/cabinet/editaccount/new">
                                    <div class="btn btn-primary btn-xs">Новый аккаунт</div>
                                </a>


                            </div>
                        </div> <!-- / .row -->
                    </div>
                </div>

                <!-- Form -->
                <form class="mb-12">

                    <table class="table">
                        <thead>
                        <tr>
                            <th scope="col"></th>
                            <th scope="col">id</th>
                            <th scope="col">ФИО</th>
                            <th scope="col">Телефон</th>
                            <th scope="col">Email</th>
                            <th scope="col">Роль</th>
                            <th scope="col">Последняя активность</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="(item, user_index) in accounts">

                            <th><a :href="'/cabinet/editaccount/' + item.id" target="_blank"><i class="fe fe-edit"></i></a></th>
                            <th>{{item.id}}
                            </th>
                            <td>

                                <small>{{item.name}} {{item.surname}} {{item.middlename}}</small>

                                <br/>

                                <img width="16" height="16" :src="getOauth(item.oauth_from).icon_url" :title="getOauth(item.oauth_from).title">

                                <small class="text-muted">{{item.petition_city}}</small>

                            </td>
                            <td><small>{{item.phone}}</small></td>
                            <td><small>{{item.email}}</small></td>
                            <td>

                                <div v-show="!item.is_editable">

                                    <span v-show="item.role == 'admin'">администратор</span>
                                    <span v-show="item.role == 'coordinator'">куратор города</span>
                                    <span v-show="item.role == 'settler'">заявитель</span>
                                    <span v-show="item.role == 'guest'">нет прав</span>

                                    <a href="#" onclick="return false;" target="_blank" v-if="item.id != window.mainSite.profile.id"><i @click="toggleEdit(user_index)" class="fe fe-edit"></i></a>


                                </div>



                                <span v-show="item.is_editable">

                                <select v-model="item.role" class="form-control" v-show="item.is_editable">

                                    <option value="admin">Администратор</option>
                                    <option value="coordinator">Куратор города</option>
                                    <option value="settler">Заявитель</option>
                                    <option value="guest">Нет прав</option>
                                    
                                </select>

                                <br/>

                                <div class="btn btn-primary btn-small" v-show="item.is_editable" @click="updateRole(item);">Сохранить</div>


                                </span>

                            </td>
                            <td>
                                <small>{{item.last_activity_h}}</small>
                            </td>

                        </tr>

                        </tbody>
                    </table>


                </form>

            </div>
        </div> <!-- / .row -->
    </div>

</div> <!-- / .main-content -->

<script>

    vuePage = new Vue({
        el: '#vue-page',

        data: {

            accounts: [],

            oauth_collection: <?=json_encode($_CFG['oauth_collection']); ?>,

        },

        mounted: function () {


        },

        methods: {

            init: function(){

                mainSite.swalLoading.enable();

                this.loadAccounts();

            },

            getOauth: function(oauth_from){

                var output = {
                    icon_url: 'https://podpishi.org/static/podpishi/favicon.ico',
                    title: 'podpishi.org',
                };

                if (oauth_from != '')
                    if (this.oauth_collection.hasOwnProperty(oauth_from))
                        output = this.oauth_collection[oauth_from];

                return output ;

            },

            toggleEdit: function(index){

                if (this.accounts[index].is_editable){
                    this.accounts[index].is_editable = false;
                }
                else {
                    this.accounts[index].is_editable = true;
                }

            },

            updateRole: function(user){

                var data = {};

                data.context = 'update__accountRole';

                data.role = user.role;
                data.user_id = user.id;

                user.is_editable = false ;

                smartAjax('/ajax/ajax_petition_admin.php?context=update__accountRole', data, function(msg){



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

            loadAccounts: function(){

                var data = {};

                data.context = 'get_accounts';

                smartAjax('/ajax/ajax_petition_admin.php?context=get_accounts', data, function(msg){

                    mainSite.swalLoading.disable();

                    vuePage.accounts = msg.accounts ;

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