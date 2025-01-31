<?php
//print '<pre>' . print_r($list, true) . '</pre>';

$streetSources = array(
	'alive_center' => array(
		'names' => array(),
		'type' => 'alive',
		'name' => 'Остались в центре',
	),
	'alive_tillapril' => array(
		'names' => array(),
		'type' => 'alive',
		'name' => 'К сносу в апреле 2017',
	),
	'closed_7july2016' => array(
		'names' => array(),
		'type' => 'closed',
		'name' => 'Закрыты 7 июля 2016',
	),
	'closed_2may' => array(
		'names' => array(),
		'type' => 'closed',
		'name' => 'Закрыты 2 мая',
	),
	'closed_october1_2015' => array(
		'names' => array(),
		'type' => 'closed',
		'name' => 'Закрыты 1 октября 2015',
	),
	'closed_summer2013' => array(
		'names' => array(),
		'type' => 'closed',
		'name' => 'Закрыты летом 2013',
	),
	'closed_summer2014' => array(
		'names' => array(),
		'type' => 'closed',
		'name' => 'Закрыты летом 2014',
	),
);
$groups = scandir('./streets_groups');


foreach ($streetSources as $_key => $_value){
	
	$names = file('./streets_groups/' . $_key . '.txt');
	
	foreach ($names as $_n => $_name){
			$names[$_n] = trim(str_replace('—', '-', $_name));
			$names[$_n] = mb_convert_case((str_replace('', '', $names[$_n])), MB_CASE_LOWER, 'utf-8');
	}
	
	
	$streetSources[$_key]['names'] = $names ;
	
}
//print '<pre>' . print_r($streetSources, true) . '</pre>';


//exit();
$list = scandir('streets');

$_trolley = array(
	
);

$streets_names = array();


foreach ($list as $_file){
	

	if ($_file == '.' OR $_file == '..')
		continue ;
	
	//print $_file ;
	
	$content = file_get_contents('./streets/' . $_file);
	
	$content = unserialize($content);
	
	//print '<pre>' . print_r($content, true) . '</pre>';
	
	$points = json_decode($content['map_points1'], true);
	
	$streets_names[$_file] = trim($content['name']) ;
	
	$points[0]['t_name'] = $content['name'];
	
	//$points[]
	
	$_trolley[] = $points;
	//print $content ;
	//exit();
	
}

//print '<pre>' . print_r($_trolley, true) . '</pre>';



?>


	<!DOCTYPE html>
