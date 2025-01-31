<?php

//header('Location: https://podpishi.org/metro');

//exit();

$_CFG['meta'] = array(
    'bicycle' => array(
        'property="og:url"' => 'https://podpishi.org/bicycle',
        'property="og:title"' => 'Требуйте велодорожки!',
        'property="og:type"' => 'В Москве, занимающей 5-е место в мире по пробкам, развитие велоинфрастуктуры могло бы внести серьезный вклад в решение транспортной проблемы. Подпишите петицию!',
        'property="og:description"' => 'В Москве, занимающей 5-е место в мире по пробкам, развитие велоинфрастуктуры могло бы внести серьезный вклад в решение транспортной проблемы. Подпишите петицию!',
        'property="og:image"' => 'https://podpishi.org/static/images/velo_soc.jpg',
        'property="og:image:width"' => '900',
        'property="og:image:height"' => '605',
        'name="twitter:card"' => 'summary',
        'name="twitter:site"' => 'https://podpishi.org/bicycle',
        'name="twitter:title"' => 'Требуйте велодорожки!',
        'name="twitter:description"' => 'В Москве, занимающей 5-е место в мире по пробкам, развитие велоинфрастуктуры могло бы внести серьезный вклад в решение транспортной проблемы. Подпишите петицию!',
        'name="twitter:image:src"' => 'https://podpishi.org/static/images/velo_soc.jpg',
        'name="twitter:domain"' => 'https://podpishi.org/',
    ),
    'trolley' => array(
        'property="og:url"' => 'https://podpishi.org/trolley',
        'property="og:title"' => 'Троллейбус уничтожают!',
        'property="og:type"' => 'В центре Москвы перестали ходить троллейбусы. В июле от некогда самой большой в мире сети осталось 12 маршрутов. К сентябрю 2020 года планируется заменить все троллейбусы на дизельные автобусы. Это навсегда.',
        'property="og:description"' => 'В центре Москвы перестали ходить троллейбусы. В июле от некогда самой большой в мире сети осталось 12 маршрутов. К сентябрю 2020 года планируется заменить все троллейбусы на дизельные автобусы. Это навсегда.',
        'property="og:image"' => 'https://podpishi.org/static/images/traol_soc.jpg',
        'property="og:image:width"' => '900',
        'property="og:image:height"' => '605',
        'name="twitter:card"' => 'summary',
        'name="twitter:site"' => 'https://podpishi.org/trolley',
        'name="twitter:title"' => 'Троллейбус уничтожают!',
        'name="twitter:description"' => 'В центре Москвы перестали ходить троллейбусы. В июле от некогда самой большой в мире сети осталось 12 маршрутов. К сентябрю 2020 года планируется заменить все троллейбусы на дизельные автобусы. Это навсегда.',
        'name="twitter:image:src"' => 'https://podpishi.org/static/images/traol_soc.jpg',
        'name="twitter:domain"' => 'https://podpishi.org/',
    ),
);


$template_name = 'bicycle';

if (isset($_GET['parts']['1']) AND $_GET['parts'][1] == 'trolley')
	$template_name = $_GET['parts'][1];

$template = new gdTemplate('index_podpishi');

$tmp_activeTab = 'velo';
if ($template_name == 'trolley')
	$tmp_activeTab = '';

$template_params = $_CFG['template_params'][$template_name] ;

$meta = '';
foreach ($_CFG['meta'][$template_name] as $_key => $_value)
	$meta .= '		<meta '. $_key . ' content="' . $_value . '" />' . "\r\n";

$counter_value = 0;

if ($template_name == 'bicycle'){
	
	$_petitions = db_old_appeals::count(array(
		'conditions' => "`destination` IN ('meriya_velo')",
	));
	
	$counter_value += $_petitions;	
}
else {
	
	$counter_value = 5342 + 265 + 350 + 1362 + 571 + 1695 + 348 + 730 + 221;
	
	$_petitions = db_old_appeals::count(array(
		'conditions' => "`destination` IN ('meriya','mosgorduma')",
	));
	
	$counter_value += $_petitions;
	
}

$template->set(array(
	'{tmp_activeTab}' => $tmp_activeTab,
	'{title}' => $_CFG['meta'][$template_name]['property="og:title"'],
	'{meta}' => $meta,
	'{petition_title}' => $template_params['petition_title'],
	'{template_name}' => $template_name,
	'{counter_value}' => $counter_value,
	'{counter_sklonenie}' => getNumEnding($counter_value, array('человек<br/> подписал', 'человека<br/> подписало', 'человек<br/> подписали'))
));

