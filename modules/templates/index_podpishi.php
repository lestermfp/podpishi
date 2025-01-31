
<!DOCTYPE html>
<html><head>
    <title>{title}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="description" content="">
    <meta name="keywords" content="">
    <meta name="viewport" content="width=1040">

    <link href='https://fonts.googleapis.com/css?family=PT+Sans:400,700&subset=latin,cyrillic' rel='stylesheet' type='text/css'>

    <link rel="stylesheet" type="text/css" href="/static/v2/css/style_tabs.css" media="screen, projection, print">
	<link rel="stylesheet" type="text/css" href="/static/v2/css/likely.css" media="screen, projection, print">


    {meta}
	
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
	<script type="text/javascript" src="/static/v2/js/likely.js"></script>
	<script type="text/javascript" src="/static/v2/js/main.js"></script>
    <script type="text/javascript" src="/static/v2/js/page.js"></script>
	<script type="text/javascript" src="/static/v2/js/sketch.min.js?v=1"></script>
	
	<script>
	  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
	  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
	  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
	  })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

	  ga('create', 'UA-49182086-2', 'auto');
	  ga('send', 'pageview');

	</script>

</head>
<body>
    <div class="top-outer">
        <div class="top {tmp_activeTab}">
            <div class="top-left" data-target="/bicycle">
                <div class="top-left-bg">
                    <div class="top-title">
                        Требуем
                        <br>
                        велодорожки
                    </div>
                </div>
            </div>
            <div class="top-right" data-target="/trolley">
                <div class="top-right-bg">
                    <div class="top-title">
                        Сохраним троллейбусы
                        <br>
                        в Москве
                    </div>

                </div>
            </div>
            <div class="container">

            </div>
        </div>
    </div>

    <div class="page page-troll active">
        <div class="slogan2">
            <div class="container">
                <h2>{petition_title}</h2>
            </div>
        </div>
        <div class="petition">
            <form onsubmit="return false;">
                <div class="container">
					<div class="petition_nav__item_tmp" style="display: none;">
						<div class="petition_nav__item petition_nav__item--active1 petition_nav__item--checked1 petition_nav__item--active1" data-petition_id="">
							<span class="text_link">Мэру Москвы</span>
						</div>				
					</div>
					
                    <div class="petition_nav">
                        <div class="petition_nav__item meriya petition_nav__item--active1 petition_nav__item--checked1">
                            <span class="text_link">Мэру Москвы</span>
                        </div>
                        <!--div class="petition_nav__item mosgorduma ">
                            <span class="text_link">Депутату МГД</span>
                            <div class="green-check"></div>
                        </div-->
                    </div>
                    <div class="petition__paper">
                        <div class="wrote">
						
                            <a href="#map" class="text_link">Подписанты <br>на карте Москвы</a>
                            <div class="wrote__text" style="display: none;"></div>
                            <div class="wrote__num" style="margin-top: 10px;" >{counter_value}</div>
                            <div class="wrote__text" style="font-size: 14px;margin-bottom: 10px;">
								{counter_sklonenie}
                            </div>
                        </div>
                        <div class="container_inner">
                            <div class="form__block">
                                <div class="whom">
                                    Мэру Москвы <br />С. С. Собянину <br/>
                                </div>
                                <div class="form__row">
                                    <div class="before-input">от</div><input class="form__input ajax_arg" name="name" placeholder="Булгакова Михаила Афанасьевича" type="text">
                                    <div class="form__comment more_margin">
                                        Впишите ваши ФИО<br > (это требование ФЗ)
                                    </div>
                                </div>
                                <div class="form__row">проживающего по адресу:</div>
                                <div class="form__row">
                                    <input class="form__input ajax_arg" type="text" placeholder="Город" value="Москва" name="city">
                                    <div class="form__comment more_margin city_comment">
                                        Заполните адрес полностью, пожалуйста. Вам придёт ответ из мэрии
                                    </div>
                                </div>
                                <div class="form__row">
                                    <input class="form__input ajax_arg" type="text" placeholder="Улица" name="street_name">
                                </div>
                                <div class="form__row more_margin">
                                    <input class="form__input form__input--short ajax_arg" type="text" placeholder="Дом/корпус" name="house_number">
                                    <input class="form__input form__input--short ajax_arg" type="text" placeholder="Квартира" name="appartment">
                                    <div class="form__comment more_margin">
                                        Впишите ноль, если проживаете в частном секторе
                                    </div>
                                </div>
                                <div class="form__row more_margin">
                                    <input class="form__input form__input--short ajax_arg" type="text" placeholder="Номер телефона" name="phone">
                                    <input class="form__input form__input--short ajax_arg" type="text" placeholder="e-mail" name="email">
                                    <div class="form__comment more_margin">
                                        Для верификации и уточнений
                                    </div>
                                </div>
                            </div>
                            <div class="petition__text">
                                <div class="" id="petition__text">
                                    <strong class="whom_header_petition">Уважаемый Сергей Семёнович!</strong>
                                    <div class="text_link edit_text">Редактировать текст</div>
                                    <div class="petition__textblock">
                                        <p>Мне стало известно о запланированных в программе благоустройства «Моя улица» демонтаже троллейбусной сети внутри Садового кольца, ликвидации движения троллейбусов и замене их на автобусы. Я выражаю своё категорическое несогласие с этим решением Правительства Москвы.
                                        </p>
                                        <p>
                                            Троллейбус — экологически чистый транспорт, и в отличие даже от самых современных дизельных автобусов не производит выбросов в атмосферу. На сегодняшний день, насколько мне известно, не существует наземного пассажирского транспорта, аналогичного троллейбусу по экологическим и потребительским качествам. Десятки городов по всему миру развивают и внедряют электрический транспорт, и только Москва считает троллейбус «устаревшим».
                                        </p>
                                        <p>
                                            Московский троллейбус — это больше, чем просто транспорт. Это часть нашей истории и культуры, отличительный признак городского пейзажа и бренд нашего города.
                                        </p>
                                        <p>
                                            Уважаемый Сергей Семёнович. Услышьте мнение москвичей! О планах по ликвидации троллейбусного движения в центре мы неоднократно слышали от московских властей, и теперь они воплощаются в конкретные решения. Эти планы всегда подвергались критике со многими аргументами, на которые не даётся удовлетворительных ответов.
                                        </p>
                                        <p>
                                            Прошу Вас остановить ликвидацию троллейбуса в центре Москвы, пересмотреть проекты реконструкции улиц и внести в них обязательное требование по сохранению движения троллейбусных маршрутов, направить средства на модернизацию контактной сети и улучшение условий для беспрепятственного движения троллейбусов по улицам нашего города.
                                        </p>
                                    </div>
                                    <div class="controls_edit hidden">
                                        <button class="button edit__button" style="display: none;">Сохранить</button>
                                        <div class="text_link edit__cancel" style="display: none;">Отменить</div>
                                    </div>
                                    <div class="petition__date">Дата: <?=date('d') . ' ' . $_CFG['monthsList'][date('n')]?> <?=date('Y');?>, подпись</div>
                                </div>
                                <div class="petition__controls">
                                    <span class="petition__expand text_link hidden">Развернуть текст</span>
                                    <br />
                                    <span class="petition__collapse text_link">Свернуть текст</span>
                                </div>
                                <div class="form__row">
                                    <div class="sign_wrap">
                                        <canvas id="sign_sketch" class="withOpacity" width="340" height="125"></canvas>
                                        <div class="text_link" onClick="podpishi.clearSketch();">Очистить</div>
                                        <div style="position: absolute;height: 1px;background: gray;width: 270px;right: 30px;top: 110px;"></div>
                                    </div>
                                    <div class="form__comment">
                                        В этом окне просьба расписаться. <br />Без вашей подписи обращение не будет рассматриваться
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="wrap">
                        <div class="petition__send" style="position: relative;">
                            <!-- <div class="container_inner">
                                <div class="flexbox">
                                    <div class="flexbox__item">
                                        <div class="form__row">Кому отправить</div>
                                        <div class="form__row">
                                            <input id="mosgorduma" name="mosgorduma" class="form__checkbox ajax_arg" type="checkbox" checked="true">
                                            <label for="mosgorduma" class="label_checkbox">
                                                <span>Мосгордума</span>
                                            </label>
                                        </div>
                                        <div class="form__row">
                                            <input id="gosduma" name="gosduma" class="form__checkbox ajax_arg" type="checkbox" checked="true">
                                            <label for="gosduma" class="label_checkbox">
                                                <span>Госдума</span>
                                            </label>
                                        </div>
                                        <div class="form__row">
                                            <input id="meriya" name="meriya" class="form__checkbox ajax_arg" type="checkbox" checked="true">
                                            <label for="meriya" class="label_checkbox">
                                                <span>Мэрия</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="flexbox__item">
                                        <div class="subscribe">
                                            <label>Эл. почта для новостей кампании</label>
                                            <input class="subscribe__input ajax_arg" name="email1" type="email1">
                                            <button onClick="petition.savePetition();" class="subscribe__button">Отправить</button>
                                        </div>
                                    </div>
                                </div>
                            </div> -->
                            <button class="button send__button" onClick="podpishi.savePetition();">Подписать петицию</button>
                            <div class="button-comment">
                                Нажимая красную кнопку, вы поручаете муниципальному депутату Анастасии Брюхановой распечатать ваше обращение и передать его в мэрию Москвы или депутату Московской городской Думы
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="president" style="cursor: pointer;" onClick="document.location.href = 'http://echo.msk.ru/blog/vl_geer/1760682-echo/';">
            <div class="container">

                <a href="http://echo.msk.ru/blog/vl_geer/1760682-echo/" target="_blank"><button class="button button--blue">Читать обращение транспортников к&nbsp;президенту России</button></a>
                <div class="text-downtime">Действующие сотрудники Мосгортранса и ветераны транспортной отрасли столицы обратились к Владимиру Путину. В обращении они детально изложили аргументы против ликвидации троллейбуса, а также предупредили, что мэрия Москвы сообщает горожанам заведомо ложную информацию.</div>

            </div>
        </div>
        <div class="social">
            <div class="container">
                <div class="social__title">Расскажите друзьям</div>
                <div class="likely">
                    <div class="twitter">Твитнуть</div>
                    <div class="facebook">Поделиться</div>
                    <div class="vkontakte">Поделиться</div>
                </div>
                <div class="logo-city4people">
                    <a href="https://city4people.ru/" class="logo">
                        <img src="/static/v2/images/logo-gorproect.png" class="logo" alt="Городские проекты">
                    </a>
                </div>
            </div>
        </div>
        <a href="#map" name="map" id="map"></a>
        <iframe frameborder="0" height="810" id="mapIframe" src="https://trolley.city4people.ru/map/?staging&template_name={template_name}#podpishi_header={counter_value}" data-counter_value="{counter_value}" width="100%" style="margin-top: 50px;"></iframe>



        <div class="modal_wrap meriya_added" style="display: none;">
            <div class="modal">
                <div class="modal__text">
                    <strong>Спасибо!</strong>
                    <p>
                        Мы распечатаем ваше обращение и отнесём в мэрию Москвы в течение двух дней.
                    </p>
                    <p>
                        В течение 30 дней вам обязаны отправить официальный ответ по указаному вами обратному адресу.
                    </p>

                </div>
                <div class="modal__close">
                    <div class="text_link" onCLick="$(this).closest('.modal_wrap').fadeOut(); $('.button.send__button').fadeOut();">Закрыть</div>
                </div>
            </div>
        </div>
		
        <div class="modal_wrap mosgorduma_added" style="display: none;" data-target_map="trolley">
            <div class="modal">
                <div class="modal__text">
                    <strong>Спасибо!</strong>
                    <p>
                        Мы распечатаем ваше обращение и отправим получателю в течение двух дней.
                    </p>
                    <p>
                        Сейчас мы перенаправим вас на карту где отмечены все кто отправил обращение. Найдите там себя, ваш дом будет отмечен красным. 
                    </p>
                </div>
                <div class="modal__close">
                    <div class="text_link" onCLick="$(this).closest('.modal_wrap').fadeOut();">Закрыть</div>
                </div>
            </div>
        </div>
		
        <div class="modal_wrap meriya_velo_added" style="display: none;">
            <div class="modal">
                <div class="modal__text">
                    <strong>Спасибо!</strong>
                    <p>
                        Мы распечатаем ваше обращение и отнесём в мэрию Москвы в течение двух дней.
                    </p>
                    <p>
                        В течение 30 дней вам обязаны отправить официальный ответ по указаному вами обратному адресу.
                    </p>

                </div>
                <div class="modal__close">
                    <div class="text_link" onCLick="$(this).closest('.modal_wrap').fadeOut(); $('.button.send__button').fadeOut();">Закрыть</div>
                </div>
            </div>
        </div>
		
        <div class="modal_wrap mosgorduma_velo_added" style="display: none;" data-target_map="bicycle">
            <div class="modal">
                <div class="modal__text">
                    <strong>Спасибо!</strong>
                    <p>
                        Мы распечатаем ваше обращение и отправим получателю в течение двух дней.
                    </p>
                    <p>
                        В течение 30 дней вам обязаны отправить официальный ответ по указаному вами обратному адресу.
                    </p>
                    <p>
                        Еще можно подписать петицию за сохранение троллейбусов в Москве.
                    </p>
                    <button class="button modal__button" onClick="document.location.href = '/trolley?prefilled';">Подписать петицию за сохранение троллейбусов</button>
                </div>
                <div class="modal__close">
                    <div class="text_link" onCLick="$(this).closest('.modal_wrap').fadeOut();">Закрыть</div>
                </div>
            </div>
        </div>

        <div class="top_popup" style="display: none;">
            <div class="top_popup_text">
                <strong>Спасибо!</strong>
                <p>Обращение <span class="sent_destination"></span> отправлено. Мы его распечатаем и&nbsp;доставим в&nbsp;течение двух дней, дальше в&nbsp;течение 30 дней вам придёт официальный&nbsp;ответ.</p>
                <p>Теперь подпишите, пожалуйста, обращение <span class="target_destination"></span> ниже</p>
            </div>
            <div class="top_popup__close">
                <span class="text_link">Закрыть</span>
            </div>
        </div>

        <div class="modal_wrap none_added" style="display: none;">
            <div class="modal">
                <div class="modal__text">
                    <strong>Спасибо!</strong>
                    <p>
                        Мы распечатаем ваше обращение и отправим получателю в течение двух дней.
                    </p>
                    <p>
                        Сейчас мы перенаправим вас на карту где отмечены все кто отправил обращение. Найдите там себя, ваш дом будет отмечен красным. 
                    </p>
                </div>
                <div class="modal__close">
                    <div class="text_link" onCLick="$(this).closest('.modal_wrap').fadeOut();">Закрыть</div>
                </div>
            </div>
        </div>
    </div>

	
	

</body>
</html>