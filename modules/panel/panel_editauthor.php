<style>

    .union-close {
        position: absolute;
        font-weight: bold;
        font-size: 18px;
        right: 20px;
        top: 8px;
        cursor: pointer;
        background: white;
        border-radius: 50%;
    }

    .row-socials-custom {
        position: relative;
    }

</style>

<!-- MAIN CONTENT
================================================== -->
<div class="main-content" id="vue-user">



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
                                    {{pageTitle}}
                                </h2>

                                <!-- Title -->
                                <h4 class="text-primary" v-if="user.id != 'new'">
                                    <br/>
                                    {{user.name}} {{user.surname}} {{user.middlename}}
                                </h4>



                            </div>
                        </div> <!-- / .row -->
                    </div>
                </div>

                <!-- Form -->
                <form class="mb-4">

                    <!-- Project name -->
                    <div class="form-group">

                        <div class="row">

                            <div class="col-4">

                                <label>
                                    Фамилия
                                </label>
                                <input class="form-control" maxlength="100" v-model="user.surname">

                            </div>

                            <div class="col-4">

                                <label>
                                    Имя
                                </label>
                                <input class="form-control" maxlength="100" v-model="user.name">

                            </div>

                            <div class="col-4">

                                <label>
                                    Отчество
                                </label>
                                <input class="form-control" maxlength="100" v-model="user.middlename">

                            </div>

                        </div>


                    </div>

                    <!-- Project name -->
                    <div class="form-group">

                        <div class="row">

                            <div class="col-6">

                                <label>
                                    Телефон
                                </label>
                                <input class="form-control" maxlength="100" v-model="user.phone">

                            </div>

                            <div class="col-6">

                                <label>
                                    E-mail
                                </label>
                                <input class="form-control" maxlength="100" v-model="user.email">

                            </div>



                        </div>


                    </div>

                    <!-- Project name -->
                    <div class="form-group">

                        <div class="row">

                            <div class="col-6">

                                <label>
                                    Ссылка на аватар
                                </label>


                                <div class="row">

                                    <div class="col-auto">

                                        <img :src="user.avatar_url" width="64" height="64" style="border-radius: 50%;">

                                    </div>

                                    <div class="col">

                                        <input class="form-control" maxlength="255" v-model="user.avatar_url">

                                    </div>

                                </div>

                            </div>

                            <div class="col-6">

                                <label>
                                    Социальные сети
                                </label>


                                <div class="row">
                                    <div class="col-12">
                                        <div class="row row-socials-custom" v-for="(social, index) in user.socials">
                                            <div class="col-12">
                                                <input class="form-control mb-2" type="text" :name="'socials-' + index" v-model="user.socials[index]" maxlength="128" :class="{'attr-not-empty': user.socials[index] != ''}">
                                                <div class="union-close" @click="removeSocialByIndex(index)">✕</div>
                                            </div>
                                        </div>
                                        <hr><a class="link-add-social" href="" onclick="return false;" @click="addSocialLine()">+ Добавить соцсеть</a>
                                    </div>
                                </div>
                            </div>



                        </div>


                    </div>

                    <!-- Project name -->
                    <div class="form-group">

                        <div class="row">

                            <div class="col-12">

                                <label>
                                    Биография
                                </label>
                                <textarea rows=5 class="form-control" maxlength="300" v-model="user.description"></textarea>

                            </div>




                        </div>


                    </div>

                    <!-- Divider -->
                    <hr class="mt-5 mb-5">

                    <!-- Buttons -->
                    <a href="#" class="btn btn-block btn-primary" onclick="return false;" @click="saveAuthor();">
                        Сохранить
                    </a>

                </form>

            </div>
        </div> <!-- / .row -->
    </div>

</div> <!-- / .main-content -->



<script>


    vuePage = new Vue({
        el: '#vue-user',

        data: {

            pageTitle: 'Новый автор',

            user: {
                id: 'new',
                name: '',
                surname: '',
                middlename: '',
                phone: '',
                email: '',
                description: '',
                avatar_url: '',
                socials: [],

            },


        },

        mounted: function () {


        },

        watch: {

            'user.id': function(author_id){

                if (this.user.id == 'new'){
                    this.pageTitle = 'Новый автор';
                }
                else {
                    this.pageTitle = 'Редактирование автора';
                }

                if (author_id == 'new' || author_id == '')
                    return ;

                this.loadAuthorById(author_id);

            },

        },

        methods: {

            removeSocialByIndex: function(index){

                this.user.socials.splice(index, 1);

            },

            addSocialLine: function(){

                for (var i = 0; i < this.user.socials.length; i++)
                    if (this.user.socials[i] == '')
                        return ;

                this.user.socials.push('');

            },

            init: function(){

                var pathparts = document.location.pathname.split('/');

                if (pathparts.length == 4)
                    this.user.id = pathparts[3] ;
            },


            loadAuthorById: function(author_id){

                var data = {};

                data.context = 'get__authorById';

                data.author_id = author_id;

                mainSite.swalLoading.enable();

                smartAjax('/ajax/ajax_petition_admin.php?context=get__authorById', data, function(msg){

                    mainSite.swalLoading.disable();

                    $.each(vuePage.user, function(key, value){

                        if (key == 'id')
                            return ;

                        if (msg.user.hasOwnProperty(key) == false)
                            return ;

                        vuePage.user[key] = msg.user[key] ;

                    });


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

            saveAuthor: function(){

                var data = {};

                data.context = 'save__author';

                data.form = this.user ;

                mainSite.swalLoading.enable();

                smartAjax('/ajax/ajax_petition_admin.php?context=save__author', data, function(msg){

                    //mainSite.swalLoading.disable();

                    document.location.href = '/cabinet/editauthor/' + msg.user.id ;


                }, function(msg){

                    mainSite.swalLoading.disable();

                    if (msg.hasOwnProperty('field')) {
                        vueProject.addError(msg.field, msg.error_text);
                    }
                    else {
                        mainSite.swal(msg.error_text);
                    }


                }, 'save__project', 'POST');

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