<html>
    <head>
		<meta content="text/html; charset=utf-8" http-equiv="Content-Type">
		
        <title>Москвичи за троллейбус</title>
		
		<link href='https://fonts.googleapis.com/css?family=Ubuntu:500,400&subset=latin,cyrillic' rel='stylesheet' type='text/css'>
		<link rel="stylesheet" href="https://trolley.city4people.ru/map/static/css/style.css">
		<link rel="stylesheet" href="https://trolley.city4people.ru/map/static/css/button.css">
		
		<script src="https://api-maps.yandex.ru/2.1/?lang=ru_RU" type="text/javascript"></script>
		<script src="https://yandex.st/jquery/1.6.4/jquery.min.js" type="text/javascript"></script>
		
		
        <meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="robots" content="all"/>
				
		<meta property="og:url"                content="http://trolley.city4people.ru/map/" />
		<meta property="og:type"               content="article" />
		<meta property="og:title"              content="Москвичи за троллейбус" />
		<meta property="og:description"        content="Присоединяйтесь к кампании за сохранение экологически чистого транспорта" />
		<meta property="og:image"              content="http://trolley.city4people.ru/map/static/images/social_logo.png" />
		<meta property="og:image:width" content="900" />
		<meta property="og:image:height" content="605" />

		
		<meta name="twitter:card" content="summary_large_image"/>  <!-- Тип окна -->
		<meta name="twitter:site" content="Москвичи за троллейбус"/>
		<meta name="twitter:title" content="Москвичи за троллейбус">
		<meta name="twitter:description" content="Присоединяйтесь к кампании за сохранение экологически чистого транспорта"/>
		<meta name="twitter:image:src" content="http://trolley.city4people.ru/map/static/images/social_logo.png"/>
		<meta name="twitter:domain" content="http://trolley.city4people.ru/map/"/>
	
		
		<script>
		
		$(document).ready(function(){

	
			$.fn.formatPnoneNumber = function(){
				return this.each(function(){
					$(this).bind('keyup', function(){
						var num = this.value.replace( '+' , '' ).replace( /\D/g, '' ).split( /(?=.)/ ), i = num.length;
						if ( 0 <= i ) num.unshift( '+' );
						if ( 2 <= i ) num.splice( 2, 0, ' ' );
						if ( 5 <= i ) num.splice( 6, 0, ' ' );
						if ( 8 <= i ) num.splice( 10, 0, '-' );
						if ( 11 <= i ) num.splice( 13, 0, '-' );
						//if ( 12 <= i ) num.splice( 14, 0, '-' );
						//if ( 9 <= i ) num.splice( 12, 0, '-' );
						if ( 14 <= i ) num.splice( 17, num.length - 17 );
						this.value = num.join( '' );
					});
				});
			};
		});
			
		</script>
					

		<style>
			header.header {
				position: absolute;
				top: 0;
				left: 0;
				right: 0;
				width: 100%;
				min-height: 100px;
                height: auto;
				background: rgba(255, 255,255, .8);
				padding: 25px 0 10px 0;
                box-sizing: border-box;
			    color: #010101;
			    z-index: 50;
			}

			header.header img {
				height: 50px;
				position: relative;
				margin-right: 40px;
			}

			body {
				background: #f4f4f4;
			}

			.wrapper {
				width: 100%;
				padding: 0 20px;
				box-sizing: border-box;
			}

			.button.button--red {
			    background-color: #E41616 !important ;
			}

            .button.button--red:hover {
                background-color: #d4151c !important ;
            }

			.button.button--twolined {
				font-weight: normal;
				font-size: 13px !important ;
				line-height: 15px !important ;
			}

			@media screen and (min-width: 1000px) {
				.wrapper {
					width: 1000px;
					margin: 0 auto;
					padding: 0;
				}
				.footer__text, .footer__button {
					display: inline-block;
					vertical-align: top;
					font-size: 15px;
				}

				.footer__text {
					width: 34%;
                    line-height: 17px;
                    margin-right: 2%;
				}

				.footer__button {
					width: 39%
				}

				.footer__button .button {
					float: right;
				}
			}
		</style>
		
    </head>
    <body>
		
		
<header class="header podpishi_iframe" style="padding-top: 30px; display: none;">
    <div class="wrapper" style="font-size: 29px;text-align: center;font-weight: bold;font-family: 'PT Sans', sans-serif;">Подписанты и волонтёры кампании «Москвичи за троллейбус»</div>
</header>

<header class="header header_main" style="display: none;">
    <div class="wrapper">
        <img src="/map/static/images/m_logo.png">
        <div class="footer__text">
           Оставьте нам свой телефон или почту. <br/>С вами свяжется координатор чтобы передать листовки и значок «Москвичи за троллейбус»
        </div>
        <div class="footer__button" style="width: 198px;">
            <button class="button button--blue button--smalsize" type="button" onclick="trolley.myHouseIsNotFound();">Зарегистрироваться</button>
			<div style="
				text-align: left;
				font-size: 13px;
				border: none;
			" href="/"><a target="_blank" href="http://trolley.city4people.ru/" class="text-link">Узнать о кампании</a></div>
        </div>
		<div class="footer__button footer__button--nomargin" style="width: 198px;margin-left: 10px;">
			<a href="https://podpishi.org/" target="_blank"><button class="button button--smalsize button--twolined button--red" type="button" onclick="">Подпишите обращение к&nbsp;Мэру Москвы</button></a>
			
		</div>
    </div>
