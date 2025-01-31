

<?php

if ($_USER->hasRole('guest') == false) {

    ?>
    <!-- NAVIGATION
    ================================================== -->

    <nav class="navbar navbar-vertical fixed-left navbar-expand-md navbar-light" id="sidebar">
        <div class="container-fluid">

            <!-- Toggler -->
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#sidebarCollapse"
                    aria-controls="sidebarCollapse" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- User (xs) -->
            <div class="navbar-user d-md-none">

                <!-- Dropdown -->
                <div class="dropdown">

                    <!-- Toggle -->
                    <a href="#" id="sidebarIcon" class="dropdown-toggle" role="button" data-toggle="dropdown"
                       aria-haspopup="true" aria-expanded="false">
                        <div class="avatar avatar-sm avatar-online">
                            <img src="assets/img/avatars/profiles/avatar-1.jpg" class="avatar-img rounded-circle"
                                 alt="...">
                        </div>
                    </a>

                    <!-- Menu -->
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="sidebarIcon">
                        <a href="profile-posts.html" class="dropdown-item">Profile</a>
                        <a href="settings.html" class="dropdown-item">Settings</a>
                        <hr class="dropdown-divider">
                        <a href="sign-in.html" class="dropdown-item">Logout</a>
                    </div>

                </div>

            </div>

            <!-- Collapse -->
            <div class="collapse navbar-collapse" id="sidebarCollapse">


                <template v-cloak v-if="petition_opened.is_opened">



                    <!-- Navigation -->
                    <ul class="navbar-nav">

                        <li class="nav-item">
                            <a class="nav-link" href="#sidebarPages" data-toggle="collapse" role="button"
                               aria-expanded="true" aria-controls="sidebarPages">
                                <i class="fe fe-file"></i> <b>Петиция</b>
                            </a>

                            <a class="nav-link">
                                <small>{{petition_opened.name}}</small>
                            </a>

                            <div class="collapse show" id="sidebarPages">
                                <ul class="nav nav-sm flex-column">

                                    <li class="nav-item">
                                        <a href="#nav-name" class="nav-link ">
                                            🚩 Название
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="#nav-opengraph" class="nav-link ">
                                            🌐 Опенграф
                                        </a>
                                    </li>

                                    <li class="nav-item">
                                        <a href="#nav-styles" class="nav-link ">
                                            🎑 Внешний вид
                                        </a>
                                    </li>

                                    <?php

                                    if (in_array('yabloko', $_USER->getMainUser()->getDomains())) {

                                    ?>

                                    <li class="nav-item">
                                        <a href="#nav-author" class="nav-link ">
                                            👤 Видео и автор
                                        </a>
                                    </li>

                                    <?php

                                    }

                                    ?>

                                    <li class="nav-item">
                                        <a href="#nav-files" class="nav-link ">
                                            📁 Файлы
                                        </a>
                                    </li>

                                    <li class="nav-item">
                                        <a href="#nav-buttonsave" class="nav-link ">
                                            ⚙️ Финальные настройки
                                        </a>
                                    </li>

                                    <li class="nav-item">
                                        <a href="#nav-saveall" class="nav-link ">
                                            💾 Сохранение
                                        </a>
                                    </li>


                                </ul>
                            </div>
                        </li>

                    </ul>

                    <!-- Divider -->
                    <hr class="navbar-divider my-3">


                </template>

                <!-- Navigation -->
                <ul class="navbar-nav">

                    <li class="nav-item">
                        <a class="nav-link" href="#sidebarPages" data-toggle="collapse" role="button"
                           aria-expanded="true" aria-controls="sidebarPages">
                            <i class="fe fe-sidebar"></i> Навигация
                        </a>
                        <div class="collapse show" id="sidebarPages">
                            <ul class="nav nav-sm flex-column">

                                <li class="nav-item">
                                    <a href="/cabinet/petitionedit/new" class="nav-link ">
                                        Новая кампания
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/cabinet/petitionslist" class="nav-link ">
                                        Список кампаний
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="/cabinet/authors" class="nav-link ">
                                        Авторы
                                    </a>
                                </li>

                                <?php

                                if ($_USER->hasRole('admin')) {

                                ?>

                                <li class="nav-item">
                                    <a href="/cabinet/accounts" class="nav-link ">
                                        Аккаунты
                                    </a>
                                </li>

                                <?php

                                }

                                ?>
                                <li class="nav-item">
                                    <a href="/logout" class="nav-link ">
                                        Выход
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                </ul>



            </div> <!-- / .navbar-collapse -->

        </div>
    </nav>

    <script>

        var podpishiMenu = new Vue({
            el: '#sidebar',

            data: {

                petition_opened: {
                    name: '',
                    is_opened: false,
                },



            },

            mounted: function () {



            },

        });

    </script>
    <?php

}

?>