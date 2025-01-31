<?php
//print '<pre>' . print_r($_subtmp, true) . '</pre>';
include('../../../config.php');

class db_moscow_transport extends ActiveRecord\Model {
	static $table_name = 'moscow_transport';
}


$trolley = db_moscow_transport::find('all', array(
	'conditions' => array('type="trolley"'),
	'select' => 'id, name',
));

/*
$_trolley = array();
print '
<script>
plans = [';
foreach ($trolley as $_plan){
	print '{c_end: ' . $_plan->read_attribute('map_points1') . ',';
	print 'c_start:' . $_plan->read_attribute('map_points2') . '}';
}
print ']; </script>';
*/

//print '<pre>' . print_r($_trolley, true) . '</pre>';

?>

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
            width: 100%; height: 100%;
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
	<input type="button" style="display: none;" value="Создать новый маршрут" onClick="gd.createNewRoute();">
	<input type="button" style="display: none;" class="newLineBtn" value="Добавить линию" onClick="gd.createNewLine();">
	<input type="button" style="display: none;" class="newLineBtn" value="Сохранить" onClick="gd.saveNewRoute();">
</div>

<script>


gd = {
	
	routeName: '',
	selected_point_id: 0,
	plans: [],
	
	createNewRoute: function(){
		
		var name = prompt('Введите название маршрута');
		
		
		gd.routeName = name ;
		
		
		$('.newLineBtn').show();
	},
		
	oddOrEven: function(x) {
	  return ( x & 1 ) ? "odd" : "even";
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
		}, function(msg){
			
			alert('Сохранено. Обновите страницу для продолжения.');
			
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
		
		if (typeof(gd.selected) == "undefined")
			return ;

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
			
			for (var i =0; i <= lastPoint; i++){
				
				points.get(i).properties.set('iconContent', 'Точка ' + (i + 1));
				
				points.get(i).properties.set('balloonContent', '<input type="button" value="Двигать" onClick="gd.pointSelected(' + gd.selected.route_id + ',' + i + ');"><div  style="display: none;" onClick="gd.pointDelete(' + gd.selected.route_id + ',' + i + ');">Удалить</div>');
				
				//console.log();
				points_list.push(points.get(i).geometry._coordinates);
			}
			
			var streets = [];
			var last_street = '';
			var poly = [];
			var total_length = 0 ;
			
			
			// Получаем массив путей.
			for (var i = 0; i < route.getPaths().getLength(); i++) {
				way = route.getPaths().get(i);
				segments = way.getSegments();
				
				var prev_coords = [] ;
				
				for (var j = 0; j < segments.length; j++) {
					var street = segments[j].getStreet();

					if (street != '' && streets.indexOf(street) == -1)
						streets.push(street);
					
					if (street != '')
						last_street = street ;
					
					total_length += parseInt(segments[j].getLength());
					
					/*
					var segment_coords = segments[j]._geometry.coordinates ;
					
					if (j != 0){
						
						// если длина один
						if (segment_coords.length == 1){
							console.log('lets add ' + JSON.stringify(prev_coords[prev_coords.length - 1]));
							
							$.merge(segment_coords, [prev_coords[prev_coords.length - 1]]);
							
							//segments[j].getHumanAction()
							
							/*
								Нужно ещё из следующего сегмента достать кусок дороги и дописать в начало текущего
							*/
							/*
							if (typeof(segments[j +1]) != "undefined"){
								var nextSegment = segments[j +1]._geometry.coordinates;
								
								var tmp_coords = [nextSegment[0]];
								
								$.merge(tmp_coords, segment_coords);
								
								segment_coords = [];
								segment_coords = tmp_coords ;
								
								console.log('Pervoe is novogo segmenta ' + JSON.stringify(nextSegment[0]));
								
							}
							
							
							console.log('now is length is ' + JSON.stringify(segment_coords));
						}
						else {
							
							if (gd.oddOrEven(segment_coords.length) == 'odd'){
								/*
									Нужно ещё из следующего сегмента достать кусок дороги и дописать в конец текущего
								*/
								/*
								if (typeof(segments[j +1]) != "undefined"){
									var nextSegment = segments[j +1]._geometry.coordinates;

									$.merge(segment_coords, [nextSegment[0]]);

									console.log('Pervoe is novogo segmenta11 ' + JSON.stringify(nextSegment[0]));
									
								}
							}
							
						}
						
						// 
						
					}

					else {
						
						if (segment_coords.length != 1){
							if (gd.oddOrEven(segment_coords.length) == 'even'){
								
								//console.log('Up is even (next is '  + segments[j +1]._geometry.coordinates.length + ')');
								// если следующий - odd
								
								/*
									Нужно ещё из следующего сегмента достать кусок дороги и дописать в конец текущего
								*/
								/*
								if (typeof(segments[j +1]) != "undefined" && gd.oddOrEven(segments[j +1]._geometry.coordinates.length) == 'odd'){
									

									var nextSegment = segments[j +1]._geometry.coordinates;

									console.log('V konec togo 4to vishe ' + JSON.stringify(nextSegment[0]));
									
									$.merge(segment_coords, [nextSegment[0]]);

									console.log('Pervoe is novogo segmenta12 ' + JSON.stringify(nextSegment[0]));
								}	
														
							}			
						}
					
					}					
					
					prev_coords = segment_coords.slice() ;
					*/
					
					poly.push({
						coord: segments[j]._geometry.coordinates,
						name: last_street,
					});
					
				}
				
			}		
			
			
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
		
	onMapInit: function(){
		
	},
	
	createNewLine: function(){
		
	   // Добавим на карту схему проезда
		ymaps.route([
        'Москва, метро Маяковская',
		'Москва, метро Чеховская',
        'Москва, метро Охотный ряд'
		], {
			routingMode: 'masstransit',
			mapStateAutoApply: true,
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
				
				points.get(i).properties.set('balloonContent', '<div onClick="gd.pointSelected(' + (gd.plans.length) + ',' + i + ');">Двигать</div><div  style="display: none;" onClick="gd.pointDelete(' + (gd.plans.length) + ',' + i + ');">Удалить</div>');
				
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
			
			//
		}, function (error) {
			
			alert('Возникла ошибка: ' + error.message);
			
		});				
		
		
		
	},
	
};

</script>

<div id="map"></div>
<div id="list"></div>
</body>

</html>

