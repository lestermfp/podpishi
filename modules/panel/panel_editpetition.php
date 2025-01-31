<style>

    .attaches-list img {
        max-width: 60px;
    }

    .card-header-title {
        font-weight: bold;
        font-size: 20px;
    }

</style>

<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/spectrum-colorpicker2/dist/spectrum.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/spectrum-colorpicker2/dist/spectrum.min.css">

<!-- MAIN CONTENT
================================================== -->
<div class="main-content" id="vue-petition">



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
                                <h4 class="text-primary" v-if="petition.id != 'new'">
                                    <br/>
                                    <a :href="'https://' + petition.domain_name + '/' + petition.url" target="_blank">{{petition.title}}</a>
                                </h4>

                            </div>
                        </div> <!-- / .row -->
                    </div>
                </div>

                <form class="mb-4">

                    <div class="form-group">
                        <label>
                            Ссылка для страницы с кампанией
                        </label>
                        <small class="form-text text-muted">
                            Проект будет доступен по адресу: <b><span>{{petition.domain_name}}</span>/{{petition.url}}</b>
                        </small>

                        <input type="text" class="form-control" maxlength="64" v-model="petition.url">
                    </div>
                </form>

                <!-- Divider -->
                <hr class="mt-4 mb-4">

                <div class="card">
                    <div class="card-header">

                        <!-- Title -->
                        <h4 class="card-header-title" id="nav-name">
                            🚩 Название, подписи и текст петиции
                        </h4>



                    </div>
                    <div class="card-body">

                        <!-- Form -->
                        <form class="mb-4">

                            <!-- Project name -->
                            <div class="form-group">
                                <label>
                                    • Название кампании по сбору
                                </label>
                                <textarea class="form-control" maxlength="200" v-model="petition.title"></textarea>
                            </div>

                            <div class="row">

                                <div class="col-6">

                                    <div class="form-group">
                                        <label>
                                            • Заголовок, обращение к чиновнику/заголовок
                                        </label>
                                        <small class="form-text text-muted">
                                            В заголовке обычно обращаются к чиновнику “Уважаемый ...” или пишут название документа Обращение, Требование”
                                        </small>
                                        <textarea class="form-control" maxlength="200" v-model="petition.appeal_title"></textarea>
                                    </div>

                                </div>

                                <div class="col-6">

                                    <div class="form-group">
                                        <label>
                                            • Город петиции
                                        </label>
                                        <small class="form-text text-muted">
                                            Данный город будет подставлен по умолчанию в петицию, подписант сможет изменить его вручную
                                        </small>
                                        <textarea class="form-control" maxlength="200" v-model="petition.petition_city"></textarea>
                                    </div>

                                </div>

                            </div>


                            <!-- Project description -->
                            <div class="form-group">
                                <label class="mb-1">
                                    • Текст петиции
                                </label>
                                <small class="form-text text-muted">
                                    Этот текст будет предложен для подписания. Постарайтесь уложить петицию в одну страницу
                                </small>
                                <textarea v-model="petition.appeal_text" class="form-control" :rows="getRowsFor(petition.appeal_text, 4, 25)"></textarea>
                            </div>

                            <div class="form-group">
                                <label>
                                    • Кому предназначен?
                                </label>
                                <small class="form-text text-muted">
                                    (например: "Мэру Москвы" и на следующей строчке "Собянину С.С.")
                                </small>

                                <textarea v-model="petition.whom" class="form-control" :rows="getRowsFor(petition.whom, 2)"></textarea>
                            </div>

                            <div class="form-group">
                                <label>
                                    • Почтовый адрес получателя
                                </label>
                                <small class="form-text text-muted">
                                    (например: "Улица Некрасова, 23, Псков, Псковская область, 180001")
                                </small>

                                <textarea v-model="petition.post_address" class="form-control" :rows="getRowsFor(petition.post_address, 2)"></textarea>
                            </div>

                            <div class="form-group">
                                <label>
                                    • Почтовый адрес отправителя
                                </label>
                                <small class="form-text text-muted">
                                    (например: "Улица Пушкина, 23, Гдов, Псковская область, 180001")
                                </small>

                                <textarea v-model="petition.address_reply_full" class="form-control" :rows="getRowsFor(petition.address_reply_full, 2)"></textarea>
                            </div>



                            <div class="form-group">
                                <label>
                                    • Название петиции <span class="badge badge-info">HTML</span>
                                </label>
                                <small class="form-text text-muted">
                                    Будет отображаться в самом верху страницы слева в качестве заголовка <a href="" @click.prevent="showExample('subtitle_title')">(пример)</a>
                                </small>

                                <textarea v-model="petition.subtitle_title" class="form-control" :rows="getRowsFor(petition.subtitle_title, 1, 2)"></textarea>
                            </div>

                            <div class="form-group">
                                <label>
                                    • Описание петиции <span class="badge badge-info">HTML</span>
                                </label>
                                <small class="form-text text-muted">
                                    Будет отображаться в самом верху страницы, сразу под названием и в левой части картинки <a href="" @click.prevent="showExample('subtitle_descr')">(пример)</a>
                                </small>

                                <textarea v-model="petition.subtitle_descr" class="form-control" :rows="getRowsFor(petition.subtitle_descr)"></textarea>
                            </div>
                        </form>

                    </div> <!-- / .card-body -->

                </div>

                <!-- Divider -->
                <hr class="mt-4 mb-4">

                <div class="card">
                    <div class="card-header">

                        <!-- Title -->
                        <h4 class="card-header-title"  id="nav-opengraph">
                            🌐 Опенграф и текст, который виден когда делишься ссылкой
                        </h4>



                    </div>
                    <div class="card-body">

                        <div class="form-group">
                            <label class="mb-1">
                                • Опенграф заголовок
                            </label>
                            <small class="form-text text-muted">
                                Когда вы будете делиться ссылками в соц.сетях, то будет использоваться этот текст <a href="" @click.prevent="showExample('meta_title')">(пример)</a>
                            </small>
                            <input type="text" class="form-control" v-model="petition.meta_title">
                        </div>

                        <div class="form-group">
                            <label class="mb-1">
                                • Опенграф описание
                            </label>
                            <small class="form-text text-muted">
                                Когда вы будете делиться ссылками в соц.сетях, то будет использоваться этот текст <a href="" @click.prevent="showExample('meta_description')">(пример)</a>
                            </small>
                            <textarea class="form-control" v-model="petition.meta_description"></textarea>
                        </div>

                        <div class="form-group">
                            <label class="mb-1">
                                • Опенграф картинка (ссылка)
                            </label>
                            <small class="form-text text-muted">
                                Будет видна, когда вы поделитесь ссылкой в соц.сети <a href="" @click.prevent="showExample('meta_image')">(пример)</a>
                            </small>
                            <input type="text" class="form-control" v-model="petition.meta_image">

                            <div class="row">

                                <div class="col-6">

                                    <br/>
                                    <img :src="petition.meta_image" v-if="petition.meta_image != ''" style="max-width: 100px; max-height: 100px;">

                                </div>


                                <div class="col-12">

                                    <br/>
                                    <b>НУЖНО ЗАГРУЗИТЬ СНАЧАЛА КАРТИНКУ ФАЙЛОМ НИЖЕ</b>

                                </div>

                            </div>

                        </div>

                    </div> <!-- / .card-body -->

                </div>

                <!-- Divider -->
                <hr class="mt-4 mb-4">

                <div class="card">
                    <div class="card-header">

                        <!-- Title -->
                        <h4 class="card-header-title"  id="nav-files">
                            📁 Файлы
                        </h4>



                    </div>
                    <div class="card-body">

                        <form class="mb-4">


                            <div v-if="petition.id == 'new'" class="text-info">
                                Загрузка файлов станет доступна после сохранения формы
                            </div>

                            <div class="col-12 attaches-list" v-show="attaches.length > 0">
                                <div class="card col-12" v-for="attach in attaches">
                                    <div class="row justify-content-around align-items-center project-previewfile">
                                        <div class="col">

                                            <img :src="attach.absLink" alt="" >

                                            <span>{{attach.filename}} <a href="#" @click="removeImageAttach(attach)" onclick="return false;" class="btn cr-btn-primary"><i class="fe fe-delete"></i></a></span>


                                            <br/>

                                            <input :value="'https://' + petition.domain_name + attach.absLink" style="font-size: 12px;" class="form-control">

                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="inline fields" v-if="petition.id != 'new'">
                                <div class="sixteen wide field">
                                    <label for="" @click="startUploading();">
                                        <div class="ui submit green button">Загрузить новый</div>
                                    </label>
                                </div>
                            </div>

                        </form>

                    </div> <!-- / .card-body -->



                </div>

                <!-- Divider -->
                <hr class="mt-4 mb-4" v-if="petition.domain == 'yabloko'">

                <div class="card"  v-if="petition.domain == 'yabloko'">
                    <div class="card-header">

                        <div class="row">

                            <div class="col-6">

                                <!-- Title -->
                                <h4 class="card-header-title"  id="nav-author">
                                    👤 Видео Youtube
                                </h4>

                            </div>

                            <div class="col-6">

                                <!-- Title -->
                                <h4 class="card-header-title" >
                                    👤 Автор петиции
                                </h4>

                            </div>

                        </div>



                    </div>
                    <div class="card-body">

                        <form class="mb-4">

                            <div class="row">

                                <div class="col-6">


                                    <div class="form-group">
                                        <label class="mb-1">
                                            • Ссылка на видео youtube
                                        </label>
                                        <small class="form-text text-muted">
                                            Будет вставлено на страницу с петицией.
                                        </small>
                                        <input type="text" class="form-control" v-model="vars.youtube_url">

                                        <iframe class="mt-3" width="300" height="150" v-if="petition.youtube_vid != ''" :src="'https://www.youtube.com/embed/' + petition.youtube_vid" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>

                                    </div>


                                </div>

                                <div class="col-6">

                                    <div class="form-group">
                                        <label class="mb-1">
                                            • Выберите из списка
                                        </label>
                                        <small class="form-text text-muted">
                                            Вы можете добавить нового <a href="/cabinet/editauthor/new" target="_blank">автора по ссылке</a>
                                        </small>

                                        <div class="mb-3">
                                            <select v-model="state.author_id" class="form-control" @change="addAuthor(state.author_id)">

                                                <option value="0">Не выбран автор</option>
                                                <option :value="author.id" v-for="author in authors_collection">{{author.surname}} {{author.name}} {{author.middlename}}</option>

                                            </select>
                                        </div>

                                        <div class="row mb-1" v-for="author_taken in petition.authors">

                                            <div class="col-8">

                                                {{author_taken.surname}} {{author_taken.name}} {{author_taken.middlename}}
                                                <div><small><a href="" @click.prevent="removeAuthor(author_taken.id)">убрать</a></small></div>

                                            </div>

                                            <div class="col-4 text-right">

                                                <img :src="author_taken.avatar_url" width="64" height="64" style="border-radius: 50%;">

                                            </div>


                                        </div>
                                    </div>

                                </div>

                            </div>

                        </form>

                    </div> <!-- / .card-body -->



                </div>

                <!-- Divider -->
                <hr class="mt-4 mb-4">

                <div class="card">
                    <div class="card-header">

                        <!-- Title -->
                        <h4 class="card-header-title"  id="nav-styles">
                            🎑 Внешний вид и особые стили
                        </h4>



                    </div>
                    <div class="card-body">

                        <form class="mb-4">

                            <div class="form-group">
                                <label class="mb-1">
                                    • Главное изображение (ссылка)
                                </label>
                                <small class="form-text text-muted">
                                    Будет отображено в самом верху страницы. Размер не менее 1280 в ширину и 600 в высоту
                                </small>
                                <input type="text" class="form-control" v-model="petition.title_image">

                                <div class="row">

                                    <div class="col-6">

                                        <br/>
                                        <img :src="petition.title_image" v-if="petition.title_image != ''" style="max-width: 100px; max-height: 100px;">

                                    </div>


                                </div>


                            </div>

                            <div class="form-group">
                                <label>
                                    • Цвет подложки для главной страницы
                                </label>
                                <small class="form-text text-muted">
                                    Нажмите для выбора (либо напишите red или #276cb8)
                                </small>

                                <input type="text"  id="color-picker" class="form-control" :value="petition.var_arrowcolor">

                            </div>

                            <div class="form-group">
                                <label>
                                    • Особые стили или скрипты <span class="badge badge-info">HTML</span>
                                </label>
                                <small class="form-text text-muted">
                                    Здесь вы можете задать любые теги или javascript-код, который будет вставлен непосредственно на страницу (в конец) для более тонкой настройки
                                </small>

                                <textarea v-model="petition.html" class="form-control" :rows="getRowsFor(petition.html, 4, 25)"></textarea>

                            </div>




                        </form>

                    </div> <!-- / .card-body -->



                </div>

                <!-- Divider -->
                <hr class="mt-4 mb-4">

                <div class="card">
                    <div class="card-header">

                        <!-- Title -->
                        <h4 class="card-header-title"  id="nav-buttonsave">
                            ⚙️ Кнопка «Подписать петицию»
                        </h4>



                    </div>
                    <div class="card-body">

                        <form class="mb-4">

                            <div class="form-group" v-if="petition.domain != 'yabloko'">
                                <label>
                                    • Описание возле кнопки "Подписать петицию" <span class="badge badge-info">HTML</span>
                                </label>
                                <small class="form-text text-muted">
                                    В тексте не нужно ничего менять, кроме своего имени, должности и получателя петиции
                                </small>

                                <textarea v-model="petition.onsave_descr" class="form-control" :rows="getRowsFor(petition.onsave_descr)"></textarea>

                            </div>

                            <div class="form-group" v-if="petition.domain != 'yabloko'">
                                <label>
                                    • ФИО ответственного за петицию, а также названия органов власти (с предлогом «в») или должностных лиц, которые будут получателями петиции
                                </label>
                                <small class="form-text text-muted">
                                    Используется для автоматической генерации Оферты
                                </small>

                                <input type="text" class="form-control" maxlength="64" placeholder="ФИО ответственного в именительном падеже" v-model="petition.attorney_name">
                                <br/>
                                <input type="text" class="form-control" maxlength="64" placeholder="ДД.ММ.ГГГГ рождения" v-model="petition.attorney_birthday">
                                <br/>


                                <input type="text" class="form-control" maxlength="255" placeholder="названия органов власти (с предлогом «в»)" v-model="petition.attorney_destination">

                            </div>




                            <div class="form-group">
                                <label>
                                    • Текст для попапа после сохранения <span class="badge badge-info">HTML</span>
                                </label>
                                <small class="form-text text-muted">
                                    Сообщение, которое увидит подписант, когда отправит подпись.
                                </small>

                                <textarea v-model="petition.onsave_popuptext" class="form-control" :rows="getRowsFor(petition.onsave_popuptext)"></textarea>

                            </div>




                        </form>

                    </div> <!-- / .card-body -->



                </div>


                <form class="mb-4" id="nav-saveall">





                    <!-- Divider -->
                    <hr class="mt-4 mb-5">

                    <div class="row">
                        <div class="col-12 col-md-6">

                            <!-- Private project -->
                            <div class="form-group">
                                <label class="mb-1">
                                    • Активна ли кампания
                                </label>
                                <small class="form-text text-muted">
                                    Если нет, то нельзя будет оставлять подписи
                                </small>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" v-model="petition.is_active" id="switchOne">
                                    <label class="custom-control-label" for="switchOne"></label>
                                </div>
                            </div>

                        </div>
                        <div class="col-12 col-md-6">


                            <div class="form-group">
                                <label class="mb-1">
                                    • Редактируемый текст
                                </label>
                                <small class="form-text text-muted">
                                    Если нет, то подписант не сможет изменить текст петиции
                                </small>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" v-model="petition.is_appeal_editable" id="switchOne1">
                                    <label class="custom-control-label" for="switchOne1"></label>
                                </div>
                            </div>

                        </div>
                    </div> <!-- / .row -->


                    <!-- Divider -->
                    <hr class="mt-5 mb-5">

                    <!-- Buttons -->
                    <a href="#" class="btn btn-block btn-primary" onclick="return false;" @click="savePetition();">
                        Сохранить
                    </a>

                </form>

            </div>
        </div> <!-- / .row -->
    </div>

</div> <!-- / .main-content -->

<div class="avatarUpaloder" style="display: none;">
    <form class="FileUploadForm" enctype="multipart/form-data" method="POST" action="" file_callback="prjUpload.onUploadDone">
        <input id="avatar_upload_input" type="file" name="fileupload" class="form__load form__input--hidden" onchange="prjUpload.uploadStart(this);">
    </form>
</div>

<script>


    prjUpload = {

        timeoutId: 0,

        onUploadDone: function(msg){

            vuePetition.reloadFilesList();

            setTimeout(function() {

                mainSite.swalLoading.disable();

            }, 500);

            if (msg.error != 'false'){
                mainSite.postError(msg.error);
                return ;
            }


        },

        uploadStart: function(_this){

            var url = prjUpload.url ;

            fileUploader.beginUploadInner(_this, url);

            mainSite.swalLoading.enable();

        },

    };

    vuePetition = new Vue({
        el: '#vue-petition',

        data: {

            pageTitle: 'Новая кампания',

            petition: {
                id: 'new',
                title: '',
                appeal_title: '',
                appeal_text: '',
                is_active: true,
                is_appeal_editable: false,
                url: '',
                domain_name: '',
                whom: '',
                post_address: '',
                address_reply_full: '',
                onsave_descr: '',
                onsave_popuptext: '',
                var_arrowcolor: '',

                domain: '',
                youtube_vid: '',
                authors: [],

                attorney_name: '',
                attorney_birthday: '',
                attorney_destination: '',
                petition_city: '',

                meta_description: '',
                meta_title: '',
                meta_image: '',

                html: '',

                title_image: '',

                subtitle_descr: '',
                subtitle_title: '',

            },

            state: {
                author_id: '',
            },

            authors_collection: [],

            vars: {
                youtube_url: '',
            },

            defaults: {
                onsave_descr: 'Я даю согласие на обработку персональных данных и поручаю муниципальному депутату <b>мундуп</b> распечатать и подать мое обращение ... на основании публичной оферты',
            },

            attaches: [],

        },

        mounted: function () {

            this.petition.petition_city = mainSite.profile.petition_city;



        },

        watch: {

            'petition.youtube_vid': function(){

                if (this.vars.youtube_url == '')
                    this.vars.youtube_url = 'https://www.youtube.com/watch?v=' + this.petition.youtube_vid;

            },

            'vars.youtube_url': function(youtube_url){

                // extract youtube url

                if (youtube_url.includes('youtu.be')){

                    var urlItem = new URL(youtube_url);

                    this.petition.youtube_vid = urlItem.pathname.replace('/', '') ;

                }

                if (youtube_url.includes('?v=')){

                    var urlItem = new URL(youtube_url);

                    if (typeof(urlItem.searchParams) != "undefined")
                        this.petition.youtube_vid = urlItem.searchParams.get('v');

                }

            },

            'petition.id': function(petition_id){

                if (this.petition.id == 'new'){
                    this.pageTitle = 'Новая кампания';
                }
                else {
                    this.pageTitle = 'Редактирование кампании';
                }

                if (petition_id == 'new' || petition_id == '') {
                    return;
                }

                this.loadPetitionById(petition_id);

            },

        },

        methods: {

            addAuthor: function(author_id){

                var petition = this.petition;

                $.each(this.authors_collection, function(key, author){


                    if (author.id == author_id)
                        petition.authors.push(author);

                });


            },

            removeAuthor: function(author_id){

                var index = -1;

                $.each(this.petition.authors, function(key, author){


                    if (author.id == author_id)
                        index = key;

                });

                if (index > 0)
                    this.petition.authors.splice(index, 1);

            },

            showExample: function(field_name){

                mainSite.swal('<img src="/static/images/img_example/example_' + field_name + '.png" style="width: 100%; height: auto;">');


            },

            init: function(){

                var pathparts = document.location.pathname.split('/');

                if (pathparts.length == 4) {

                    this.petition.id = pathparts[3];

                    if (this.petition.id == 'new')
                        this.petition['onsave_descr'] = this.defaults.onsave_descr;

                }
            },

            removeImageAttach: function(attach){

                var toDeleteAttachId = attach.attach_id ;

                var newAttaches = [];

                $.each(this.attaches, function(key, item){

                    if (attach.attach_id == item.attach_id)
                        return ;

                    newAttaches.push(item);

                });


                this.attaches = newAttaches ;

                var data = {
                    context: 'delete__projectItemAttach',
                    attach_id: toDeleteAttachId
                };

                mainSite.swalLoading.enable();

                smartAjax('/ajax/ajax_attach.php',data,function(msg){

                    mainSite.swalLoading.disable();
                });

            },

            startUploading: function(){

                $('#avatar_upload_input').closest('form').attr('file_callback', "prjUpload.onUploadDone");

                prjUpload.url = '/ajax/ajax_attach.php?context=upload__petitionAttach&connected_id=' + this.petition.id;

                $('#avatar_upload_input').click();

            },

            reloadFilesList: function(){

                var data = {};

                data.context = 'get__campaignById';

                data.campaign_id = this.petition.id;


                smartAjax('/ajax/ajax_petition_admin.php?context=get__campaignById', data, function(msg){

                    vuePetition.attaches = msg.attachList ;


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

            getRowsFor: function(text, min, max){

                if (typeof(max) == "undefined")
                    max = 8;

                if (typeof(min) == "undefined")
                    min = 4;

                var new_lines_amount = (text.split("\n").length - 1);
                
                var rows = new_lines_amount;

                if (rows < min)
                    rows = min ;

                if (rows > max)
                    rows = max ;

                return rows;


            },

            loadPetitionById: function(petition_id, no_swal_if_not_undefined){

                var data = {};

                data.context = 'get__campaignById';

                data.campaign_id = petition_id;

                if (typeof(no_swal_if_not_undefined) == "undefined")
                    mainSite.swalLoading.enable();

                smartAjax('/ajax/ajax_petition_admin.php?context=get__campaignById', data, function(msg){

                    if (typeof(no_swal_if_not_undefined) == "undefined")
                        mainSite.swalLoading.disable();

                    vuePetition.authors_collection = msg.authors_collection;

                    $.each(vuePetition.petition, function(key, value){

                        if (key == 'id')
                            return ;

                        if (msg.campaign.hasOwnProperty(key) == false)
                            return ;

                        vuePetition.petition[key] = msg.campaign[key] ;

                    });

                    if (vuePetition.petition['onsave_descr'] == ''){
                        vuePetition.petition['onsave_descr'] = vuePetition.defaults.onsave_descr;
                    }

                    vuePetition.attaches = msg.attachList ;

                    podpishiMenu.petition_opened.is_opened = true;
                    podpishiMenu.petition_opened.name = vuePetition.petition.title;

                    $(vuePetition.$el).find('#color-picker').spectrum({
                        type: "component",
                        hideAfterPaletteSelect: "true",
                        showInput: "true",
                        showInitial: "true",
                        color: vuePetition.petition.var_arrowcolor,

                        change: function(color) {

                            vuePetition.petition.var_arrowcolor = color.toHexString();

                        },
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

            savePetition: function(){

                var component = this ;

                var data = {};

                data.context = 'save__petition';

                data.form = this.petition ;

                mainSite.swalLoading.enable();

                smartAjax('/ajax/ajax_petition_admin.php?context=save__petition', data, function(msg){

                    //mainSite.swalLoading.disable();

                    //mainSite.swal('Saved! <a href="https://podpishi.org/' + vuePetition.petition.url + '" target="_blank">open it</a>' );

                    if (msg.campaign.is_confirmed == 0){


                        mainSite.swal('Петиция станет активна после проверки федеральным штабом<br/><span style="font-style: italic;">(изменения сохранены)</span>');
                        //document.location.href = '/cabinet/petitionedit/' + msg.campaign.id ;

                    }
                    else {

                        mainSite.swal('Изменения сохранены и доступны сразу<br/><span style="font-style: italic;">(так как ваша петиция уже была проверена штабом)</span>');

                    }



                    component.loadPetitionById(msg.campaign.id, 'no_swal');



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

            vuePetition.init();

        },

        bindings: function(){

        },

    };

</script>