</header>




<style>
	.hide {
	  display: none;
	}

	#map {
		height: 100%;
	}

    .baloon {
        border-radius: 0px !important;
    }

	.map .baloon--login {
		text-align: left;
	}

	#baloon--register {
		text-align: left;
		padding-bottom: 15px;
	}

	#baloon--register button {

	}

	#baloon--register::before, #baloon--helper::before, #baloon--shtab1::before, #baloon--shtab::before {
		content: '';
		position: absolute;
		width: 20px;
		height: 20px;
		top: -8px;
		left: -8px;
		border-radius: 50%;
		background-color: #0096ff;
	}

	.button.button--pink {
		background-color: #009fe3;
	}

	.button.button--pink:hover {
		background-color: #009fe3;
	}

	.button.button--smalsize {
		font-size: 11px;
		height: 30px;
		line-height: 30px;
	}

	.baloon--shtab {
		text-align: left;
		line-height: 19px;
	}

    .button.button--smalsize:hover, .button.button--pink:hover {
        background-color: #0189c8;
    }

    .footer {
        position: absolute;
        bottom: 0px;
        left: 0;
        right: 0;
        width: 100%;
        background: rgba(255, 255,255, .8);

    }

    .modal {
        border-radius: 0;
    }


	.button.button--smalsize, .button.button--pink {
		font-size: 16px;
		height: 40px;
        background-color: #0095da;
        border-radius: 0;
        padding: 0 20px 2px 20px;
	}
    .button.button--smalsize:hover, .button.button--pink:hover {
        background-color: #0189c8;
    }

    .text-link {
        line-height: 18px;
    }


	@media screen and (max-width: 800px) {
		header.header {
			height: auto;
		}

		header.header img {
			left: 0px;
            margin-bottom: 10px;
		}

		#map {
			margin-top: 205px;
		}

		.modal3 {
			padding: 35px 30px 5px 20px;
			margin-top: 200px;
		    margin-bottom: 250px;
		}

		.myHouseIsNotFound {
			overflow: scroll ;
			margin-bottom: 200px;
		}

		.baloon--legend {
			display: none;
		}

		.footer__button--nomargin {
			margin-left: 0px !important ;
		}

		.button.button--twolined {
			font-weight: normal;
			font-size: 13px !important ;
			line-height: 14px !important ;
		}

	}

	.baloon--legend {
		top: initial;
		bottom: 30px;
		left: 10px;
		width: 360px;
		text-align: left;
	    background: rgba(255, 255,255, .8);
		border-radius: 0px;
		padding-top: 24px;
		padding-left: 28px;
		padding-bottom: 18px;
		height: 240px;
	}

    #baloon--register {
        padding: 20px 30px 30px 30px;
    }

    .baloon__address {
        margin-bottom: 10px;
    }

    .baloon__follower_counter {
        margin-bottom: 15px;
    }

    .form__input, .form__textarea {
        border-radius: 0;
    }

	.baloon .form__input, .baloon .form__textarea {
        width: 100%;
		border-radius: 0px;
	}

    .form__label {
        margin-bottom: 5px;
    }

	.baloon__list .list__text {
		width: 290px;
		font-size: 12px;
		line-height: 16px;
		height: 16px;
		vertical-align: top;
	}

	.baloon__list {
		margin-left: 0px !important ;
	}

	.baloon--legend .list__text {

	}

	.list__circle.list__circle--black {
		background: black;
	}

	.list__circle {
		border-radius: 25px;
		width: 16px !important;
		height: 16px !important ;
	}

	.list__circle.list__circle--green {
		background: #58B62E;
	}

	.list__circle.list__circle--blue {
		background: #0097D8;
	}
	
	.list__circle.list__circle--blue_removed {
		background: #3f51b5;
	}
	
	.list__circle.list__circle--red_removed{
		background: #f44336;
	}

	.list__circle.list__circle--lightblue {
		background: #58C9E9;
	}

	.list__circle.list__circle--red {
		background: #EB6154;
	}
