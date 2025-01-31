<?php
//print '<pre>' . print_r($_subtmp, true) . '</pre>';
/*
include('../../../config.php');

class db_moscow_transport extends ActiveRecord\Model {
	static $table_name = 'moscow_transport';
}


$trolley = db_moscow_transport::find('all', array(
	'conditions' => array('type="bus" AND id=405'),
	'select' => 'id, name, map_points1',
));


$_trolley = array();

foreach ($trolley as $_plan){
	break ;
	$args = json_decode($_plan->read_attribute('map_points1'), true);

	foreach ($args as $_arg){
		$_arg['poly'][0]['t_name'] = $_plan->read_attribute('name');
		$_arg['streets'] = $_arg['streets'];
		$_trolley[] = $_arg ;
	}
}


//print '<pre>' . print_r($_trolley, true) . '</pre>';
*/


$list = scandir('streets');

$_trolley = array(
	
);

$streets_names = array();

if (isset($_GET['view'])){
	
	
	
	
	//print '<pre>' . print_r($list, true) . '</pre>';
	
	foreach ($list as $_file){
		

		if ($_file == '.' OR $_file == '..')
			continue ;
		
		//print $_file ;
		
		$content = file_get_contents('./streets/' . $_file);
		
		$content = unserialize($content);
		
		//print '<pre>' . print_r($content, true) . '</pre>';
		
		$points = json_decode($content['map_points1'], true);
		
		$streets_names[$_file] = $content['name'] ;
		
		$_trolley[] = $points;
		//print $content ;
		//exit();
		
	}
	
	//print '<pre>' . print_r($_trolley, true) . '</pre>';
	
}

?>
<script>

poly = <?=json_encode($_trolley)?>;


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
    </style>
</head>

<body>
<div style="margin-bottom: 15px;">
	
	
	
	<select id="plansList" style="display: none;">
	
		
		<?php
			foreach ($trolley as $_plan){
				//print '<option value="' . $_plan->read_attribute('id') . '">' . $_plan->read_attribute('name') . '</option>';
			}
		?>
	</select>
	<input type="button" value="Новая улица" onClick="gd.createNewBusRoute();">
	<input type="button" style="display: none;" class="newLineBtn" value="Добавить отрезок" onClick="gd.createNewBusLine();">
	<input type="button" style="display: none;" class="newLineBtn" value="Улица готова, сохранить" onClick="gd.saveNewRoute();">
</div>

<script>


gd = {
	
	routeName: '',
	selected_point_id: 0,
	plans: [],
	elecrified: [],
	
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
		
		for (var i = 0; i < poly.length; i++){
			gd.coords = [] ;
			
			for (var n = 0; n < poly[i].length; n++){
				
				$.each(poly[i][n].poly, function(){
					$.merge(gd.coords, this.coord);
					
					if (gd.elecrified.indexOf(this.name) == -1 && this.name != "")
						gd.elecrified.push(this.name);
					
				});
				
			}
			
			gd.drawPolyline(gd.coords, poly[i][0].poly[0].t_name);
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
				strokeColor: "#000000",
				// Ширина линии.
				strokeWidth: 4,
				// Коэффициент прозрачности.
				strokeOpacity: 0.5
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

</script>

<div class="mainContainer">
	<div id="map"></div>
	<div style="width: 20%; vertical-align: top;">
		Улиц добавлено: <?=count($list) - 2?><br/>
		<select style="display: none;">
			<option route_id="">Имя</option>
		</select>
	</div>
</div>


<div style="display: none;">
<?php

foreach ($streets_names as $_key => $_value)
	print $_key . '=' . $_value . "\r\n";
?>
</div>

<div id="list"></div>
</body>

</html>

