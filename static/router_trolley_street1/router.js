GLOBAL_AJAX  = {};

ymaps.ready(init);

function init() {
    gdMap.map = new ymaps.Map("map", {
            center: [55.75710630790187, 37.656742761104105],
            zoom: 13
        }, {
            searchControlProvider: 'yandex#search'
        });

		
	gdMap.map.events.add('click', function (e) {
		
		gdMap.clickedCoords = e.getSourceEvent().originalEvent.coords;
		
		gd.onMapClicked();
	});
	
	gd.onMapInit();
	
}

// Shorter variant of the jquery ajax function.
function shortAjax(path, data, onSuccess, unique_name, method){

	if (typeof(method) == "undefined")
		method = "GET";

	if (unique_name === undefined){
		$.ajax({
			type: method,
			url: path,
			cache: false,
			data: data,
			success: onSuccess,
		});	
	}
	else {
		// Deletes multi-queries
		if (GLOBAL_AJAX[unique_name] !== undefined){
			if (typeof(GLOBAL_AJAX[unique_name]) !== undefined)	GLOBAL_AJAX[unique_name].abort();
		}
		
		GLOBAL_AJAX[unique_name] = $.ajax({
			type: method,
			url: path,
			cache: false,
			data: data,
			success: onSuccess,
		});		
	}
}

function smartAjax(path, data, onSuccess, onError, unique_name, method){
	
	if (typeof(method) == "undefined")
		method = "GET";
	
	var innerHandler = function(msg){
		//var steamid = arguments.callee.steamid ;
		
		try {
			
			msg = JSON.parse(msg);
			
		}
		catch (e){
			msg = {};
			msg.error = 'incorrectJSON';
		}
	
			
		if (typeof(arguments.callee.onSuccess) == "function" && msg.error == "false"){
			arguments.callee.onSuccess.apply(null, [msg]);
		}		


		
		if (typeof(arguments.callee.onError) == "function" && msg.error != "false"){
			arguments.callee.onError.apply(null, [msg]);
		}
		else {
			if (msg.error != "false"){
				if (msg.error_text !== undefined){
					mainSite.postError(msg.error_text);
				}
				else {
					console.log(msg.error);
				}			
			}					
		}
		
	};
	
	innerHandler.onSuccess = onSuccess ;
	innerHandler.onError = onError ;
	
	shortAjax(path, data, innerHandler, unique_name, method);
	
}

