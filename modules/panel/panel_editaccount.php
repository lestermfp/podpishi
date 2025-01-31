

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
                                    Город
                                </label>
                                <input class="form-control" maxlength="100" v-model="user.petition_city">

                            </div>

                            <div class="col-6">

                                <label>
                                    Роль
                                </label>


                                <select v-model="user.role" class="form-control">

                                    <option value="admin">Администратор</option>
                                    <option value="coordinator">Куратор города</option>
                                    <option value="settler">Заявитель</option>
                                    <option value="guest">Нет прав</option>

                                </select>

                            </div>



                        </div>


                    </div>

                    <!-- Project name -->
                    <div class="form-group">

                        <div class="row">

                            <div class="col-12">

                                <label>
                                    Пароль <small class="text-muted">(можно задать новый)</small>
                                </label>
                                <input class="form-control" maxlength="100" v-model="user.password">

                            </div>




                        </div>


                    </div>

                    <!-- Divider -->
                    <hr class="mt-5 mb-5">

                    <!-- Buttons -->
                    <a href="#" class="btn btn-block btn-primary" onclick="return false;" @click="saveUser();">
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

            pageTitle: 'Новый аккаунт',

            user: {
                id: 'new',
                name: '',
                surname: '',
                middlename: '',
                phone: '',
                email: '',
                role: 'guest',
                password: '',
                petition_city: '',

            },


        },

        mounted: function () {


        },

        watch: {

            'user.id': function(user_id){

                if (this.user.id == 'new'){
                    this.pageTitle = 'Новый аккаунт';
                }
                else {
                    this.pageTitle = 'Редактирование аккаунта';
                }

                if (user_id == 'new' || user_id == '')
                    return ;

                this.loadUserById(user_id);

            },

        },

        methods: {

            init: function(){

                var pathparts = document.location.pathname.split('/');

                if (pathparts.length == 4)
                    this.user.id = pathparts[3] ;
            },


            loadUserById: function(user_id){

                var data = {};

                data.context = 'get__userById';

                data.user_id = user_id;

                mainSite.swalLoading.enable();

                smartAjax('/ajax/ajax_petition_admin.php?context=get__userById', data, function(msg){

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

            saveUser: function(){

                var data = {};

                data.context = 'save__user';

                data.form = this.user ;

                mainSite.swalLoading.enable();

                smartAjax('/ajax/ajax_petition_admin.php?context=save__user', data, function(msg){

                    //mainSite.swalLoading.disable();

                    document.location.href = '/cabinet/editaccount/' + msg.user.id ;


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