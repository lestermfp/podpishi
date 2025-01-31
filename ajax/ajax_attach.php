<?php
include('../config.php');
//print '<pre>' . print_r($_SESSION, true) . '</pre>';
/*
	Скрипт обслуживает ajax запросы для авторизации и разлогинивания.
	Все ответы выводятся в виде JSON
*/

/*
    ini_set('display_errors', true);
    ini_set('error_reporting',  E_ALL);
    error_reporting(E_ALL + E_STRICT);
*/



class CReg_user_ajax extends gdHandlerSkeleton {
    public $output = array(); //Создаем массив который будет отослан в виде JSON.
    public $timeTypes = array(
        'created_at' => 'd.m', 'deadline_at' => 'd.m',
    );

    private $mimeToName = array(
        'image/jpeg' => '.jpg',
        'image/gif' => '.gif',
        'image/png' => '.png',
        'application/pdf' => '.pdf',
    );

    private $attaches_limit = array(
        'avatar_attach' => 2,
        'blog_image' => 25,
        'petition_attach' => 1500,
        'prjimg' => 1505,
        'payment_image' => 5100,
        'instruction' => 1000,
    );

    public function __construct() {
        $this->output['error'] = 'false';


        if (isset($_GET['context'])) {
            switch ($_GET['context']) {

                case 'upload__petitionAttach':
                    $this->upload__petitionAttach();
                    break;

                case 'list__petitionAttaches':
                    $this->list__petitionAttaches();
                    break;

                case 'delete__projectItemAttach':
                    $this->delete__projectItemAttach();
                    break;


            }
        }

        echo json_encode( $this->output );
    }


    /*
        Arg: attach_id

        */
    private function delete__projectItemAttach(){
        Global $_USER ;

        $this->output['attach_id'] = $_GET['attach_id'] ;

        $this->checkAuthLevel(array('news_editor', 'admin', 'coordinator'));
        if ($this->output['error'] != 'false') return;

        $attach = db_attaches_list::find('first', array(
            'conditions' => array('attach_id=? AND category="petition_attach"', $_GET['attach_id']),
        ));

        if (empty($attach)){
            $this->output['notify'] = 'ntf';
            return ;
        }

        $campaign = new pdPetitions($attach->connected_id);

        if (!$campaign->isExists()){
            $this->output['error'] = 'ntf campaign';
            $this->output['error_text'] = 'Не найдена кампания';
            return ;

        }

        if (!$campaign->hasPrevi()){
            $this->output['error'] = 'wrong previ';
            $this->output['error_text'] = 'Недоступная кампания';
            return ;
        }

        mdFiles::removeAttachedFile($attach);

        //print '<pre>' . print_r($this->output, true) . '</pre>';
    }


    /*
    Arg: connected_id, $_USER[user_id]

*/
    private function list__petitionAttaches(){
        Global $_USER ;

        $this->checkAuthLevel(array('news_editor', 'admin', 'coordinator'));
        if ($this->output['error'] != 'false') return;

        if (!isset($_GET['connected_id']) OR !is_numeric($_GET['connected_id'])) exit();

        $campaign = new pdPetitions($_GET['connected_id']);

        if (!$campaign->isExists()){
            $this->output['error'] = 'ntf campaign';
            $this->output['error_text'] = 'Не найдена кампания';
            return ;

        }

        if (!$campaign->hasPrevi()){
            $this->output['error'] = 'wrong previ';
            $this->output['error_text'] = 'Недоступная кампания';
            return ;
        }

        $attaches_list = db_attaches_list::find('all', array(
            'conditions' => array('connected_id=? AND category="petition_attach"', $_GET['connected_id']),
        ));

        $this->output['attachList'] = mdFiles::parseAttachesOutput($attaches_list);

        //print '<pre>' . print_r($this->output, true) . '</pre>';
    }

    /*
        Arg: connected_id, _FILES
    */
    private function upload__petitionAttach(){
        Global $_CFG, $_USER ;

        $this->checkAuthLevel(array('news_editor', 'admin', 'settler', 'coordinator'));
        if ($this->output['error'] != 'false') return;

        $campaign = new pdPetitions($_GET['connected_id']);

        if (!$campaign->isExists()){
            $this->output['error'] = 'ntf campaign';
            $this->output['error_text'] = 'Не найдена кампания';
            return ;

        }

        if (!$campaign->hasPrevi()){
            $this->output['error'] = 'wrong previ';
            $this->output['error_text'] = 'Недоступная кампания';
            return ;
        }


        $this->uploadNewAttach('petition_attach', $_GET['connected_id'], array('imageOnly'));

        //$this->output['task']['connected_id'] = $_GET['connected_id'];

        //print '<pre>' . print_r($this->output, true) . '</pre>';
        //print '<pre>' . print_r($_FILES, true) . '</pre>';

    }


