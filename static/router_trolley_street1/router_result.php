<?php
//print '<pre>' . print_r($_subtmp, true) . '</pre>';
include('../../../config.php');

class db_moscow_transport extends ActiveRecord\Model {
	static $table_name = 'moscow_transport';
}


$trolley = db_moscow_transport::find('all', array(
	'conditions' => array('type="trolley2" AND convertable=0'),
	'select' => 'id, name, map_points1',
));


$_trolley = array();

foreach ($trolley as $_plan){
	
	$args = json_decode($_plan->read_attribute('map_points1'), true);

	
	foreach ($args as $_arg){
		$_arg['poly'][0]['t_name'] = $_plan->read_attribute('name');
		$_arg['streets'] = $_arg['streets'];
		$_trolley[] = $_arg ;
	}
}

$buses = db_moscow_transport::find('all', array(
	'conditions' => array('type="bus"'),
	'select' => 'id, name, map_points1',
));


$_buses = array();
$tmp = array();

foreach ($buses as $_plan){
	
	$args = json_decode($_plan->read_attribute('map_points1'), true);

	$tmp = array();

	foreach ($args as $_arg){
		$_arg['poly'][0]['t_name'] = $_plan->read_attribute('name');
		$_arg['streets'] = $_arg['streets'];
		$tmp[] = $_arg ;
	}
	
	$_buses[] = $tmp ;
}


//print '<pre>' . print_r($_trolley, true) . '</pre>';

?>
<script>

trolleys = <?=json_encode($_trolley)?>;
poly = <?=json_encode($_buses)?>;
//poly = [];
</script>
<!DOCTYPE html>

<html>

<head>
    <title>Примеры. Построение маршрута</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <!-- Если вы используете API локально, то в URL ресурса необходимо указывать протокол в стандартном виде (http://...)-->
    <script src="https://api-maps.yandex.ru/2.1/?lang=ru_RU" type="text/javascript"></script>
    <script src="https://yandex.st/jquery/1.6.4/jquery.min.js" type="text/javascript"></script>
    <script src="router.js" type="text/javascript"></script>
	
	<style>
        body, html {
            padding: 0;
            margin: 0;
            width: 100%;
            height: 100%;
            font-family: Arial;
            font-size: 14px;
        }
        #list {
            padding: 10px;
        }
        #map {
            width: 70%; height: 100%;
        }
		
		.mainContainer div {
			display: inline-block;
		}
		
		#result_table {
			border: 1px solid gray;
		}
		
		#result_table td {
			padding: 5px;			
		}
		
    </style>
</head>

<body>
<div>
	<select id="plansList" style="display: none;">
		<?php
			foreach ($trolley as $_plan){
				print '<option value="' . $_plan->read_attribute('id') . '">' . $_plan->read_attribute('name') . '</option>';
			}
		?>
	</select>
	<input type="button" value="Создать автобусный маршрут" onClick="gd.createNewBusRoute();">
	<input type="button" style="display: none;" class="newLineBtn" value="Добавить линию" onClick="gd.createNewBusLine();">
	<input type="button" style="display: none;" class="newLineBtn" value="Сохранить" onClick="gd.saveNewRoute();">
</div>

<script>