print $template->draw();


$petition_texts = array();

foreach ($template_params['petitions'] as $_key => $_params){
	if (isset($_params['disabled']) AND $_params['disabled'] == true){
		
		unset($template_params['petitions'][$_key]);
		
		continue ;
	}
	
	$petition_texts[$_params['id']] = str_replace('\n', "\n\r", $_CFG['petition_text'][$_params['id']]) ;
	
	//$petition_texts[$_params['id']] = str_replace('\n', "\n\r", $_CFG['petition_text'][$_params['id']]) ;
	
}

//print '<pre>' . print_r($petition_texts, true) . '</pre>';
//exit();

if (!isset($_SESSION['prefilled']))
	$_SESSION['prefilled'] = array();

$prefilled_array = $_SESSION['prefilled'];

if (!isset($_GET['prefilled']))
	$prefilled_array = array();

?>


<style>
	.petition_nav__item, .petition_nav__item span.text_link:hover {
		cursor: pointer ;
	}
	
	.petition__textarea {
		min-height: 650px;
	}
	
	.send__button {
		min-width: 330px; 
	}
</style>

<script>

podpishi = {
	
	petition_texts: <?=json_encode($petition_texts);?>,
	petitions: <?=json_encode($template_params['petitions'])?>,
	template_name: <?=json_encode($template_name);?>,
	
	prefilled: <?=json_encode($prefilled_array)?>,
	
	addressInfo: {
		
	},
	
	active: {
		
	},
	
	is_sending: false,
	
	init: function(){
		
		podpishi.petition_nav = $('.petition_nav');
		
		podpishi.bindings();
		
		podpishi.drawTabs();
		
		podpishi.setDefaultTab();
		
		podpishi.sketchInit();
		
		podpishi.drawPrefilled();
		
		if (podpishi.template_name != 'trolley')
			$('.president').hide();
		
	},
	
	bindings: function(){
		
		//podpishi.petition_nav.on('click', '.petition_nav__item', podpishi.callbacks.onTabClicked);
	

		$.fn.scrollView = function () {
			return this.each(function () {
				$('html, body').animate({
					scrollTop: $(this).offset().top
				}, 1000);
			});
		}

		$('.edit_text').on('click', function(){
			$(this).addClass('hidden');
			$('.controls_edit').removeClass('hidden');
			
			podpishi.setPetitionEditableText();
			
			
		});
		$('.edit__button').on('click', function(event) {
			event.preventDefault();
			petition_text = $('.petition__textarea').val();
			
			petition.petition_text = petition_text ;
			
			$('.petition__textarea').replaceWith($('<div class="petition__textblock">').text(petition_text));
			$('.controls_edit').addClass('hidden');
			$('.edit_text').removeClass('hidden');
		});
		
		$('.top_popup__close').on('click', function() {
			$(this).closest('.top_popup').css('display', 'none');
		});				
	
	},
	
	drawPrefilled: function(){
		
		if (podpishi.prefilled.length == 0)
			return ;
		
		
		$('.ajax_arg[name="name"]').val(podpishi.prefilled.name);
		$('.ajax_arg[name="city"]').val(podpishi.prefilled.city);
		$('.ajax_arg[name="appartment"]').val(podpishi.prefilled.appartment_raw);
		$('.ajax_arg[name="street_name"]').val(podpishi.prefilled.street_name);
		$('.ajax_arg[name="house_number"]').val(podpishi.prefilled.house_number);
		$('.ajax_arg[name="email"]').val(podpishi.prefilled.email);
		$('.ajax_arg[name="phone"]').val(podpishi.prefilled.phone);
		
	},
	sketchInit: function(){
		
		$('#sign_sketch').sketch();
		$('#sign_sketch').sketch().is_mobile = false ; //petition.isMobile();
		$('#sign_sketch').sketch().color = '#000080';
		$('#sign_sketch').sketch().size = '2';		
		
	},
	
	clearSketch: function(){
		
		$('#sign_sketch').sketch().actions = [];
		$('#sign_sketch').sketch().context.beginPath();
		$('#sign_sketch').sketch().context.clearRect(0, 0, 340, 125);
		$('#sign_sketch').sketch().painting = false ;
		$('#sign_sketch').addClass('withOpacity');
	},
	
	callbacks: {
		
		onTabClicked: function(){
			
			var petition_id = $(this).attr('data-petition_id');
			
			podpishi.setActiveTab(petition_id);
			
		},
		
	},
	
	drawTabs: function(){
		

		var template = $('.petition_nav__item_tmp');

		var html = '';
		
		$.each(podpishi.petitions, function(){
			
			template.find('.petition_nav__item').attr({
				'data-petition_id': this.id,
			});
			
			template.find('span.text_link').text(this.button_name);
			
			html += template.html();
			
		});
		
		podpishi.petition_nav.html(html);
		
	},
	
	setPetitionEditableText: function(){
		
		$('.petition__textblock').replaceWith('<textarea class="petition__textarea" name="petition__textarea">' + podpishi.petition_texts[podpishi.active.petition_id].replace('{target_name}', podpishi.active.deputy_begin_text) + '</textarea>');
		
	},
	
	setDefaultTab: function(){

		
		
		podpishi.setActiveTab(podpishi.petition_nav.find('.petition_nav__item').first().attr('data-petition_id'));
		
	},
	
	setActiveTab: function(petition_id){
		
		var params = {};
		
		for (var i = 0; i < podpishi.petitions.length; i++)
			if (podpishi.petitions[i].id == petition_id)
				params = podpishi.petitions[i];
		
		console.log(params);
		
		if (params.hasOwnProperty('addr_required') == true && Object.keys(podpishi.addressInfo).length == 0){
			//console.log('Not allowed ' + petition_id);
			//return ;
		}
		
		//petition_nav__item--checked
		
		var classes = 'petition_nav__item--active';
		
		podpishi.petition_nav.find('.petition_nav__item').removeClass(classes);
		
		
		podpishi.petition_nav.find('.petition_nav__item[data-petition_id="' + petition_id + '"]').addClass(classes);
		
		
		
		
		
		podpishi.active.petition_id = petition_id ;
		
		podpishi.drawers['petition_' + petition_id].apply();
		
	},
	
	setTabChecked: function(petition_id){
		
		var classes = 'petition_nav__item--checked';
		
		podpishi.petition_nav.find('.petition_nav__item[data-petition_id="' + petition_id + '"]').addClass(classes);		
		
	},
	
	drawers: {
		
		petition_meriya: function(){
			
			$('.form__comment.city_comment').html('Заполните адрес полностью, пожалуйста. Вам придёт ответ из мэрии Москвы');
			
			$('.whom').html('Мэру Москвы <br/ > С. С. Собянину<br/>');
			$('.whom_header_petition').html('Уважаемый Сергей Семёнович!');
			
			var petition_text = podpishi.formatTextAndReturn((podpishi.petition_texts[podpishi.active.petition_id]));
			$('.petition__textblock').html(petition_text);
			
			podpishi.setButtonText('Подписать петицию');
			
		},
		
		petition_gosduma: function(){
			
		},
		
		petition_mosgorduma: function(){
	
			$('.form__comment.city_comment').html('Заполните адрес полностью, пожалуйста. Вам придёт ответ из Московской городской Думы');
			
			var info = podpishi.msg.deputy.mosgorduma;
			
			var deputy_sex = {
				'f': 'Уважаемая ',
				'm': 'Уважаемый ',
			};
			
			
			var textblock = podpishi.formatTextAndReturn(podpishi.petition_texts[podpishi.active.petition_id]);
			
			var name_parts = info.name_ncl[0].split(' ');
			var deputy_begin_text = deputy_sex[info.sex] + name_parts[1] + ' ' + name_parts[2];
			textblock = textblock.replace('{target_name}', deputy_begin_text);
			
			deputy_begin_text = deputy_begin_text + '!';
			
			$('.whom').html('Депутату Московской городской Думы <br/ > ' + info.name_ncl[2] + '<br/>');
			$('.whom_header_petition').html(deputy_begin_text);
			
			podpishi.active.deputy_begin_text = deputy_begin_text ;
			var textblock = (podpishi.formatTextAndReturn(textblock));
			
			$('.petition__textblock').html(textblock);
			
			podpishi.setButtonText('Подписать петицию');
			
		},
		
		petition_meriya_velo: function(){
			podpishi.drawers.petition_meriya();
		},
		
		petition_mosgorduma_velo: function(){
			podpishi.drawers.petition_mosgorduma();
		},
		
	},
	
	closePopupAndScroll: function(){
		
		
		$(this).closest('.modal_wrap').fadeOut(250);
		
		podpishi.waveScroll(600);
		
	},
	
	refreshIframe: function(){
		
		var iframe = $('#mapIframe');
		
		var href = iframe.attr('src').replace('=' + iframe.attr('data-counter_value'), '=' + (parseInt(iframe.attr('data-counter_value')) + 1));
		iframe.attr('src', '');
		iframe.attr('src', href);
		
	},
	
	savePetition: function(){
		
		if (podpishi.is_sending) return ;
		podpishi.is_sending = true ;
		
		var container = $('div.petition');
		
		var data = {};

		container.find('input.ajax_arg, textarea.ajax_arg').each(function(){
			data[$(this).attr('name')] = $(this).val();
		});				
		
		data.destination = podpishi.active.petition_id;
		
		data.sign = $('#sign_sketch')[0].toDataURL('image/png');
		
		data.petition_text = '' ;
		
		if ($('.petition__textarea').val() != '' && typeof($('.petition__textarea').val()) != "undefined")
			data.petition_text = $('.petition__textarea').val();
		
		// check if name is wrong or too small
		
		var name_parts = data.name.split(' ');
		
		if (name_parts.length < 2){
			alert('Вы забыли указать имя или фамилию');
			podpishi.is_sending = false ;
			return ;
		}
		if (name_parts[0].length < 3 || name_parts[1].length < 3 || data.name.indexOf('.') != -1){
			alert('Инициалы недопустимы в официальном обращении, укажите полностью ФИО');
			podpishi.is_sending = false ;
			return ;
		}

		if (podpishi.hasOwnProperty('gosduma_deputy') == false)
			podpishi.gosduma_deputy = {};
		
		data.gosduma_deputy = podpishi.gosduma_deputy ;

		podpishi.setButtonText('Идёт отправка');
						
		//data.context = 'savePetition';
		
		//console.log(data);
		
		//return ;
		$('div.form__row').removeClass('form__row-error');
		
		smartAjax('/ajax/ajax_petition_staging.php?context=savePetition_v2', data, function(msg){
			
			//console.log(msg);
			
			//return ;
			podpishi.setTabChecked(msg.destination);
			
			//hide textarea if previous petition was manually entered
			$('.petition__textarea').replaceWith($('<div class="petition__textblock">').text(''));
			$('.controls_edit').addClass('hidden');
			$('.edit_text').removeClass('hidden');				
			
			podpishi.msg = msg ;
			
			var modal = $('.' + msg.destination + '_added');
			
			if (msg.destination.search('meriya') != -1)
				podpishi.refreshIframe();
			
			modal.fadeIn(500);
			
			if (typeof(modal.attr('data-target_map')) != "undefined"){
				podpishi.redirectUrl = '/' + modal.attr('data-target_map') + '#map';
				
				setTimeout(function(){
					
					$('#map').next().scrollView()
					
					//document.location.href = podpishi.redirectUrl ;
					
				}, 5000);
			}
			
			$('.wrote__num').html(parseInt($('.wrote__num').text().replace(' ', '')) + 1);

			
			
			if (msg.destinations_left.length == 0)
				$('.send__button, .form__comment.form__comment--longline').hide();
			
			podpishi.is_sending = false;
			
		}, function(msg){
			
			$('div.form__row').removeClass('form__row-error');
			
			if (msg.hasOwnProperty('error_hightlight')){
				$('input.ajax_arg[name="' + msg.error_hightlight + '"]').closest('.form__row').addClass('form__row-error');
				alert('Не все поля были заполнены');
				
				podpishi.waveScroll(600);
			}
			else {
				
				alert(msg.error_text);
				
				
			}
			
			podpishi.setButtonText('Подписать петицию');
			
			podpishi.is_sending = false;
			
		}, 'savePetition', 'POST');					
	},	
	
	formatTextAndReturn: function(text){
		
		text = '<p>' + text;
		
		text = text.replace(/([^>])\n/g, '$1</p><p>');;
		
		//text = text.replace(/\\n/g, '</p><p>');
		
		//text = text.replace('<br>', '</p>');
		
		return text ;
		
	},
		
	nl2br: function( str ) {	// Inserts HTML line breaks before all newlines in a string
		// 
		// +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)

		return str.replace(/([^>])\n/g, '$1<br/>');
	},
	
	setButtonText: function(text){
		$('.send__button').html(text);
	},
	
	waveScrollTop: function(){
		var body = $("html, body");
		body.stop().animate({scrollTop: 0}, '3000', 'swing', function() { 

		});
	},
	
	waveScroll: function(height){
		var body = $("html, body");
		body.stop().animate({scrollTop: height}, '3000', 'swing', function() { 

		});
	},
	
};


