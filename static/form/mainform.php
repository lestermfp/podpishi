<?php

//print '<pre>' . print_r($_SERVER, true) . '</pre>';

$list = file('./html/apple_posts.csv');
$csv = array_map('str_getcsv', $list);


$apple_districts = array();

$prevDistrict = '';
foreach ($csv as $_line){
	
	$district = trim($_line[0]);
	
	if ($district != ''){
		$prevDistrict = $district;
	}
	else {
		$district = $prevDistrict;
	}
	
	if (!isset($apple_districts[$district]))
		$apple_districts[$district] = array(
			'value' => array(),
			'list' => array(),
		);
	
	$apple_districts[$district]['value'] = array_merge($apple_districts[$district]['value'], array(trim($_line[1])));
	$apple_districts[$district]['list'][trim($_line[1])] = explode(',', str_replace(array('.'), '', str_replace(', ', ',', (trim($_line[2])))));
	
	
	
	
	//exit();
	
}


ksort ($apple_districts);

$genderSelect_args = array(
	'<option value="male">Мужской</option>', '<option value="female">Женский</option>'
);

//if (mt_rand(1,2) == 1)
//	$genderSelect_args = array_reverse($genderSelect_args);

$genderSelect = implode('', $genderSelect_args);

//print '<pre>' . print_r($apple_districts, true) . '</pre>';
//exit();
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>Заявление в бюро</title>

    <!-- Bootstrap -->
	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

	<!-- Optional theme -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>

	<!-- Latest compiled and minified JavaScript -->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
	
  </head>
  <body style="padding: 15px;">
    <h1>Заявление в бюро</h1>

	<style>
		.orig_text {
				
			margin-top: 20px;
			line-height: 23px;
			font-size: 16px;		
			
		}
	</style>
<div class="row">

	<div class="col-xs-5" style="margin-bottom: 15px;">
		<input type="text" value="" class="form-control member_name" placeholder="Фамилия, имя и отчество">
		<small>(в родительном падеже)</small>
	</div>
	<div class="col-xs-5" style="margin-bottom: 15px;">
		<select class="form-control gender_select">
			<?=$genderSelect;?>
		</select>
	</div>	
		

	
	<div class="col-xs-5" style="margin-bottom: 15px;">
		<input type="text" value="" class="form-control address" placeholder="Фактический адрес проживания">
	</div>
	<div class="col-xs-5" style="margin-bottom: 15px;">
		<input type="text" value="" class="form-control birthdate" placeholder="Дата рождения">
	</div>
</div>
<div class="row">

	<div class="col-xs-5">
	<select class="form-control district_select">
	  <option>Выберите округ</option>
	  <?php
		foreach ($apple_districts as $_key => $_value){
			print '<option value="' . $_key . '">' . $_key . '</option>';
		}
	  ?>
	</select>
	
	</div>
	
	<div class="col-xs-5">
	<select class="form-control spawn_select">

	</select>
	
	</div>
	
	
</div>

<div class="row">
	<div class="col-xs-10">
	<div class="orig_text">
		Прошу рассмотреть настоящее заявление непосредственно в Бюро Партии «ЯБЛОКО», поскольку ранее мне было отказано в принятии в члены партии Региональным советом Московского отделения Партии «ЯБЛОКО» (далее – Региональный совет) решением № 144 от 21.12.2016.<br/>Согласно приложению к указанному решению, причинами отказа названы следующие обстоятельства, якобы имевшие место:
		
		<br/>
		<ul><li>Якобы при собеседовании я не смог объяснить, чем Партия «ЯБЛОКО» отличается от других партий,</li><li>Заявил, что не собираюсь вести постоянную работу в партии,</li><li>Не имел представления, чем должна заниматься партия между выборами,</li><li>Единственной причиной моего желания вступить в партию являлось, якобы, моё желание выдвигаться в муниципальные депутаты,</li><li>Но я не смог объяснить, чем буду заниматься в случае избрания меня муниципальным депутатом и не знал полномочия депутатов,</li><li>Демонстрировал несогласие с программными документами партии, её целями и задачами,</li><li>Демонстрировал неуважение к члену Регионального совета.</li><li>Что мои намерения враждебны и вовлекут региональное отделение в тяжёлый конфликт.</li></ul>
		
		<br/>Указанные обстоятельства не соответствуют действительности и являются вымышленными.</div>
	
	<textarea class="form-control plot_text" placeholder="" rows="6">В действительности со мной не проводилось собеседования, и описанные выше вопросы я ни с кем из членов Московского отделения Партии не обсуждал.