gd = {
	
	routeName: '',
	selected_point_id: 0,
	plans: [],
	selected: {},
	elecrified: [],
	results: [],
	current_route: [],
	
	createNewBusRoute: function(){
		
		var name = prompt('Введите название маршрута');
		
		
		gd.routeName = name ;
		
		
		$('.newLineBtn').show();
		gd.createNewBusLine();
	},
	
	prepareElectrifiedList: function(){
		
		for (var i = 0; i < trolleys.length; i++){
			
			$.each(trolleys[i].poly, function(){
				
				if (this.name == 'МКАД')
					return ;
				
				if (gd.elecrified.indexOf(this.name) == -1 && this.name != "")
					gd.elecrified.push(this.name);
				
			});
			
		}
		
	},
	
	drawBusLineOneAfterAnother: function(number){
	
		if (typeof(gd.current_route) != "undefined"){
			$.each(gd.current_route, function(){
				gdMap.map.geoObjects.remove(this);
			});
			gd.current_route = [];
		}
			
		if (typeof(number) == "undefined")
			number = gd.number;
		
		if (typeof(poly[number]) == "undefined"){
			alert('Gotovo');
		}
		
		



		gd.elecrified_distance = 0 ;	
		gd.total_length = 0;
		
		gd.plans = [];
		gd.selected.route_id = 0;
		gd.plans[0] = {};
		
		gd.routesQueue = 0 ;
		$.each(poly[number], function(){
			gd.routesQueue++ ;
				gd.prepareRouteArray(this.dots);
		});
		
		gd.routeName = poly[number][0].poly[0].t_name ;
		
		

		
		//gd.saveNewRoute();
		
		gd.number = number ;
		//gd.number++ ;
		
		setTimeout(function(){
			



	
			
			//gd.current_route = [];
			
			//gd.drawBusLineOneAfterAnother();
		}, 3000);
				
	},
	
	drawRouteResults: function(){
		gd.results.push({
			'name': gd.routeName,
			'total_length': gd.total_length,
			'elecrified_distance': gd.elecrified_distance,
			'wired_percentage': ((gd.elecrified_distance / gd.total_length) * 100).toFixed(0),
		});

		$('#result_table tbody').append('<tr><td>' + gd.routeName + '</td><td>' + gd.total_length + '</td><td>' + gd.elecrified_distance + '</td><td>' + ((gd.elecrified_distance / gd.total_length) * 100).toFixed(0) + '</td></tr>');		

		/*
		var next = $('.mainContainer select :selected').next();
		
		if (next.length != 0){
			next.prop('selected', true);
			gd.drawBusLineOneAfterAnother(next.attr('route_id'));
		}
		*/
		
		
	},
	
	alphanum: function (a, b) {

		var a = $(a).text();
		var b = $(b).text();

	  function chunkify(t) {
		var tz = [], x = 0, y = -1, n = 0, i, j;

		while (i = (j = t.charAt(x++)).charCodeAt(0)) {
		  var m = (i == 46 || (i >=48 && i <= 57));
		  if (m !== n) {
			tz[++y] = "";
			n = m;
		  }
		  tz[y] += j;
		}
		return tz;
	  }

	  var aa = chunkify(a);
	  var bb = chunkify(b);

	  for (x = 0; aa[x] && bb[x]; x++) {
		if (aa[x] !== bb[x]) {
		  var c = Number(aa[x]), d = Number(bb[x]);
		  if (c == aa[x] && d == bb[x]) {
			return c - d;
		  } else return (aa[x] > bb[x]) ? 1 : -1;
		}
	  }
	  return aa.length - bb.length;
	},
	
	naturalSortSelect: function(target){




		var options = target.find('option');
		options.detach().sort(gd.alphanum);
		options.appendTo(target);                          // Re-attach to select
		
		target.prepend('<option>Не выбрано</option>');


		
	},
		
	drawAllRoutesToTable: function(){
		
		if (typeof(gd.tabled_number) == "undefined")
			gd.tabled_number = 0 ;
		
		
		
	},
		
	onMapInit: function(){
		gd.prepareElectrifiedList();
		
		
		var html = '';
		var replaces = ['Катя', 'Настя', 'Автобус', 'автобу','с ','Авсобу','Автобк', 'автобус', '(до ИЯИ)', 'катя', 'Автобкс', 'Авсобус', ' '];
		
		for (var i = 0; i < poly.length; i++){
						
			var t_name = poly[i][0].poly[0].t_name;

			for (var n = 0; n < replaces.length; n++)
				t_name = t_name.replace(replaces[n], '');

			html += '<option route_id="' +  i +'">' + t_name + '</option>';
			
		}
		
		var select = $('.mainContainer select');
		
		select.before('<div>Всего: ' + poly.length + '</div><br/>');
		select.html(html);
		gd.naturalSortSelect(select);
		
		$('.mainContainer select option').first().prop('selected', true);
		
		//gd.drawBusLineOneAfterAnother(1);
		
		return ;
		for (var i = 0; i < poly.length; i++){
			gd.coords = [] ;
			
			$.each(poly[i].poly, function(){
				$.merge(gd.coords, this.coord);
				
				if (gd.elecrified.indexOf(this.name) == -1 && this.name != "")
					gd.elecrified.push(this.name);
				
			});
			
			gd.drawPolyline(gd.coords, poly[i].poly[0].t_name);
		}
			
		/*
		for (var i = 0; i < poly.length; i++)
			$.each(poly[i], function(){
				gd.drawPolyline(this.coord);
			});
		*/
	},
	
	drawPolyline: function(coord, balloonContent){
			
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
				strokeColor: "#ff0000",
				// Ширина линии.
				strokeWidth: 5,
				// Коэффициент прозрачности.
				strokeOpacity: 0.9,
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
			type: 'bus',
		}, function(msg){
			
			alert('Сохранено. Обновите страницу для продолжения.');
			
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
			mapStateAutoApply: true,
		}).then(function (route) {
						
			gdMap.map.geoObjects.add(route);
			
			gd.current_route.push(route) ;

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
			
			for (var i =0; i <= lastPoint; i++){
				
				points.get(i).properties.set('iconContent', 'Точка ' + i);
				
				points.get(i).properties.set('balloonContent', '<input type="button" value="Двигать" onClick="gd.pointSelected(' + gd.selected.route_id + ',' + i + ');"><div  style="display: none;" onClick="gd.pointDelete(' + gd.selected.route_id + ',' + i + ');">Удалить</div>');
				
				//console.log();
				points_list.push(points.get(i).geometry._coordinates);
			}
			
			var moveList = 'Маршрут ' + gd.routeName +  ',</br>',
				way,
				segments;
				
			var streets = [];
			
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
						gd.elecrified_distance += parseInt(segments[j].getLength());

						//gdMap.drawPolyline(segments[j]);
						//gdMap.ww = segments[j];
					}
					else {
						
					}
					
					gd.total_length += parseInt(segments[j].getLength());
					
					
					poly.push({
						coord: segments[j]._geometry.coordinates,
						name: last_street,
					});					
					
				}
				
			}		
			gd.routesQueue-- ;
			
			if (gd.routesQueue == 0){
				gd.drawRouteResults();
			}
	
			moveList += 'Общая длина - ' + (gd.total_length / 1000).toFixed(1) + ' км. </br>'
			moveList += 'Из них под проводами - ' + (gd.elecrified_distance / 1000).toFixed(1) + ' км.</br>'

			
			
			//moveList += 'Останавливаемся.';
			
			// Выводим маршрутный лист.
			
			//$('#list').html(moveList);			
			
			
			gd.plans[gd.selected.route_id].route = route;
			gd.plans[gd.selected.route_id].dots = points_list;
			gd.plans[gd.selected.route_id].streets = streets;
			gd.plans[gd.selected.route_id].poly = poly;
			gd.plans[gd.selected.route_id].total_length = total_length;



			
			
		}, function (error) {
			
			alert('Возникла ошибка: ' + error.message);
			
		});		
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
			
			for (var i =0; i <= lastPoint; i++){
				
				points.get(i).properties.set('iconContent', 'Точка ' + (i + 1));
				
				points.get(i).properties.set('balloonContent', '<input type="button" value="Двигать" onClick="gd.pointSelected(' + (gd.plans.length) + ',' + i + ');"><div  style="display: none;" onClick="gd.pointDelete(' + (gd.plans.length) + ',' + i + ');">Удалить</div>');
				
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

</script>

<div class="mainContainer">
	<div id="map"></div>
	<div style="width: 19%; vertical-align: top;">
		<select onchange="gd.drawBusLineOneAfterAnother($(this).find(':selected').attr('route_id'));">
			<option route_id="">Имя</option>
		</select>
	</div>
</div>
<div id="list"></div>

<table id="result_table" border="1">
	<thead>
		<tr>
			<td>Название</td>
			<td>Общая длина</td>
			<td>Длина под проводами</td>
			<td>% под проводами</td>
		</tr>
	</thead>
	<tbody>

	</tbody>
</table>
</body>

</html>