$(document).ready(function(){
	
	podpishi.init();
	
});
</script>


<?php



exit();
?>
<script>


		petition = {
			
			is_sending: false,
			petition_text: '',
			type: 'meriya',
			zoom: document.documentElement.clientWidth / window.innerWidth,
			
			texts_main: <?=json_encode($_CFG['petition_text'])?>,
			
			gosduma_deputy: <?=json_encode($gosduma_deputy);?>,
			
			init: function(){

				$('.petition__textblock').html(petition.formatTextAndReturn(petition.texts_main[petition.type]));
			
				$('#sign_sketch').sketch();
				$('#sign_sketch').sketch().is_mobile = false ; //petition.isMobile();
				$('#sign_sketch').sketch().color = '#000080';
				$('#sign_sketch').sketch().size = '2';
				
				
				petition.bindings();

			},
			
			bindings: function(){

			},
			
			// disabled
			onResize: function(){
				$('#sign_sketch').replaceWith('<canvas id="sign_sketch" class="withOpacity" width="340" height="125"></canvas>');
				petition.init();
			},
			
			isMobile: function(){
				var isMobile = false; //initiate as false
				// device detection
				if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|ipad|iris|kindle|Android|Silk|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(navigator.userAgent) 
					|| /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(navigator.userAgent.substr(0,4))) isMobile = true;	

				return isMobile;
			},
			
			clearSketch: function(){
				
				$('#sign_sketch').sketch().actions = [];
				$('#sign_sketch').sketch().context.beginPath();
				$('#sign_sketch').sketch().context.clearRect(0, 0, 340, 125);
				$('#sign_sketch').sketch().painting = false ;
				$('#sign_sketch').addClass('withOpacity');
			},
			
			savePetition: function(){
				
				if (petition.is_sending) return ;
				petition.is_sending = true ;
				
				var container = $('div.petition');
				
				var data = {};

				container.find('input.ajax_arg, textarea.ajax_arg').each(function(){
					data[$(this).attr('name')] = $(this).val();
				});				
				
				data.destination = petition.type ;
				
				data.sign = $('#sign_sketch')[0].toDataURL('image/png');
				
				data.petition_text = petition.petition_text ;
				
				if ($('.petition__textarea').val() != '')
					data.petition_text = $('.petition__textarea').val();
				
				// check if name is wrong or too small
				
				var name_parts = data.name.split(' ');
				
				if (name_parts.length < 2){
					alert('Вы забыли указать имя или фамилию');
					petition.is_sending = false ;
					return ;
				}
				if (name_parts[0].length < 3 || name_parts[1].length < 3 || data.name.indexOf('.') != -1){
					alert('Инициалы недопустимы в официальном обращении, укажите полностью ФИО');
					petition.is_sending = false ;
					return ;
				}
	
				data.gosduma_deputy = petition.gosduma_deputy ;
	
				petition.setButtonText('Идёт отправка');
								
				//data.context = 'savePetition';
				
				console.log(data);
				
				smartAjax('/ajax/ajax_petition_staging.php?context=savePetition', data, function(msg){
					
					$('.' + msg.destination + '_added').fadeIn(500);
					
					$('.wrote__num').html(parseInt($('.wrote__num').text()) + 1);
					
					
									
					if (msg.destination == 'meriya' && msg.hasOwnProperty('deputy') == false){
						$('.none_added').fadeIn(500);
						setTimeout(function(){
							document.location.href = 'https://trolley.city4people.ru/map/';
						}, 5000);
						
						return ;
					}
					

					
					petition.setButtonText('Подписать петицию');
					
					petition.msg = msg ;
					
					if (msg.destinations_left.length > 0){
	
						petition.setPetitionNavChecked(msg.destination);
						petition.preparePetitionOf(msg.destinations_left[0]);
						petition.displayTopPopup(msg.destination, msg.destinations_left[0]);
					}
					
					
					if (msg.destinations_left.length == 0)
						$('.send__button, .form__comment.form__comment--longline').hide();
					
					if (msg.destinations_left.length == 0){
						setTimeout(function(){
							document.location.href = 'https://trolley.city4people.ru/map/';
						}, 5000);
						
					}
					
					petition.is_sending = false;
					
				}, function(msg){
					
					$('div.form__row').removeClass('form__row-error');
					
					if (msg.hasOwnProperty('error_hightlight')){
						$('input.ajax_arg[name="' + msg.error_hightlight + '"]').closest('.form__row').addClass('form__row-error');
						alert('Не все поля были заполнены');
						
						petition.waveScrollTop();
					}
					else {
						
						if (msg.error == 'already inner 1 exists'){
							$('.none_added').fadeIn(500);
									
							if (msg.destination == 'mosgorduma' || msg.destination == 'none')
								$('.send__button, .form__comment.form__comment--longline').hide();
							
						}
						else {
						
							alert(msg.error_text);
						}
						
					}
					
					petition.setButtonText('Подписать петицию');
					
					petition.is_sending = false;
					
				}, 'savePetition', 'POST');					
			},
			

			
			displayTopPopup: function(sent_destination, target_destination){
				
				var sent_list = {
					'meriya': 'в мэрию',
					'gosduma': 'в Государственную Думу',
					'mosgorduma': 'в Московскую городскую Думу',
				};
				
				var target_list = {
					'meriya': '',
					'gosduma': '',
					'mosgorduma': '',
				};
				
				var container = $('.top_popup');
				
				container.find('.sent_destination').text(sent_list[sent_destination]);
				container.find('.target_destination').text(sent_list[target_destination]);
				
				container.show();
				
			},
			
			setPetitionNavChecked: function(type){
				
				$('.petition_nav__item.' + type).addClass('petition_nav__item--checked');
				
			},
			
			setPetitionNavActive: function(type){
				
				$('.petition_nav__item').removeClass('petition_nav__item--active');
				$('.petition_nav__item.' + type).addClass('petition_nav__item--active');
				
			},
			
			preparePetitionOf: function(type){
				
				petition.setPetitionNavActive(type);	
				petition.petition_text = '';
				
				//hide textarea if previous petition was manually entered
				$('.petition__textarea').replaceWith($('<div class="petition__textblock">').text(''));
				$('.controls_edit').addClass('hidden');
				$('.edit_text').removeClass('hidden');				

				if (type == "mosgorduma"){
					
					var info = petition.msg.deputy[type] ;
					
					$('.petition__paper').addClass(type);
					$('.form__comment.form__comment--longline').html('Нажимая красную кнопку, вы поручаете муниципальному депутату Максиму Кацу распечатать ваше обращение и передать его в Московскую городскую Думу');
					$('.form__comment.city_comment').html('Заполните адрес полностью, пожалуйста. Вам придёт ответ из МГД');
					
					var deputy_sex = {
						'f': 'Уважаемая ',
						'm': 'Уважаемый ',
					};
					
					var name_parts = info.name_ncl[0].split(' ');
					var deputy_begin_text = deputy_sex[info.sex] + name_parts[1] + ' ' + name_parts[2] + '!';
					petition.texts_main[type] = petition.texts_main[type].replace('{target_name}', deputy_begin_text);
					petition.deputy_begin_text = deputy_begin_text;
					
					$('.whom').html('Депутату Московской городской Думы <br/ > ' + info.name_ncl[2] + '<br/>');
					$('.whom_header_petition').html(deputy_begin_text);
					petition.type = type ;
					petition.setButtonText('Подписать петицию');
					petition.init();
					
					$('.modal_wrap').fadeOut(500);
					
					petition.waveScrollTop();
					
				}
				else if (type == "gosduma"){
					
					var info = petition.gosduma_deputy ;
					
					$('.petition__paper').addClass(type);
					$('.form__comment.form__comment--longline').html('Нажимая красную кнопку, вы поручаете муниципальному депутату Максиму Кацу распечатать ваше обращение и передать его в Государственную Думу');
					$('.form__comment.city_comment').html('Заполните адрес полностью, пожалуйста. Вам придёт ответ из Государственной Думы');
					
					var deputy_sex = {
						'f': 'Уважаемая ',
						'm': 'Уважаемый ',
					};
					
					var name_parts = info.name_ncl[0].split(' ');
					var deputy_begin_text = deputy_sex[info.sex] + name_parts[1] + ' ' + name_parts[2] + '!';
					petition.texts_main[type] = petition.texts_main[type].replace('{target_name}', deputy_begin_text);
					petition.deputy_begin_text = deputy_begin_text;
					
					$('.whom').html('Депутату Государственной Думы <br/ > ' + info.name_ncl[2] + '<br/>');
					$('.whom_header_petition').html(deputy_begin_text);
					petition.type = type ;
					petition.setButtonText('Подписать петицию');
					petition.init();
					
					$('.modal_wrap').fadeOut(500);
					
					petition.waveScrollTop();
					
				}
				else if (type == "meriya"){
					
					$('.petition__paper').addClass(type);
					$('.form__comment.form__comment--longline').html('Нажимая красную кнопку, вы поручаете муниципальному депутату Максиму Кацу распечатать ваше обращение и передать его в мэрию Москвы');
					$('.form__comment.city_comment').html('Заполните адрес полностью, пожалуйста. Вам придёт ответ из мэрии Москвы');
					
					var deputy_sex = {
						'f': 'Уважаемая ',
						'm': 'Уважаемый ',
					};
					
					$('.whom').html('Мэру Москвы <br/ > С. С. Собянину<br/>');
					$('.whom_header_petition').html('Уважаемый Сергей Семёнович!');
					petition.type = type ;
					petition.setButtonText('Подписать петицию');
					petition.init();
					
					$('.modal_wrap').fadeOut(500);
					
					petition.waveScrollTop();
					
				}
				else {
					
				}
				
			},
			
			setButtonText: function(text){
				$('.send__button').html(text);
			},
			
			waveScrollTop: function(){
				var body = $("html, body");
				body.stop().animate({scrollTop: 0}, '3000', 'swing', function() { 

				});
			},
		};
		
		$(document).ready(function(){
			petition.init();
			
			if (document.location.hash.search('gosduma') != -1)
				petition.preparePetitionOf('gosduma');
		});

	</script>
		
    <script type="text/javascript">
	
	<?php
		foreach ($_CFG['petition_text'] as $_key => $_text){
			$_CFG['petition_text'][$_key] = str_replace('\n', "\n\r", $_text) ;
			}
	?>
	var petition_texts = <?=json_encode($_CFG['petition_text']);?>;

        $(function() {
            $('.petition__controls .text_link').on('click', function() {
                var $petition = $('#petition__text');
                $(this).addClass('hidden').siblings('.text_link').removeClass('hidden');
                $petition.hasClass('hidden') ? $petition.removeClass('hidden') : $petition.addClass('hidden');
            });
            $('.subscribe__button').on('click', function(event) {
                event.preventDefault();
                $('.modal_wrap').removeClass('modal_wrap--hidden');
            });
            $('.modal__close').on('click', '.text_link', function() {
                $('.modal_wrap').addClass('modal_wrap--hidden');
            });
            $('.edit_text').on('click', function(){
                $(this).addClass('hidden');
                $('.controls_edit').removeClass('hidden');
                $('.petition__textblock').replaceWith('<textarea class="petition__textarea" name="petition__textarea">' + petition_texts[petition.type].replace('{target_name}', petition.deputy_begin_text) + '</textarea>');
            });
            $('.edit__button').on('click', function(event) {
                event.preventDefault();
                petition_text = $('.petition__textarea').val();
				
				petition.petition_text = petition_text ;
				
                $('.petition__textarea').replaceWith($('<div class="petition__textblock">').text(petition_text));
                $('.controls_edit').addClass('hidden');
                $('.edit_text').removeClass('hidden');
            });
			
				
			$(window).resize( function () {
				resizeTriangle();
				resizeTop()
			});
			resizeTriangle();
			resizeTop()

			function resizeTriangle () {
				$('.triangle').each(function () {
					var width = ($(this).parent().width());
					$(this).css('border-left-width', width).css('border-bottom-width', width/9);
				});
			}

			function resizeTop() {
				var windowWidth = $(window).width();
				$('.top').css('height', windowWidth/1.75);
			}			
			
			
            $('.petition_nav__item').on('click', function() {
				return ;
			   if($(this).hasClass('petition_nav__item--active')) {
                    return;
                }
                else {
                    $('.petition_nav__item').removeClass('petition_nav__item--active');
                    $(this).addClass('petition_nav__item--active');
                }
            });

            $('.top_popup__close').on('click', function() {
                $(this).closest('.top_popup').css('display', 'none');
            });			
			
        });
		
		
podpishi = {
	
};

</script>