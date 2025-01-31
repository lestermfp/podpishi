
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
                                    <i class="fe fe-activity"></i> Статистика
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

                <div class="card">
                    <div class="card-header">

                        <!-- Title -->
                        <h3 class="card-header-title">
                            🕺 Всего подписало: <b>{{petition.stats.amount_total}}</b> чел.
                        </h3>



                    </div>
                    <div class="card-body">

                        <!-- Form -->
                        <form class="mb-4">

                            <!-- Project name -->
                            <div class="form-group">


                                <div class="row">

                                    <div class="col-6">

                                        <h2>Псковская область</h2>

                                        Псковская область в целом: {{petition.stats.pskov_region_total}} <small>({{percentOf(petition.stats.pskov_region_total, petition.stats.amount_total)}}%)</small>

                                        <br/>
                                        <br/>

                                        <label>
                                            • Псков: {{petition.stats.city_pskov_total}} <small>({{percentOf(petition.stats.city_pskov_total, petition.stats.amount_total)}}%)</small>
                                        </label>
                                        <br/>
                                        <label>
                                            • Великие Луки: {{petition.stats.city_luki_total}} <small>({{percentOf(petition.stats.city_luki_total, petition.stats.amount_total)}}%)</small>
                                        </label>


                                        <template v-for="subregion in petition.stats.subregions_pskov">

                                            <br/>
                                            <label>
                                                • {{subregion.rn_name}}: {{subregion.amount}} <small>({{percentOf(subregion.amount, petition.stats.amount_total)}}%)</small>
                                            </label>

                                        </template>

                                    </div>

                                    <div class="col-6">

                                        <h2>Другие регионы РФ</h2>

                                        Всего из регионов: {{petition.stats.regions_sum}} <small>({{percentOf(petition.stats.regions_sum, petition.stats.amount_total)}}%)</small>
                                        <br/>

                                        <template v-for="region in petition.stats.regions">

                                            <br/>
                                            <label>
                                                • {{region.region_name}}: {{region.amount}} <small>({{percentOf(region.amount, petition.stats.amount_total)}}%)</small>
                                            </label>

                                        </template>


                                        <br/>
                                        <h2>Не РФ: {{petition.stats.abroad_total}} <small>({{percentOf(petition.stats.abroad_total, petition.stats.amount_total)}}%)</small></h2>

                                    </div>

                                </div>



                            </div>



                        </form>

                    </div> <!-- / .card-body -->

                </div>


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

                domain: '',

                stats: {
                    amount_total: 0,
                    pskov_region_total: 0,
                },

            },

        },

        mounted: function () {

        },

        watch: {


        },

        methods: {

            init: function(){


                var pathparts = document.location.pathname.split('/');

                if (pathparts.length == 4) {

                    this.petition.id = pathparts[3];

                    this.loadPetitionById(this.petition.id);

                }

            },

            percentOf: function(a, b){

                if (b == 0)
                    return 0;

                return Math.round(a / b * 100);

            },

            loadPetitionById: function(petition_id){

                var component = this;

                var data = {};

                data.context = 'get__campaignStatsById';

                data.campaign_id = petition_id;

                smartAjax('/ajax/ajax_petition_admin.php?context=get__campaignStatsById', data, function(msg){

                   component.petition = msg.petition ;

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

            vuePetition.init();

        },

        bindings: function(){

        },

    };

</script>