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
                                    Авторы кампаний
                                </h2>

                                <!-- Title -->
                                <h5 class="">
                                    Количество: {{authors.length}}
                                </h5>

                            </div>
                            <div class="col-6 text-right">


                                <a href="/cabinet/editauthor/new">
                                    <div class="btn btn-primary btn-xs">Новый автор</div>
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
                            <th scope="col">Кем создан</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="(item, user_index) in authors">

                            <td><a :href="'/cabinet/editauthor/' + item.id" target="_blank"><i class="fe fe-edit"></i></a></td>
                            <td>

                                {{item.id}}
                            </td>
                            <td>

                                <img width="32" height="32" style="border-radius: 50% ; float: left; margin-right: 5px;" :src="item.avatar_url">

                                <small>{{item.surname}} {{item.name}} {{item.middlename}}</small>

                                <br/>



                            </td>
                            <td><small>{{item.phone}}</small></td>
                            <td><small>{{item.email}}</small></td>

                            <td>
                                <small v-if="item.created_by_id > 0">{{item.created_by.surname}} {{item.created_by.name}} (id#{{item.created_by.id}})</small>
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

            authors: [],

        },

        mounted: function () {


        },

        methods: {

            init: function(){

                mainSite.swalLoading.enable();

                this.loadAuthors();

            },


            loadAuthors: function(){

                var data = {};

                data.context = 'list_authors';

                smartAjax('/ajax/ajax_petition_admin.php?context=list_authors', data, function(msg){

                    mainSite.swalLoading.disable();

                    vuePage.authors = msg.authors ;

                }, function(msg){

                    mainSite.swalLoading.disable();

                    if (msg.hasOwnProperty('field')) {
                        vueProject.addError(msg.field, msg.error_text);
                    }
                    else {
                        mainSite.swal(msg.error_text);
                    }


                }, 'list_authors', 'GET');

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