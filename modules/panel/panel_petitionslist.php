<?php

$petitions = pdPetitions::getListForUser($_USER->getMainUser());

?>


<!-- MAIN CONTENT
================================================== -->
<div class="main-content" id="vue-page">



    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-10 col-xl-10">

                <!-- Header -->
                <div class="header mt-md-5">
                    <div class="header-body">
                        <div class="row align-items-center">
                            <div class="col">

                                <!-- Title -->
                                <h2 class="header-title">
                                    Список кампаний
                                </h2>

                                <a href="" onclick="return false;" @click="isEditable = true;" v-if="isAdmin() && !isEditable">включить управление</a>

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
                            <th scope="col">Адрес</th>
                            <th scope="col">Название</th>
                            <th scope="col">Владелец</th>
                            <th scope="col">Подписанты</th>
                            <th scope="col">Активна</th>
                            <th scope="col" v-if="isAdmin()">Эпрув админом</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="item in petitions">

                            <th><a :href="'/cabinet/petitionedit/' + item.id" target="_blank"><i class="fe fe-edit"></i></a></th>
                            <th> <a :href="item.abs_url" target="_blank">{{item.url}}</a></th>
                            <td>{{item.title}}
                                <br/>
                                <small class="text-muted">{{item.petition_city}}</small>

                            </td>
                            <td>{{item.owner.name}} {{item.owner.surname}}</td>
                            <td>{{item.people}}

                                <a :href="'/cabinet/appeals/' + item.id" target="_blank"><i class="fe fe-list"></i></a>

                                <br/>
                                <a target="_blank" :href="'/printappeals/' + item.id" target="_blank"><i class="fe fe-printer"></i></a>


                                <a target="_blank" :href="'/printappeals/' + item.id + '/?page=' + (pitem_index + 1)" v-for="(page_item, pitem_index) in getAppealsPagesArray(item.people)"><sup>{{pitem_index + 1}}</sup></a>

                                <br/>
                                <sup>- реестр</sup>
                                <br/>

                                <a target="_blank" :href="'/printappeals/' + item.id + '/?listonly'" target="_blank"><i class="fe fe-printer"></i></a>

                                <a target="_blank" :href="'/printappeals/' + item.id + '/?listonly&page=' + (pitem_index + 1)" v-for="(page_item, pitem_index) in getAppealsPagesArray(item.people)"><sup>{{pitem_index + 1}}</sup></a>

                                <template v-if="item.domain == 'yabloko'">
                                    <br/>
                                    <sup><a :href="'/cabinet/statistics/' + item.id"><i class="fe fe-activity"></i> статистика</a></sup>
                                </template>


                            </td>
                            <td>
                                <span v-show="item.is_active">да</span>
                                <span v-show="!item.is_active">нет</span>
                            </td>
                            <td v-if="isAdmin()">
                                <span v-show="item.is_confirmed == 1">да

                                    <a v-if="isEditable" href="#" @click="saveConfirmation(item, 0);" onclick="return false;">отключить</a>

                                </span>
                                <span v-show="item.is_confirmed == 0">нет

                                    <a v-if="isEditable" href="#" @click="saveConfirmation(item, 1);" onclick="return false;">включить</a>

                                </span>
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

            petitions: <?=json_encode($petitions);?>,

            isEditable: false,

        },

        mounted: function () {

        },


        methods: {

            getAppealsPagesArray: function(amount){

                var items = [];

                var pages = Math.ceil(parseInt(amount) / 500) ;

                for (var i = 0; i < pages; i++){

                    items.push(i);

                }


                return items ;
            },

            isAdmin: function(){


                return (mainSite.profile.role == 'admin');

            },


        },

    });

    <?php

    if ($_USER->hasRole('admin')){
    ?>

    vuePage.saveConfirmation = function (item, status) {

        var data = {};

        data.context = 'save__petitionConfirmed';

        data.campaign_id = item.id;
        data.status = status ;

        item.is_confirmed = 1 ;

        mainSite.swalLoading.enable();

        smartAjax('/ajax/ajax_petition_admin.php?context=save__petitionConfirmed', data, function(msg){

            mainSite.swal('Saved');

        }, function(msg){

            mainSite.swalLoading.disable();


        }, 'save__petitionConfirmed', 'GET');

    };


    <?php
    }
    ?>



    pPanel.page = {

        initForm: function(){

        },

        bindings: function(){

        },

    };

</script>