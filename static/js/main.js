GLOBAL_OPTIONS = {
    loaderId: 0,
    isAjaxInProgress: false,
} ;
GLOBAL_IMAGES = {} ;
GLOBAL_LANG = {} ;
GLOBAL_AJAX = {} ;

navi = {

    last: '',

    init: function(callback){

        navi.naviByHash = callback;

        navi.last = document.location.hash ;

        $(window).bind( "hashchange", function(e) {

            navi.onHashChange();

        });

        navi.onHashChange('force');

    },

    getParams: function(){
        var hash = document.location.hash ;

        return navi.hashToObject(hash);
    },

    onHashChange: function(force){
        var hash = document.location.hash ;

        if (typeof(force) == "undefined")
            if (hash == navi.last) return ;

        navi.last = hash ;

        navi.naviByHash.apply(null, [navi.hashToObject(hash)]);
    },

    hashToObject: function(hash){

        hash = hash.substr(1);

        var params = hash.split('&');

        var navi_params = {} ;

        for (var i = 0; i < params.length; i++){
            if (typeof(params[i]) == "undefined") continue;

            var tmp = params[i].split('=');

            if (typeof(tmp[1]) == "undefined") tmp[1] = '';

            navi_params[tmp[0]] = tmp[1];

        }

        return navi_params ;

    },

    naviByHash: function(navi_params){


        if (navi_params.hasOwnProperty('main')){
            site.mainPage.loadForm();
            return true ;
        }
        else if (navi_params.hasOwnProperty('balance')){
            site.balancePage.loadForm();
            return true ;
        }
        else {
            site.mainPage.loadForm();
            return true ;
        }

        return false;

    },

    setHash: function(page){

        if (document.location.hash == '#' + page){
            navi.naviByHash(navi.hashToObject(document.location.hash));
        }
        else {
            document.location.hash = page ;
        }

    },
};