    private function uploadNewAttach($_filecategory, $connected_id, $params = array()){
        Global $_USER, $_CFG ;

        $allowedExtensions = array(
            'pdf'
        );

        if (in_array('attachScope', $params))
            $allowedExtensions = [
                'pdf', 'docx', 'pptx', 'xls', 'doc', 'xlsx'
            ];

        $user_id = -1 ;

        if (is_authed())
            $user_id = $_USER->getOptions('id');

        if (isset($_FILES['fileupload'])){
            $file = $_FILES['fileupload'] ;
            $error = '';

            if (!is_uploaded_file($file['tmp_name'])){
                $error = 'Не удаётся загрузить файл';
            }
            else {

                $extension = explode('.', $file['name']);
                $extension = $extension[count($extension) - 1];
                $extension = mb_convert_case($extension, MB_CASE_LOWER, 'utf-8');

                if ($extension == 'jpeg')
                    $extension = 'jpg';

                //Проверяем размер файла
                if ($file['size'] > 1024 * 1024 * 25){
                    $error = 'Размер файла превышает 25 Мб';
                }
                //проверяем тип файла
                if(($f_type = getimagesize($file['tmp_name'])) != true){


                    // Если ожидается изображение
                    if (in_array('imageOnly', $params)){
                        $error = 'Загруженный файл не является картинкой';
                    }
                    else {
                        //Проверяем размер файла
                        if ($file['size'] > 25 * 1024 * 1024){
                            $error = 'Размер файла превышает 25 Мб';
                        }

                        if (!in_array(strtolower($extension), $allowedExtensions) OR strpos($file['name'], '.') === false){
                            $error = 'Недопустимый формат файла, разрешены только';

                            foreach ($allowedExtensions as $_allowedExt)
                                $error .= ' .' . $_allowedExt . ', ';

                            $error = rtrim($error, ', ');
                        }
                    }
                }
                else {
                    //Убеждаемся, что пришедший файл - в одном из нужных нам форматов
                    if (!in_array($f_type['mime'], array('image/jpeg','image/png','image/gif'))){
                        $error = 'Разрешены только форматы .PNG, .JPG и .GIF';
                    }
                }


                if (strlen($file['name']) > 95){
                    $error = 'Слишком длинное название файла';
                }

                $attaches_exists = db_attaches_list::find('all', array(
                    'conditions' => array('user_id=? AND connected_id=? AND category=?', $user_id, $connected_id, $_filecategory),
                ));

                if (!isset($this->attaches_limit[$_filecategory]))
                    $this->attaches_limit[$_filecategory] = 1 ;

                //print '<pre>' . print_r($attaches_exists, true) . '</pre>';

                //print $_filecategory . '= .' . $attaches_exists;
                //exit();
                if (count($attaches_exists) + 1 > $this->attaches_limit[$_filecategory] AND empty($error)){
                    $error = 'Превышен лимит на количество прикрепляемых файлов';


                    // особый кейс для аватарок
                    if ($_filecategory == 'avatar_attach'){

                        foreach ($attaches_exists as $_attach_exists){
                            $_GET['attach_id'] = $_attach_exists->read_attribute('attach_id');
                            mdFiles::removeAttachedFile($_attach_exists);
                        }

                        $error ='';

                    }
                    else {
                        // Удаляем предыдущий аттач, если лимит на число файлов = 1
                        if ($this->attaches_limit[$_filecategory] == 1){
                            $_GET['attach_id'] = $attaches_exists[0]->read_attribute('attach_id');
                            mdFiles::removeAttachedFile($attaches_exists[0]);

                            $error ='';
                        }
                    }



                }

            }

            if (!in_array($extension, array_merge(['jpg', 'png', 'gif', 'pdf'], $allowedExtensions)))
                $error = 'Недопустимый формат файла';

            if (empty($error)){


                $type = '.pdf';

                if (is_array($f_type)){
                    $type = $this->mimeToName[$f_type['mime']];
                }
                else {
                    $f_type['mime'] = 'application/pdf';
                }

                /*
                    Генерируем уникальный
                */


                $connection = db_attaches_list::connection() ;
                $connection->query('LOCK TABLES `attaches_list` WRITE');

                while (true){

                    $download_hash_md5 = md5(mt_rand(1,999) . time() . mt_rand(0,1) . md5(serialize($file)));

                    if (db_attaches_list::exists(array(
                            'conditions' => array('download_hash_md5=?', $download_hash_md5),
                        )) == true)
                        continue ;

                    break ;

                }


                $hash_md5 = md5(file_get_contents($file['tmp_name']));

                $subdir_name = substr($download_hash_md5, 0, 10);

                $save_name = $download_hash_md5 . '.' . $extension;
                $new_path = $_CFG['root'] . 'static/attach/' . $subdir_name . '/';

                if (!file_exists($new_path)){

                    if (mkdir($new_path) == false){
                        $this->output['error'] = 'Непонятная ошибка №1';
                        return ;
                    }

                }

                if (file_exists($new_path . $save_name)){
                    $this->output['error'] = 'Непонятная ошибка №2';
                    return ;
                }

                $result = move_uploaded_file($file['tmp_name'], $new_path . $save_name);

                $newAttach = array(
                    'user_id' => $user_id,
                    'connected_id' => $connected_id,
                    'category' => $_filecategory,
                    'filename' => htmlspecialchars($file['name']),
                    'size' => $file['size'] / 1024,
                    'extension' => $extension,
                    'mime' => $f_type['mime'],
                    'hash_md5' => $hash_md5,
                    'download_hash_md5' => $download_hash_md5,
                    'date' => 'now',
                );

                //print '<pre>' . print_r($newAttach, true) . '</pre>';

                //exit();

                $this->uploaded_attach = db_attaches_list::create($newAttach);

                $connection->query('UNLOCK TABLES');

                $link = $subdir_name . '/' . $download_hash_md5 . $type;

                $this->output['attachList'][] = array_merge($this->uploaded_attach->to_array(), array('link' => $link));


            }
            else {
                $this->output['error'] = $error ;
                return ;
            }
        }

        //print '<pre>' . print_r($this->output, true) . '</pre>';

        return ;

        print '<pre>' . print_r($_GET, true) . '</pre>';
        print '<pre>' . print_r($_FILES, true) . '</pre>';
        print '<pre>' . print_r($_POST, true) . '</pre>';
    }

}

$aux = new CReg_user_ajax;
?>