</style>

<div>
<center>
  <div id="map"></div>
	
	<div class="baloon baloon--legend" style="">
		<span style="font-weight: bold;">Карта троллейбусного погрома</span>
		<ul class="baloon__list" style="margin-top: 15px;margin-left: 0px;">
			<li class="list__item">
				<div class="list__circle list__circle--red"></div>
				<div class="list__text" onclick="gd.drawStreetsGroup('closed');"> Где убрали троллейбус (<span id="total_removed"></span>)</div>
			</li>
			<li class="list__item">
				<div class="list__circle list__circle--blue_removed"></div>
				<div class="list__text" onclick="gd.drawStreetsGroup('alive');"> Могут убрать в апреле (<span id="total_to_remove"></span>)</div>
			</li>
			<li class="list__item" style="">
				<div class="list__circle list__circle--black"></div>
				<div class="list__text" onclick="gd.drawStreetsGroup('all');"> Всё вместе (<span id="total_length"></span>)</div>
			</li>
			<li class="list__item" style="display: none;">
				<div class="list__circle list__circle--red"></div>
				<div class="list__text">за последний час (<span id="amount_helper_new"></span>)</div>
			</li>
		</ul>
		<span style="font-weight: bold; display: none;">Всего: <span id="amount_total"></span></span>
	</div>

	


</center>
</div>


<br />



<br />


<script>

poly = <?=json_encode($_trolley)?>;
streetSources = <?=json_encode($streetSources);?>;

//poly = [];
</script>



<script>

GLOBAL_AJAX  = {};

ymaps.ready(init);

function init() {
    gdMap.map = new ymaps.Map("map", {
            center: [55.75710630790187, 37.656742761104105],
            zoom: 13,
			controls: [],
        }, {
            searchControlProvider: 'yandex#search'
        });
	
		gdMap.map.controls.add('zoomControl', {
							
			position: {
					left: 5,
					top: 5,
				}				
			
		});

		
	gdMap.map.events.add('click', function (e) {
		
		gdMap.clickedCoords = e.getSourceEvent().originalEvent.coords;
		
		gd.onMapClicked();
	});
	
	gd.onMapInit();
	
};


gdMap = {
	
	map: {},
	
	elecrified: ['улица Большая Лубянка', 'проспект Мира', 'проспект Мира (дублёр)', 'Останкинский проезд', 'улица Академика Королёва', 'Ботаническая улица'],
	
};