$(document).ready(function(){

    if (typeof($('select.selectize').first().selectize) != "undefined")
        $('select.selectize').selectize();

    formatPhoneNumber = function(value){
        var num = value.replace( '+' , '' ).replace( /\D/g, '' ).split( /(?=.)/ ), i = num.length;
        if ( 0 <= i ) num.unshift( '+' );
        if ( 2 <= i ) num.splice( 2, 0, ' ' );
        if ( 5 <= i ) num.splice( 6, 0, ' ' );
        if ( 8 <= i ) num.splice( 10, 0, '-' );
        if ( 11 <= i ) num.splice( 13, 0, '-' );
        //if ( 12 <= i ) num.splice( 14, 0, '-' );
        //if ( 9 <= i ) num.splice( 12, 0, '-' );
        if ( 13 <= i ) num.splice( 16, num.length - 16 );
        value = num.join( '' );

        return value ;
    };


    $.fn.formatPnoneNumber = function(){
        return this.each(function(){
            $(this).bind('keyup', function(){

                this.value = formatPhoneNumber(this.value);


            });
        });
    };


    $(document).on('click', 'img.screenshot', function(){
        $(this).toggleClass('screenshot--full');
    });



    /*! sprintf.js | Copyright (c) 2007-2013 Alexandru Marasteanu <hello at alexei dot ro> | 3 clause BSD license */(function(e){function r(e){return Object.prototype.toString.call(e).slice(8,-1).toLowerCase()}function i(e,t){for(var n=[];t>0;n[--t]=e);return n.join("")}var t=function(){return t.cache.hasOwnProperty(arguments[0])||(t.cache[arguments[0]]=t.parse(arguments[0])),t.format.call(null,t.cache[arguments[0]],arguments)};t.format=function(e,n){var s=1,o=e.length,u="",a,f=[],l,c,h,p,d,v;for(l=0;l<o;l++){u=r(e[l]);if(u==="string")f.push(e[l]);else if(u==="array"){h=e[l];if(h[2]){a=n[s];for(c=0;c<h[2].length;c++){if(!a.hasOwnProperty(h[2][c]))throw t('[sprintf] property "%s" does not exist',h[2][c]);a=a[h[2][c]]}}else h[1]?a=n[h[1]]:a=n[s++];if(/[^s]/.test(h[8])&&r(a)!="number")throw t("[sprintf] expecting number but found %s",r(a));switch(h[8]){case"b":a=a.toString(2);break;case"c":a=String.fromCharCode(a);break;case"d":a=parseInt(a,10);break;case"e":a=h[7]?a.toExponential(h[7]):a.toExponential();break;case"f":a=h[7]?parseFloat(a).toFixed(h[7]):parseFloat(a);break;case"o":a=a.toString(8);break;case"s":a=(a=String(a))&&h[7]?a.substring(0,h[7]):a;break;case"u":a>>>=0;break;case"x":a=a.toString(16);break;case"X":a=a.toString(16).toUpperCase()}a=/[def]/.test(h[8])&&h[3]&&a>=0?"+"+a:a,d=h[4]?h[4]=="0"?"0":h[4].charAt(1):" ",v=h[6]-String(a).length,p=h[6]?i(d,v):"",f.push(h[5]?a+p:p+a)}}return f.join("")},t.cache={},t.parse=function(e){var t=e,n=[],r=[],i=0;while(t){if((n=/^[^\x25]+/.exec(t))!==null)r.push(n[0]);else if((n=/^\x25{2}/.exec(t))!==null)r.push("%");else{if((n=/^\x25(?:([1-9]\d*)\$|\(([^\)]+)\))?(\+)?(0|'[^$])?(-)?(\d+)?(?:\.(\d+))?([b-fosuxX])/.exec(t))===null)throw"[sprintf] huh?";if(n[2]){i|=1;var s=[],o=n[2],u=[];if((u=/^([a-z_][a-z_\d]*)/i.exec(o))===null)throw"[sprintf] huh?";s.push(u[1]);while((o=o.substring(u[0].length))!=="")if((u=/^\.([a-z_][a-z_\d]*)/i.exec(o))!==null)s.push(u[1]);else{if((u=/^\[(\d+)\]/.exec(o))===null)throw"[sprintf] huh?";s.push(u[1])}n[2]=s}else i|=2;if(i===3)throw"[sprintf] mixing positional and named placeholders is not (yet) supported";r.push(n)}t=t.substring(n[0].length)}return r};var n=function(e,n,r){return r=n.slice(0),r.splice(0,0,e),t.apply(null,r)};e.sprintf=t,e.vsprintf=n})(typeof exports!="undefined"?exports:window);


    function PAGE_INIT(){

    }

});

mainSite = {};

mainSite.number_format = function( number, decimals, dec_point, thousands_sep ) {	// Format a number with grouped thousands
    //
    // +   original by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +	 bugfix by: Michael White (http://crestidg.com)

    var i, j, kw, kd, km;

    // input sanitation & defaults
    if( isNaN(decimals = Math.abs(decimals)) ){
        decimals = 2;
    }
    if( dec_point == undefined ){
        dec_point = ",";
    }
    if( thousands_sep == undefined ){
        thousands_sep = ".";
    }

    i = parseInt(number = (+number || 0).toFixed(decimals)) + "";

    if( (j = i.length) > 3 ){
        j = j % 3;
    } else{
        j = 0;
    }

    km = (j ? i.substr(0, j) + thousands_sep : "");
    kw = i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + thousands_sep);
    //kd = (decimals ? dec_point + Math.abs(number - i).toFixed(decimals).slice(2) : "");
    kd = (decimals ? dec_point + Math.abs(number - i).toFixed(decimals).replace(/-/, 0).slice(2) : "");


    return km + kw + kd;
};

mainSite.format_money = function(number){

    return mainSite.number_format(number, 0, " ", " ");

};

mainSite.isMobile = function(){

    var isMobile = false; //initiate as false
    // device detection
    if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|ipad|iris|kindle|Android|Silk|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(navigator.userAgent)
        || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(navigator.userAgent.substr(0,4))) isMobile = true;

    return isMobile ;

};

mainSite.parseFormArgs = function(container){

    var data = {};

    container.find('.ajax_arg').each(function(){

        var name = $(this).attr('name');

        if (name == '' || typeof(name) == "undefined")
            return ;

        if ($(this).is('select')){
            value = $(this).next().find('.selectize-input .item').text();
        }
        else if ($(this).parent().hasClass('easy-autocomplete')){
            value = $(this).attr('ajax_arg_value');
        }
        else if ($(this).is('input[type="checkbox"]')){
            value = $(this).prop('checked');
        }
        else if ($(this).is('input[type="radio"]')){

            if ($(this).prop('checked') == false)
                return ;

            value = $(this).val();
        }
        else {
            value = $(this).val();
        }
        data[name] = value ;

    });

    return data ;

};

mainSite.ifMac = function(){

    return mac = navigator.platform.match(/(Mac|iPhone|iPod|iPad)/i) ? true : false;

};

mainSite.numberWithSeparation = function(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ");
};
mainSite.swalLoading = {

    isLoadingEnabled: false,

    enable: function(){


        swal({'title': '', 'showConfirmButton': false, 'customClass': 'swal-gd-loader swal-gd-clean'});
        swal.showLoading();

        mainSite.swalLoading.isLoadingEnabled = true ;

    },

    disable: function(){

        if (mainSite.swalLoading.isLoadingEnabled == false)
            return ;

        mainSite.swalLoading.isLoadingEnabled = false ;
        swal.close();

    },

};

mainSite.mt_rand = function (min, max) { // eslint-disable-line camelcase
    //  discuss at: http://locutus.io/php/mt_rand/
    // original by: Onno Marsman (https://twitter.com/onnomarsman)
    // improved by: Brett Zamir (http://brett-zamir.me)
    //    input by: Kongo
    //   example 1: mt_rand(1, 1)
    //   returns 1: 1
    var argc = arguments.length
    if (argc === 0) {
        min = 0
        max = 2147483647
    } else if (argc === 1) {
        throw new Error('Warning: mt_rand() expects exactly 2 parameters, 1 given')
    } else {
        min = parseInt(min, 10)
        max = parseInt(max, 10)
    }
    return Math.floor(Math.random() * (max - min + 1)) + min
};

mainSite.formatCommaText = function(msg, options){

    var text = '';

    $.each(options, function(){

        if (msg[this] == '')
            return ;

        if (text == ''){
            text = msg[this] ;
        }
        else {
            text += ', ' + msg[this];
        }

    });

    return text ;
};

mainSite.waveScroll = function(offset, body, time){

    if (typeof(body) == "undefined")
        body = $("html, body, div.popup-bg");

    if (typeof(time) == "undefined")
        time = 3000 ;

    //console.log(time);


    body.stop().animate({scrollTop: offset}, time, 'swing', function() {

    });
};

mainSite.forceSelectizeValue = function(selectize_select, value, silent){

    var selectize = selectize_select.next();

    var value_number = 0;

    $.each(selectize_select.selectize()[0].selectize.options, function(){

        if (this.text == value){
            value_number = this.value;

            return false;
        }

    });

    if (typeof(silent) == "undefined")
        silent = false ;

    selectize_select.selectize()[0].selectize.setValue(value_number, silent);

};

mainSite.postError = function(errorText, errorTitle, swalParams){
    if (typeof(errorTitle) == "undefined") errorTitle = '';

    if (typeof(swal) != "undefined"){

        var swalFinalArgs = {
            html: errorText,
            title: errorTitle,
            showConfirmButton: false,
        };

        $.each(swalParams, function(key, item){
            swalFinalArgs[key] = item ;
        });

        console.log(swalParams);

        swal(swalFinalArgs);

    }
    else {

        $('.uniPopup').find('.modal__title div').html(errorTitle);
        $('.uniPopup').find('.modal__text').html(errorText);

        $('.uniPopup').fadeIn(500);
    }
};

mainSite.swal = function(errorText, errorTitle, swalParams){

    if (typeof(errorTitle) == "undefined") errorTitle = '';
    if (typeof(swalParams) == "undefined") swalParams = {};

    mainSite.postError(errorText, errorTitle, swalParams);

};

mainSite.notify = function(message,type,position) {
    if(typeof(type) == 'undefined') {
        type = 'warning';
    }
    if(typeof(position) == 'undefined') {
        position = 'bottom';
    }
    var box = $('<div class="notify-alert notify-'+type+' notify-alert-pos-'+position+'">'+message+'</div>');
    $('body').append(box);
    $(box).delay(100).queue(function(next){
        $(this).addClass('visible');
        $(this).dequeue();
    }).delay(3500).queue(function(next){
        $(this).removeClass("visible");
        $(this).dequeue();
    });

};
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

        if (typeof(msg) == "string"){
            try {

                msg = JSON.parse(msg);

            }
            catch (e){
                msg = {};
                msg.error = 'incorrectJSON';
            }
        }


        if (typeof(arguments.callee.onSuccess) == "function" && msg.error == "false"){
            arguments.callee.onSuccess.apply(null, [msg]);
        }



        if (typeof(arguments.callee.onError) == "function" && msg.error != "false"){
            arguments.callee.onError.apply(null, [msg]);
        }
        else {
            if (msg.error != "false"){

                if (msg.hasOwnProperty('alertify') && typeof(alertify) != 'undefined'){
                    alertify[msg.alertify.mode].apply(null, [msg.alertify.text]);
                }
                else {
                    if (msg.error_text !== undefined){
                        mainSite.postError(msg.error_text);
                    }
                    else {
                        console.log(msg.error);
                    }
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

mainSite.resizeEqColumn = function(){

    $('.eq-column').height('initial');

    /*equal hight*/
    var max_col_height = 0;
    $('.eq-column').each(function(){
        if ($(this).height() > max_col_height) {
            max_col_height = $(this).height();
        }
        console.log($(this).height());
    });
    $('.eq-column').height(max_col_height + 100);

};


function downloadURI(uri, name) {
    var link = document.createElement("a");
    link.download = name;
    link.href = uri;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    delete link;
}

var tableToExcel = (function() {
    var uri = 'data:application/vnd.ms-excel;base64,'
        , template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{worksheet}</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body><table>{table}</table></body></html>'
        , base64 = function(s) { return window.btoa(unescape(encodeURIComponent(s))) }
        , format = function(s, c) { return s.replace(/{(\w+)}/g, function(m, p) { return c[p]; }) }
    return function(table, name, filename) {

        var html = '';
        if(typeof jQuery == 'function') {
            var table = $('#'+table).clone();
            table.find('textarea').each(function(){

                $(this).parent().html($(this).val());

            });
            table.find('input, button, select').remove();
            table.find('a').off('click');
            html = table.html();
        } else if (!table.nodeType) {
            table = document.getElementById(table)
            html = table.innerHTML;
        }

        var ctx = {worksheet: name || 'Worksheet', table: html}
        //window.location.href = uri + base64(format(template, ctx))

        downloadURI(uri + base64(format(template, ctx)), filename);

    }
})();

sbUploader = {

    initFor: function(container){

        container.fileupload({
            dataType: 'json',
            sequentialUploads: true,
            dropZone: $('#dropzone'),
            autoUpload:true,
            done: function (e, data) {

                var result = data.result ;

                if (result.error != "false"){

                    if (!result.hasOwnProperty('error_text'))
                        result.error_text = result.error ;

                    mainSite.swal(result.error_text);
                    return ;
                }

                if (result.hasOwnProperty('file_callback')){

                    var callback_eval = 'var callback_function = ' + result.file_callback + ';';

                    eval(callback_eval);

                    if (typeof(callback_function) == "function"){
                        if(result.hasOwnProperty('attachList')) {
                            data.files[0].data=result.attachList[0];
                            callback_function.apply(null, [result.attachList[0], e, data]);

                        } else {
                            console.log('No files saved');
                            console.log(data.formData);
                        }

                    }
                    else {
                        console.log('No callback find: ' + result.file_callback);
                    }


                }
                else {
                    console.log('No callback defined');
                    console.log('result');
                }

            },

            fileuploadsubmit: function(){

            },
        });

        container.bind('fileuploadsubmit', function (e, data) {

            var target = $(e.target);

            data.formData = {
                category: target.attr('data-category'),
                file_callback: target.attr('data-file_callback'),
                format: 'json',
            };

        });

        container.bind('fileuploadprogress', function (e, data) {
            // Log the current bitrate for this upload:
            console.log(data);
            console.log(data.loaded);
        });

    },
};

auth = {

    is_url: false,

    btnSetText: function(text){
        auth.container.find('.btn-primary').text(text);

    },

    usePopup: function(){

        auth.container = $(this).closest('div.login-popup');

        var auth_phone = auth.container.find('.auth_phone').val();
        var auth_password = auth.container.find('.auth_password').val();

        auth.btnSetText('Входим');
        smartAjax('/ajax/ajax_auth.php?context=authUser', {
            context: 'authUser',
            auth_phone: auth_phone,
            auth_password: auth_password,
        }, function(msg){

            if (typeof(window.onAuthSpecial) == "function"){
                window.onAuthSpecial(msg);
            }
            else {
                $('.yourInfoHelpUsToWin').fadeOut(500);

                if (auth.is_url)
                    document.location.href = "/panel";

                if (!auth.is_url)
                    document.location.href = "/panel";
            }

            auth.btnSetText('Успешно вошли');

        }, function(msg){

            auth.btnSetText('Войти');

            swal({
                title: '',
                html: '<div>' +  msg.error_text + '</div>',

                showConfirmButton: false,
            });

        }, 'auth', 'POST');

    },

    restorePassword: function(){

        var container = $('div.restorePopup');

        var auth_phone = container.find('.auth_phone').val();

        smartAjax('/ajax/ajax_auth.php?context=restorePassword' , {
            context: 'restorePassword',
            phone: auth_phone,
        }, function(msg){

            $('.restorePopup').fadeOut(500);

            mainSite.postError('Новый пароль отправлен на ваш E-mail','Готово!');

        }, function(msg){

            mainSite.postError(msg.error_text);

        }, 'auth', 'POST');

    },

    changePassword: function(){

        var container = $('div.container.profileEdit .passwordChangeContainer');

        var data = {} ;

        container.find('input.ajax_arg, textarea.ajax_arg').each(function(){
            data[$(this).attr('name')] = $(this).val();
        });

        data.user_id = container.closest('.profileEdit').attr('user_id');

        data.context = 'changePassword';


        smartAjax('/ajax/ajax_auth.php?context=' + data.context, data, function(msg){

            mainSite.postError('Сохранено успешно.', 'Готово!');


        }, function(msg){

            mainSite.postError(msg.error_text, 'Ошибка!');

        }, 'auth', 'POST');


    },

    checkAuthOnHref: function(){
        if (auth.is_authed) return true ;

        auth.is_url = true ;

        $('.authPopup').fadeIn(500);

        return false ;
    },

};


fileUploader = {

    uploadIframeId: 0,
    upload_counter: 0,

    origin: '',

    file_callback: '',

    beginUpload: function(_this){

        var form = $(_this).closest('form');

        form.attr({
            'action': '/ajax/ajax_attach.php?context=uploadAttachForTask&task_id=' + tasks.cur_task_id,
        });

        fileUploader.file_callback = form.attr('file_callback');

        fileUploader.sendForm(form[0], '', 'fileUploader.onFileUploaded(this);');

    },

    beginUploadInner: function(_this, url){

        var form = $(_this).closest('form');

        form.attr({
            'action': url,
        });

        fileUploader.file_callback = form.attr('file_callback');

        fileUploader.sendForm(form[0], '', 'fileUploader.onFileUploaded(this);');

    },

    sendForm: function (form, url, callback_name) {
        if (fileUploader.upload_counter != 0) return ;
        if (!document.createElement) return; // not supported
        if (typeof(form)=="string") form=document.getElementById(form);
        var frame= fileUploader.createIFrame(callback_name);

        form.setAttribute('target', frame.id);

        form.submit();
    },

    onFileUploaded: function(_this){

        if (fileUploader.upload_counter == 0){
            fileUploader.upload_counter++ ;

            if (mainSite.ifMac == false)
                return ;
        }


        var result = fileUploader.getIframeWindow(document.getElementById(fileUploader.uploadIframeId)).document.getElementsByTagName('body')[0].innerHTML;

        if (fileUploader.file_callback == 'avatarUpaloder.onUploadAvatar'){

            clearTimeout(avatarUpaloder.timeoutId);

            avatarUpaloder.timeoutId = setTimeout(function() {

                var data = {};
                data.context = 'get__avatarInfo';

                smartAjax('/ajax/ajax_attach.php', data, function (msg) {

                    eval('var fn = ' + fileUploader.file_callback);

                    fn.apply(null, [msg]);

                    fileUploader.upload_counter = 0;

                }, function (msg) {

                    mainSite.swal(msg.error_text);

                }, 'get__avatarInfo', 'GET');

            }, 3000);




        }
        else {


            try {
                // fix for iphone JSON.parse eof error
                var msg = JSON.parse(result);
            }
            catch (e) {

                //mainSite.swal(e);

                //mainSite.swal('Фотография успешно загружена');
                return;
            }

            eval('var fn = ' + fileUploader.file_callback);

            fn.apply(null, [msg]);

            fileUploader.upload_counter = 0 ;

        }



    },


    getIframeWindow: function (iframe_object) {
        var doc;

        if (iframe_object.contentWindow) {
            return iframe_object.contentWindow;
        }

        if (iframe_object.window) {
            return iframe_object.window;
        }

        if (!doc && iframe_object.contentDocument) {
            doc = iframe_object.contentDocument;
        }

        if (!doc && iframe_object.document) {
            doc = iframe_object.document;
        }

        if (doc && doc.defaultView) {
            return doc.defaultView;
        }

        if (doc && doc.parentWindow) {
            return doc.parentWindow;
        }

        return undefined;
    },

    createIFrame: function (callback_name) {
        var id = 'f' + Math.floor(Math.random() * 99999);
        fileUploader.uploadIframeId = id ;

        div = document.createElement('div');
        div.innerHTML = '<iframe style="display: none;" src="about:blank" id="'+id+'" name="'+id+'" onload="'+callback_name+'"></iframe>';
        document.body.appendChild(div);

        return document.getElementById(id);
    }

};


/*
	Специальная надстройка для класса fileUploader
*/

fileUploader.initLoaderWith = function(doc_category, fileupload_callback){

    fileUploader.doc_category = doc_category;

    fileUploader.initedByTab = $(this);

    fileUploader.fileupload_callback = fileupload_callback ;
    fileUploader.file_callback = fileUploader.onExternalSuccess ;

};


fileUploader.flushAndPrepareforNewUpload = function(){

    $('form.FileUploadForm').attr({
        'action': '',
        'target': '',
    });

    fileUploader.upload_counter = 0;

};

fileUploader.onExternalSuccess = function(data){

    //console.log('msg');


    msg = data.output ;

    console.log(msg);

    if (msg.error != 'false'){

        //413 (Request Entity Too Large)

        if (typeof(msg.error_text) == "undefined")
            msg.error_text = msg.error ;

        if (typeof(swal) != "undefined"){
            swal({
                html: msg.error_text,
                showConfirmButton: false,
            });
        }
        else {
            alert(msg.error_text);
        }

        return ;
    }


    $('#gdfile_upload_input').val('');
    fileUploader.flushAndPrepareforNewUpload();

    /*
        Грузим лист в панель
    */

    fileUploader.fileupload_callback.apply(null, [msg]);
    //fileUploader.onExternalSuccess(msg);

};

fileUploader.setOriginMode = function(origin){

    fileUploader.origin = origin ;

}

fileUploader.externalFileReceiver = function(event){

    var data = JSON.parse(event.data);

    if (data.engine != 'gdExtUpload')
        return;


    console.log('attemp callback "' + data.file_callback + '"');

    fileUploader[data.file_callback].apply(null, [data]);

};

fileUploader.initListener = function(){


    if (window.addEventListener) {
        window.addEventListener("message", fileUploader.externalFileReceiver);
    } else {
        // IE8
        window.attachEvent("onmessage", fileUploader.externalFileReceiver);
    }


};

/*
	Import
*/


//base64_encode
var Base64={_keyStr:"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",encode:function(input){var output="";var chr1,chr2,chr3,enc1,enc2,enc3,enc4;var i=0;input=Base64._utf8_encode(input);while(i<input.length){chr1=input.charCodeAt(i++);chr2=input.charCodeAt(i++);chr3=input.charCodeAt(i++);enc1=chr1>>2;enc2=((chr1&3)<<4)|(chr2>>4);enc3=((chr2&15)<<2)|(chr3>>6);enc4=chr3&63;if(isNaN(chr2)){enc3=enc4=64}else if(isNaN(chr3)){enc4=64}output=output+this._keyStr.charAt(enc1)+this._keyStr.charAt(enc2)+this._keyStr.charAt(enc3)+this._keyStr.charAt(enc4)}return output},decode:function(input){var output="";var chr1,chr2,chr3;var enc1,enc2,enc3,enc4;var i=0;input=input.replace(/[^A-Za-z0-9\+\/\=]/g,"");while(i<input.length){enc1=this._keyStr.indexOf(input.charAt(i++));enc2=this._keyStr.indexOf(input.charAt(i++));enc3=this._keyStr.indexOf(input.charAt(i++));enc4=this._keyStr.indexOf(input.charAt(i++));chr1=(enc1<<2)|(enc2>>4);chr2=((enc2&15)<<4)|(enc3>>2);chr3=((enc3&3)<<6)|enc4;output=output+String.fromCharCode(chr1);if(enc3!=64){output=output+String.fromCharCode(chr2)}if(enc4!=64){output=output+String.fromCharCode(chr3)}}output=Base64._utf8_decode(output);return output},_utf8_encode:function(string){string=string.replace(/\r\n/g,"\n");var utftext="";for(var n=0;n<string.length;n++){var c=string.charCodeAt(n);if(c<128){utftext+=String.fromCharCode(c)}else if((c>127)&&(c<2048)){utftext+=String.fromCharCode((c>>6)|192);utftext+=String.fromCharCode((c&63)|128)}else{utftext+=String.fromCharCode((c>>12)|224);utftext+=String.fromCharCode(((c>>6)&63)|128);utftext+=String.fromCharCode((c&63)|128)}}return utftext},_utf8_decode:function(utftext){var string="";var i=0;var c=c1=c2=0;while(i<utftext.length){c=utftext.charCodeAt(i);if(c<128){string+=String.fromCharCode(c);i++}else if((c>191)&&(c<224)){c2=utftext.charCodeAt(i+1);string+=String.fromCharCode(((c&31)<<6)|(c2&63));i+=2}else{c2=utftext.charCodeAt(i+1);c3=utftext.charCodeAt(i+2);string+=String.fromCharCode(((c&15)<<12)|((c2&63)<<6)|(c3&63));i+=3}}return string}}



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