gdMap = {
	
	map: {},
	
	elecrified: ['улица Большая Лубянка', 'проспект Мира', 'проспект Мира (дублёр)', 'Останкинский проезд', 'улица Академика Королёва', 'Ботаническая улица'],
	
	loadRouteByAjax: function(){
		
		$('#list').html('');
		gdMap.map.geoObjects.remove(gdMap.current_route);
		
		var selected_plan = $('#plansList :selected').attr('value');
		
		smartAjax('./ajax_router.php', {
			context: 'loadRouteByAjax',
			selected_plan: selected_plan,
		}, function(msg){
	
			gdMap.plan = msg.plan ;
			gdMap.plan_backward = msg.plan_backward ;
			gdMap.plan_id = msg.plan_id ;
			gdMap.plan_name = msg.plan_name ;
				
			gdMap.prepareRouteArray(gdMap.plan);
			
		}, function(msg){
			
			alert(msg.error_text);
			
		});	
		
	},
	
	switchPlans: function(){
		
		gdMap.map.geoObjects.remove(gdMap.current_route);
		
		var tmp = gdMap.plan;
		gdMap.plan = gdMap.plan_backward ;
		//gdMap.plan.features = gdMap.plan.features.reverse();
		gdMap.plan_backward = tmp;
		
		gdMap.prepareRouteArray(gdMap.plan);
		
	},
	
	saveRouteByAjax: function(){
		
		var selected_plan = $('#plansList :selected').attr('value');
		
		
		smartAjax('./ajax_router.php?context=saveRouteByAjax', {
			selected_plan: gdMap.plan_id,
			plan: gdMap.plan,
			segments: gdMap.plan_segments,
		}, function(msg){
			
			alert('Сохранено');
			
		}, function(msg){
			
			alert(msg.error_text);
			
		}, 'saveRouteByAjax', 'POST');	
		
	},
	
	prepareRouteArray: function(raw_route){
		
		var clear_route = [] ;
		var type = '';
		for (var i = 0; i < raw_route.features.length; i++){
			var item = raw_route.features[i] ;
			
			//if (item.geometry.type != 'Point')
				//continue ;
			
			//console.log($(item.properties.popupContent).text());
			
			type = 'wayPoint';
			
			if (i == 0 || i == raw_route.features.length){
				type = 'wayPoint';
			}
			
			//clear_route.push([item.geometry.coordinates[1], item.geometry.coordinates[0]]);
			clear_route.push({ type: type, point: [item.geometry.coordinates[1], item.geometry.coordinates[0]] });
			
			//if (item.properties.id == 'mar_point_numeric_15')
				//break ;
		}
		
		//console.log(clear_route);
		
		//return ;
		
		gdMap.wr = clear_route ;
		gdMap.drawRoute(clear_route);
		
	},
	
	pointSelected: function(i){
		
		gdMap.selected_point_id = i ;
		//var item = plans[0]['c_start'].features[i] ;
		
		//alert($(item.properties.popupContent).text());
	},
	
	pointDelete: function(point_id){
		
		var tmp = [];
		
		for (var i = 0; i < gdMap.plan.features.length; i++){
			var item = gdMap.plan.features[i];
			
			if (i == point_id)
				continue ;
			
			tmp.push(item);
		}
		
		gdMap.plan.features = [] ;
		gdMap.plan.features = tmp ;
		
		gdMap.map.geoObjects.remove(gdMap.current_route);
		
		gdMap.prepareRouteArray(gdMap.plan);
		
	},
	
	onMapClicked: function(){
		
		return ;
		gdMap.plan.features[gdMap.selected_point_id].geometry.coordinates = [gdMap.clickedCoords[1], gdMap.clickedCoords[0]];
		
		console.log(gdMap.plan.features[gdMap.selected_point_id].geometry.coordinates);
		
		gdMap.map.geoObjects.remove(gdMap.current_route);
		
		gdMap.prepareRouteArray(gdMap.plan);
		
	},
	
	drawRoute: function(route_info){
		
		gdMap.plan_segments = [];
		
	   // Добавим на карту схему проезда
		ymaps.route(route_info, {
			routingMode: 'masstransit',
			mapStateAutoApply: false,
		}).then(function (route) {
			gdMap.map.geoObjects.add(route);
			
			gdMap.current_route = route;
			
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

			
			for (var i =0; i <= lastPoint; i++){
				points.get(i).properties.set('iconContent', 'Точка ' + i);
				points.get(i).properties.set('balloonContent', '<div onClick="gdMap.pointSelected(' + i + ');">Двигать</div><div onClick="gdMap.pointDelete(' + i + ');">Удалить</div>');
			}
			
			gdMap.points = points;
			// Проанализируем маршрут по сегментам.
			// Сегмент - участок маршрута, который нужно проехать до следующего
			// изменения направления движения.
			// Для того, чтобы получить сегменты маршрута, сначала необходимо получить
			// отдельно каждый путь маршрута.
			// Весь маршрут делится на два пути:

			var moveList = 'Маршрут ' + gdMap.plan_name +  ',</br>',
				way,
				segments;
				
			var elecrified_distance = 0 ;	
			var total_length = 0;
			var last_street = '';
				
			// Получаем массив путей.
			for (var i = 0; i < route.getPaths().getLength(); i++) {
				way = route.getPaths().get(i);
				segments = way.getSegments();
				
				for (var j = 0; j < segments.length; j++) {
					var street = segments[j].getStreet();

					if (street != '')
						last_street = street ;
					
					if (street != '' && gdMap.plan_segments.indexOf(street) == -1)
						gdMap.plan_segments.push(street);
					
					//console.log('street ' + street);
					
					if (gdMap.elecrified.indexOf(last_street) != -1){
						//elecrified_distance += parseInt(segments[j].getLength());
						//gdMap.ww = segments[j];
						
						
						
					}
					else {
						//gdMap.drawPolyline(segments[j]);
					}
					
					total_length += parseInt(segments[j].getLength());
					
					//moveList += ('Едем ' + segments[j].getHumanAction() + (street ? ' на ' + street : '') + ', проезжаем ' + segments[j].getLength() + ' м.,');
					//moveList += '</br>'
				}
			}
			
			moveList += 'Общая длина - ' + (total_length / 1000).toFixed(1) + ' км. </br>'
			moveList += 'Из них под проводами - ' + (elecrified_distance / 1000).toFixed(2) + ' км.</br>'

			
			
			//moveList += 'Останавливаемся.';
			
			// Выводим маршрутный лист.
			
			$('#list').append(moveList);
			
		}, function (error) {
			
			alert('Возникла ошибка: ' + error.message);
			
		});		
	},
	
	drawPolyline: function(segment){
			
		// Создаем ломаную с помощью вспомогательного класса Polyline.
		var myPolyline = new ymaps.Polyline(segment._geometry.coordinates, {
				// Описываем свойства геообъекта.
				// Содержимое балуна.
				balloonContent: "Ломаная линия"
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
};

routes = {
	
	home: [
        'Москва, Лубянская площадь',
        'Ботаническая ул., 29, корп. 1, Москва',
    ],
	

	
};

/*
        {
            point: 'Москва, метро Маяковская',
            // метро "Молодежная" - транзитная точка
            // (проезжать через эту точку, но не останавливаться в ней).
            type: 'viaPoint'
        },
*/