При подаче заявления Галине Михайловне Михалёвой со мной был проведён краткий опрос, занявший в общей сложности менее 5 минут, мне были заданы формальные вопросы на тему отношения к сталинизму и моей позиции по вопросу Крыма. Приём заявления прошёл в доброжелательной обстановке, разногласий или проблем при этом выявлено не было. С тех пор мне не звонили для уточнения моих позиций, я не был приглашен на заседание Регионального совета и не имел возможности лично ответить на возникшие ко мне претензии.</textarea>

	<div class="orig_text"><p>Об отказе в приёме в партию мне стало известно из <br/> 

		<select class="form-control main_reason_select">
			<option value="none">Письмо об отказе не приходило</option>
			<option value="ssmitrohin@gmail.com">письмо пришло с адреса ssmitrohin@gmail.com</option>
			<option value="ro@mosyabloko.ru">письмо пришло с адреса ro@mosyabloko.ru</option>
		</select>
		
	</p>
	</div>
	<div class="orig_text"><p>Как мне стало известно, решение об отказе в моём приёме в члены Партии на заседании Регионального совета было принято с грубыми нарушениями норм Устава Партии. Так, в соответствии с пунктом 6.7 Устава Партии, при наличии у членов постоянно действующего руководящего органа возражений о приёме в члены Партии на голосование выносится предложение об отказе в приёме в члены Партии; если предложение об отказе в приёме не набирает большинства голосов, гражданин считается принятым в члены Партии. В нарушение данной нормы на заседании не озвучивались возражения относительно персонально моего членства в партии, по моей кандидатуре не проводилось голосование.</p><p>
Полагаю, что произведённое Региональным советом «массовое» голосование по вопросу отказа в приёме в члены Партии по одинаковым (и, к тому же, надуманным) основаниям свидетельствует о явно произвольном характере такого отказа, противоречит не только букве Устава Партии, но и принципам демократии, необходимым в демократической партии.</p><p>
При таких обстоятельствах отказ Регионального совета в моём приёме в члены Партии не может быть признан обоснованным, в связи с чем полагаю единственно возможным и правильным обратиться с настоящим заявлением в Бюро Партии на основании пункта 6.9 Устава Партии.<br/>
Подтверждаю свою готовность присутствовать при рассмотрении вопроса о моём приёме в члены Партии и давать необходимые объяснения о своих намерениях и убеждениях.</p>
</div>
	
	</div>
	<div></div>
	
	
</div>
<br/><br/>
<div class="btn btn-primary" onclick="apple_form.generatePdf();">Готово</div>
<br/><br/>
	<div>Если хотите редактировать весь документ, найти его можно <a href="https://docs.google.com/document/d/1L75ZdkomRGOSFNv8-bYoZF1MIu0ZJxIoktJ9SXl46mk/edit?usp=sharing" target="_blank">тут</a></div>

  </body>
</html>

<script>

apple_districts = <?=json_encode($apple_districts);?>;


apple_form = {
	
	init: function(){
		
		
		apple_form.bindings();
		
	},
	
	bindings: function(){
		
		$('.district_select').change(function(){
			
			var region = $(this).val();
			
			var list = apple_districts[region].list ;
			
			var html = '';
			
			console.log(list);
			
			$.each(list, function(key){
				
				for (var n = 0; n < this.length; n++){
					
					if (this[n] == '')
						continue;
					
					html += '<option value="' + key + '">' + this[n] + '</option>';
					
				}
				
			});
			
			
			$('.spawn_select').html(html);
			
		});
		
	},
	
	
	generatePdf: function(){
		var data = {};
		
		data.district = $('.district_select').val();
		data.spawn = $('.spawn_select :selected').attr('value');
		data.gender = $('.gender_select :selected').attr('value');
		data.main_reason = $('.main_reason_select :selected').attr('value');
		
		data.plot_text = $('.plot_text').val();
		
		data.member_name = $('.member_name').val();
		data.address = $('.address').val();
		data.birthdate = $('.birthdate').val();
		
		
		if (data.member_name == ''){
			alert('Вы не указали имя');
			return ;
		}
		
		data.context = 'generatePdf';

		smartAjax('./pdf_engine.php', data, function(msg){
			
			document.location.href = msg.href ;
			
		}, function(msg){
			
			alert('Error');
		}, 'generatePdf', 'POST');	
		
	},
};

GLOBAL_OPTIONS = {
	loaderId: 0,
	isAjaxInProgress: false,
} ;
GLOBAL_IMAGES = {} ;
GLOBAL_LANG = {} ;
GLOBAL_AJAX = {} ;


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
		
		if (GLOBAL_OPTIONS.loaderId != 0){
			clearInterval(GLOBAL_OPTIONS.loaderId);
			
			var loader = $('#loaderPopup');
			
			loader.hide();
			loader.find('img').css('right', '0px');
		
		}
		
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
					swal(msg.error_text);
				}
				else {
					console.log(msg.error);
				}			
			}					
		}
		
	};
	
	innerHandler.onSuccess = onSuccess ;
	innerHandler.onError = onError ;
	
	if (GLOBAL_OPTIONS.loaderId != 0)
		clearInterval(GLOBAL_OPTIONS.loaderId);
	
	GLOBAL_OPTIONS.loaderId = setInterval(function(){
		
		var loader = $('#loaderPopup');
		
		loader.show(0, function(){
			$(this).find('img').css('right', '200px');
		
		});
		//loader.
		
	}, 350);
	
	shortAjax(path, data, innerHandler, unique_name, method);
	
}


$(document).ready(function(){
	apple_form.init();
});
</script>