gd = {
	
	routeName: '',
	selected_point_id: 0,
	plans: [],
	elecrified: [],
	streetTypes: {
		'alive': [],
		'closed': [],
	},
	distances: {
		'alive': 0,
		'closed': 0,
	},
	
	createNewBusRoute: function(){
		
		var name = prompt('Введите название улицы');
		
		
		gd.routeName = name ;
		
		
		$('.newLineBtn').show();
		gd.createNewBusLine();
	},
	
	prepareElectrifiedList: function(){
		
		gd.elecrified.push();
		
	},
		
	onMapInit: function(){
		
		gd.groupStreetsByType();
		gd.drawStreetsGroup('all');
		gd.drawStreetsGroup('alive');
			
		
		gd.calculateDistances();
		
		/*
		for (var i = 0; i < poly.length; i++)
			$.each(poly[i], function(){
				gd.drawPolyline(this.coord);
			});
		*/
	},
	
	calculateDistances: function(){
		
		var total_removed = Math.round(gd.distances.closed / 1000) ;
		var total_to_remove =  Math.round(gd.distances.alive / 1000) ;
		
		var closedText = gd.streetTypes.closed.length + ' ' + niceEnding(gd.streetTypes.closed.length, ['улица', 'улицы', 'улиц']);
		var aliveText = gd.streetTypes.alive.length + ' ' + niceEnding(gd.streetTypes.alive.length, ['улица', 'улицы', 'улиц']);
		
		var totalText = (gd.streetTypes.alive.length + gd.streetTypes.closed.length) + ' ' + niceEnding(gd.streetTypes.closed.length + gd.streetTypes.alive.length, ['улица', 'улицы', 'улиц']);
		
		$('#total_removed').text(closedText + ', ' + total_removed + ' км');
		$('#total_to_remove').text(aliveText + ' , ' + total_to_remove + ' км');
		$('#total_length').text(totalText + ', ' + (total_removed + total_to_remove) + ' км');
		
	},
	
	groupStreetsByType: function(){
		

		

		$.each(streetSources, function(){
			
			if (this.type == 'alive'){
				
				
				
			}
			else if (this.type == 'closed'){
				
				
				
			}
			else {
				console.log('NOTHING');
			}
				
			
			$.merge(gd.streetTypes[this.type], this.names);
		
		});
		
	},
	
	drawStreetsGroup: function(type, group){
		
		var streetsToShow = [];
		
		if (type != 'all')
			if (typeof(group) !== "undefined"){
				$.merge(streetsToShow, streetSources[group].names);
			}
			else {
				$.each(streetSources, function(){
					
					//if (this.type == type)
						$.merge(streetsToShow, this.names);
				
				});
			}
			
		
		gdMap.map.geoObjects.removeAll();
		
		for (var i = 0; i < poly.length; i++){
			gd.coords = [] ;
			
			var street_name = poly[i][0].t_name.toLowerCase();
			
			//alert(street_name);
			//return ;
			if (type != 'all')
				if (streetsToShow.indexOf(street_name) == -1)
					continue ;
			
			for (var n = 0; n < poly[i].length; n++){
				
				$.each(poly[i][n].poly, function(){
					$.merge(gd.coords, this.coord);
					
					if (gd.elecrified.indexOf(this.name) == -1 && this.name != "")
						gd.elecrified.push(this.name);
					
				});
				
			}

			var type_inner = 'alive';
			if (gd.streetTypes.alive.indexOf(street_name) == -1){
				type_inner = 'closed';
			}
			else if (gd.streetTypes.closed.indexOf(street_name) == -1){
				console.log('Found none ' + street_name);
			}
			
			color = "#000000" ;
			
			if (type == 'all'){
				
				
				//console.log(type_inner);
				
				gd.distances[type_inner] += parseInt(poly[i][0].total_length);
				
			}
			else {
				if (type_inner == 'alive'){
					color = "#3f51b5" ;
				}
				else {
					color = "#f44336" ;
				}
			}
			/*
			else if (type == 'alive' && type_inner == "alive"){
				//color = "#3f51b5" ;
			}
			else if (type == 'closed'){
				//color = "#f44336" ;
			}
			*/
			
			gd.drawPolyline(gd.coords, poly[i][0].t_name, color);
		}		
		
	},
	
	drawPolyline: function(coord, balloonContent, strokeColor){
			
		strokeOpacity = 0.5 ;
		if (strokeColor != '#000000')
			strokeOpacity = 1;
			
		// Создаем ломаную с помощью вспомогательного класса Polyline.
		var myPolyline = new ymaps.Polyline(coord, {
				// Описываем свойства геообъекта.
				// Содержимое балуна.
				balloonContent: balloonContent,
			}, {
				// Задаем опции геообъекта.
				// Отключаем кнопку закрытия балуна.
				balloonCloseButton: false,
				// Цвет линии.
				strokeColor: strokeColor,
				// Ширина линии.
				strokeWidth: 4,
				// Коэффициент прозрачности.
				strokeOpacity: strokeOpacity,
			});

		// Добавляем линии на карту.
		gdMap.map.geoObjects.add(myPolyline);
			
	},
	
	
	saveNewRoute: function(){

		gd.tmpPlans = [];
		
		$.each(gd.plans, function(){
			gd.tmpPlans.push({
				'dots': this.dots,
				'streets': this.streets,
				'poly': this.poly,
				'total_length': this.total_length,
			});
		});
		
		smartAjax('./ajax_router.php?context=saveNewRoute', {
			routeName: gd.routeName,
			plan: gd.tmpPlans,
			type: 'street',
		}, function(msg){
			
			alert('Сохранено. Вы кот! ^_^');
			
			document.location.reload();
			
			$('.newLineBtn').hide();
			
		}, function(msg){
			
			alert(msg.error_text);
			
		}, 'saveRouteByAjax', 'POST');			
	},
	
	pointSelected: function(route_id, point_id){
		
		gd.selected = {
			route_id: route_id,
			point_id: point_id,
		};
		
		gd.markPointAsSelected();
	},

	markPointAsSelected: function(){
		
		//gd.plans[gd.selected.route_id].route.getWayPoints().get(0).properties.set('iconContent', 'wtf')
		
		var route = gd.plans[gd.selected.route_id].route;
				
		var points = route.getWayPoints(),
						lastPoint = points.getLength() - 1;
						
		for (var i =0; i <= lastPoint; i++){
			
			var iconContent = points.get(i).properties.get('iconContent')
			
			iconContent = iconContent.replace(' (выбрана)', '');
			
			if (i == gd.selected.point_id)
				iconContent += ' (выбрана)';
			
			points.get(i).properties.set('iconContent', iconContent);
			
		}
	},
	
	pointDelete: function(route_id, point_id){
		
		var tmp = [];
		
		for (var i = 0; i < gd.plan.features.length; i++){
			var item = gd.plan.features[i];
			
			if (i == point_id)
				continue ;
			
			tmp.push(item);
		}
		
		gd.plan.features = [] ;
		gd.plan.features = tmp ;
		
		gd.map.geoObjects.remove(gd.current_route);
		
		gd.prepareRouteArray(gd.plan);
		
	},
	
	onMapClicked: function(){

		gd.plans[gd.selected.route_id].dots[gd.selected.point_id] = [gdMap.clickedCoords[0], gdMap.clickedCoords[1]];
		
		console.log(gd.plans[gd.selected.route_id].dots[gd.selected.point_id]);
		
		gdMap.map.geoObjects.remove(gd.plans[gd.selected.route_id].route);
		
		gd.prepareRouteArray(gd.plans[gd.selected.route_id].dots);
		
	},
	
	prepareRouteArray: function(raw_route){
		
		var clear_route = [] ;
		var type = '';
		for (var i = 0; i < raw_route.length; i++){
			var item = raw_route[i] ;
			
			//if (item.geometry.type != 'Point')
				//continue ;
			
			//console.log($(item.properties.popupContent).text());
			
			type = 'wayPoint';
			
			if (i == 0 || i == raw_route.length){
				type = 'wayPoint';
			}
			
			//clear_route.push([item.geometry.coordinates[1], item.geometry.coordinates[0]]);
			clear_route.push({ type: type, point: [item[0], item[1]] });
			
			//if (item.properties.id == 'mar_point_numeric_15')
				//break ;
		}
		
		//console.log(clear_route);
		
		//return ;
		
		gdMap.wr = clear_route ;
		gd.drawRoute(clear_route);
				
	},
	
	drawRoute: function(route_info){
		
		gdMap.plan_segments = [];
		
	   // Добавим на карту схему проезда
		ymaps.route(route_info, {
			routingMode: 'masstransit',
			mapStateAutoApply: false,
		}).then(function (route) {
			
			gdMap.map.geoObjects.add(route);
			

			
			// Зададим содержание иконок начальной и конечной точкам маршрута.
			// С помощью метода getWayPoints() получаем массив точек маршрута.
			// Массив транзитных точек маршрута можно получить с помощью метода getViaPoints.
			var points = route.getWayPoints(),
				lastPoint = points.getLength() - 1;
			// Задаем стиль метки - иконки будут красного цвета, и
			// их изображения будут растягиваться под контент.
			points.options.set('preset', 'islands#redStretchyIcon');
			// Задаем контент меток в начальной и конечной точках.
			points.get(0).properties.set('iconContent', 'Точка отправления');
			points.get(lastPoint).properties.set('iconContent', 'Точка прибытия');

			var points_list = [];
			var points_objects = [];
			
			var route_id = gd.selected.route_id;
			var number_modifier = route_id * 3 ;
			
			for (var i =0; i <= lastPoint; i++){
				
				var iconContent = 'Точка ' + ((i + 1) + number_modifier);
				
				//if (gd.selected.point_id == i)
				//	iconContent += ' (выбрана)';
				
				points.get(i).properties.set('iconContent', iconContent);
				
				
				points.get(i).properties.set('balloonContent', '');
				
				points.get(i).properties.set('route_id', route_id);
				points.get(i).properties.set('point_id', (i));
				
				points.get(i).events.add('click', gd.onPlacemarkClicked);					
				
				//points.get(i).properties.set('balloonContent', '<input type="button" value="Двигать" onClick="gd.pointSelected(' + gd.selected.route_id + ',' + i + ');"><div  style="display: none;" onClick="gd.pointDelete(' + gd.selected.route_id + ',' + i + ');">Удалить</div>');
				
				//console.log();
				points_list.push(points.get(i).geometry._coordinates);
				points_objects.push(points.get(i));
			}
			
			var moveList = 'Маршрут ' + gd.routeName +  ',</br>',
				way,
				segments;
				
			var streets = [];
			
			var elecrified_distance = 0 ;	
			var total_length = 0;
			var last_street = '';
			var poly = [];
			
			// Получаем массив путей.
			for (var i = 0; i < route.getPaths().getLength(); i++) {
				way = route.getPaths().get(i);
				segments = way.getSegments();
				
				for (var j = 0; j < segments.length; j++) {
					var street = segments[j].getStreet();

					if (street != '')
						last_street = street ;
					
					if (street != '' && streets.indexOf(street) == -1)
						streets.push(street);

					if (gd.elecrified.indexOf(last_street) != -1){
						elecrified_distance += parseInt(segments[j].getLength());
						//gdMap.ww = segments[j];
					}
					else {
						//gdMap.drawPolyline(segments[j]);
					}
					
					total_length += parseInt(segments[j].getLength());
					
					
					poly.push({
						coord: segments[j]._geometry.coordinates,
						name: last_street,
					});					
					
				}
				
			}		

			
			moveList += 'Общая длина - ' + (total_length / 1000).toFixed(1) + ' км. </br>'
			moveList += 'Из них под проводами - ' + (elecrified_distance / 1000).toFixed(1) + ' км.</br>'

			
			
			//moveList += 'Останавливаемся.';
			
			// Выводим маршрутный лист.
			
			$('#list').html(moveList);			
			
			
			gd.plans[gd.selected.route_id].route = route;
			gd.plans[gd.selected.route_id].dots = points_list;
			gd.plans[gd.selected.route_id].d_objects = points_objects;
			gd.plans[gd.selected.route_id].streets = streets;
			gd.plans[gd.selected.route_id].poly = poly;
			gd.plans[gd.selected.route_id].total_length = total_length;
			
			gd.markPointAsSelected();
			
		}, function (error) {
			
			alert('Возникла ошибка: ' + error.message);
			
		});		
	},
	
	onPlacemarkClicked: function (placemark, e){
		
		//gd.placemark = placemark ;
		
		var route_id = placemark.originalEvent.target.properties.get('route_id');
		var point_id = placemark.originalEvent.target.properties.get('point_id');
		
		//alert(route_id +',' + point_id);
		gd.pointSelected(route_id, point_id);
		
	},
	
	drawPolylin11e: function(segment){

	},
		
	createNewBusLine: function(){
		
	   // Добавим на карту схему проезда
		ymaps.route([
        'Москва, метро Маяковская',
		'Москва, метро Чеховская',
        'Москва, метро Охотный ряд'
		], {
			routingMode: 'masstransit',
			mapStateAutoApply: false,
		}).then(function (route) {
			gdMap.map.geoObjects.add(route);
			

			
			// Зададим содержание иконок начальной и конечной точкам маршрута.
			// С помощью метода getWayPoints() получаем массив точек маршрута.
			// Массив транзитных точек маршрута можно получить с помощью метода getViaPoints.
			var points = route.getWayPoints(),
				lastPoint = points.getLength() - 1;
			// Задаем стиль метки - иконки будут красного цвета, и
			// их изображения будут растягиваться под контент.
			points.options.set('preset', 'islands#redStretchyIcon');
			// Задаем контент меток в начальной и конечной точках.
			points.get(0).properties.set('iconContent', 'Точка отправления');
			points.get(lastPoint).properties.set('iconContent', 'Точка прибытия');

			var points_list = [];
			var points_objects = [];
			var route_id = gd.plans.length ;
			
			var number_modifier = route_id * 3 ;
			
			for (var i =0; i <= lastPoint; i++){
				
				points.get(i).properties.set('iconContent', 'Точка ' + ((i + 1 ) + number_modifier));
				
				//points.get(i).properties.set('balloonContent', '<input type="button" value="Двигать" onClick="gd.pointSelected(' + (gd.plans.length) + ',' + i + ');"><div  style="display: none;" onClick="gd.pointDelete(' + (gd.plans.length) + ',' + i + ');">Удалить</div>');
				
				points.get(i).properties.set('balloonContent', '');
				
				points.get(i).properties.set('route_id', (route_id));
				points.get(i).properties.set('point_id', (i));
				
				points.get(i).events.add('click', gd.onPlacemarkClicked);
				
				points_objects.push(points.get(i));
				
				//console.log();
				points_list.push(points.get(i).geometry._coordinates);
			}
			
			var moveList = '';
			
			var streets = [];
			var last_street = '';
			var poly = [];
			var last_coord = [];
			var coords_tmp = [];
			var total_length = 0 ;
			
			// Получаем массив путей.
			for (var i = 0; i < route.getPaths().getLength(); i++) {
				way = route.getPaths().get(i);
				segments = way.getSegments();
				last_coord = [];
				
				var prev_coords = [] ;
				
				for (var j = 0; j < segments.length; j++) {
					var street = segments[j].getStreet();
				
					coords_tmp = [];
					
					if (street != '' && streets.indexOf(street) == -1)
						streets.push(street);
					
					if (street != ''){
						last_street = street ;
					}					
					
					//console.log(street.length);
					
					//gdMap.drawPolyline(segments[j]);
					
					total_length += parseInt(segments[j].getLength());
					
					poly.push({
						coord: segments[j]._geometry.coordinates,
						name: last_street,
					});
					
				}
				
			}
			
			gd.plans.push({
				'route': route,
				'dots': points_list,
				'dots_obj': points_objects,
				'streets': streets,
				'poly': poly,
				'total_length': total_length,
			});

			

			//$('#list').append(moveList);
			
			}, function (error) {
			
			alert('Возникла ошибка: ' + error.message);
			
		});				
		
		
		
	},
	
};




 /*
   Функция возвращает окончание для множественного числа слова на основании числа  и массива окончаний
   @param  $number Integer Число на основе которого нужно сформировать окончание
   @param  $endingsArray  Array Массив слов или окончаний для чисел (1, 4, 5),
          например array('яблоко', 'яблока', 'яблок')
   @return String
 */
function niceEnding(number,endingArray){
number = number % 100;
if (number >= 11 && number <= 19) {
	ending = endingArray[2] ;
}
else {
	var i = number % 10;
	switch (i) {
	case 1:	ending = endingArray[0] ; break
	case 2:
	case 3:
	case 4:	ending = endingArray[1]; break
	default: ending = endingArray[2] ;
	}
}
return ending;
} 

</script>





</body>


</html>
