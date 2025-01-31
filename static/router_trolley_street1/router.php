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
            width: 100%; height: 500px;
        }
    </style>
</head>

<body>
<div>
	<select id="plansList">
		<?php
			foreach ($trolley as $_plan){
				print '<option value="' . $_plan->read_attribute('id') . '">' . $_plan->read_attribute('name') . '</option>';
			}
		?>
	</select>
	<input type="button" value="Открыть" onClick="gdMap.loadRouteByAjax();">
	<input type="button" style="display: none;" value="Сменить направление" onClick="gdMap.switchPlans();">
	<input type="button" value="Сохранить" onClick="gdMap.saveRouteByAjax();">
</div>
<div id="map"></div>
<div id="list"></div>
</body>

</html>

