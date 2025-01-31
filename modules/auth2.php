<?php

if (is_authed())
    redirect('/cabinet');

?>


<!-- CONTENT
================================================== -->
<div class="container">
    <div class="row justify-content-center">
        <div class="col-12 col-md-5 col-xl-4 my-5">

            <!-- Heading -->
            <h1 class="display-4 text-center mb-3">
                Представьтесь
            </h1>

            <!-- Subheading -->
            <p class="text-muted text-center mb-5">

            </p>

            <!-- Form -->
            <form onsubmit="auth_sp.do_auth(); return false;" class="authform">

                <!-- Email address -->
                <div class="form-group">

                    <!-- Label -->
                    <label>Телефон</label>

                    <!-- Input -->
                    <input type="text" class="form-control" onFocus="$(this).formatPnoneNumber(); if (this.value == '') this.value = '+7';" placeholder="+7" name="auth_phone">

                </div>

                <!-- Password -->
                <div class="form-group">

                    <div class="row">
                        <div class="col">

                            <!-- Label -->
                            <label>Пароль</label>

                        </div>
                        <div class="col-auto">

                        </div>
                    </div> <!-- / .row -->

                    <!-- Input group -->
                    <div class="input-group">

                        <!-- Input -->
                        <input type="password" class="form-control form-control-appended" name="auth_password" placeholder="">


                    </div>
                </div>

                <!-- Submit -->
                <button class="btn btn-lg btn-block btn-primary mb-3">
                    Войти
                </button>

                <div>
                    Или войти через:
                    <ul>
                        <?php
                            foreach ($_CFG['oauth_collection'] as $oauth):
                        ?>
                                <li>
                                    <a href="https://<?=$oauth['base_domain'] . $oauth['oauth_url']?>">
                                        <img src="<?=$oauth['icon_url']?>" width="16" height="16">
                                        <?=$oauth['base_domain']?></a>
                                </li>
                        <?php
                            endforeach;
                        ?>


                    </ul>

                </div>


            </form>

        </div>
    </div> <!-- / .row -->
</div> <!-- / .container -->


<script>

    auth_sp = {

        is_url: true,

        do_auth: function(){

            var container = $('.authform');

            var auth_phone = container.find('input[name="auth_phone"]').val();
            var auth_password = container.find('input[name="auth_password"]').val();

            smartAjax('/ajax/ajax_auth.php?context=authUser', {
                context: 'authUser',
                auth_phone: auth_phone,
                auth_password: auth_password,
            }, function(msg){

                if (typeof(window.onAuthSpecial) == "function"){
                    window.onAuthSpecial(msg);
                }
                else {

                    document.location.href = '/cabinet';
                }

            }, function(msg){

                mainSite.swal(msg.error_text);

            }, 'auth', 'POST');

        },


    };



</script>
