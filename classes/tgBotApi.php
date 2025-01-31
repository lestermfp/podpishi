<?php


class tgBotApi {

    /*
     * Отправляем сообщение ботом в указанное место прямо дёргая контекст
     */
    static function sendMessageNow($telegram_id, $args){
        Global $_CFG;

        if (!is_array($args))
            $args = [
                'text' => $args,
            ];

        $messageParams = [
            'chat_id' => $telegram_id,
            'text' => $args['text'],
            'parse_mode' => 'HTML'
        ];

        foreach ($args as $_key => $_value)
            $messageParams[$_key] = $_value ;

        return self::apiRequest('sendMessage', $messageParams, $_CFG['tgBot']['api_url']);

    }

    static function sendToTelegram($telegram_id, $question_reply, $from_site_id = -1, $params = array()){

        $status = ['error' => 'false'];

        $question_reply = str_replace(['<br>', '<br/>'], '', $question_reply);

        $encoded_message = base64_encode($question_reply);

        $messageAdded = db_massbot_messages::find_by_message($encoded_message, array(
            'select' => 'message_id',
        ));

        if (!empty($messageAdded)){

        }
        else {

            $newMessage = array(
                'message' => $encoded_message,
                'from_site_id' => $from_site_id,
                'date' => 'now',
            );

            $messageAdded = db_massbot_messages::create($newMessage);
            if (null === $messageAdded) {
                $status['error'] = true;
                $status['error_text'] = 'не удалось создать сообщение в телеграм';
                return $status;
            }

        }

        $newDeliver = array(
            'message_id' => $messageAdded->read_attribute('message_id'),
            'chat_id' => $telegram_id,
            'status' => 'inqueue',
            'date' => 'now',
        );


        if (isset($params['priority']))
            $newDeliver['priority'] = $params['priority'] ;



        $deliver = db_massbot_deliver::create($newDeliver);

        if (null === $deliver) {
            $status['error'] = true;
            $status['error_text'] = 'не удалось отправить созданное сообщение в телеграм';
            return $status;
        }

        return $status;
    }


    static function exec_curl_request($handle) {
        $response = curl_exec($handle);

        if ($response === false) {
            $errno = curl_errno($handle);
            $error = curl_error($handle);
            error_log("Curl returned error $errno: $error\n");
            curl_close($handle);
            return false;
        }

        $http_code = intval(curl_getinfo($handle, CURLINFO_HTTP_CODE));
        curl_close($handle);

        if ($http_code >= 500) {
            // do not wat to DDOS server if something goes wrong
            sleep(10);
            return false;
        } else if ($http_code != 200) {


            $response = json_decode($response, true);
            error_log("Request has failed with error {$response['error_code']}: {$response['description']}\n");
            if ($http_code == 401) {
                throw new Exception('Invalid access token provided');
            }
            return $response;
        } else {

            $response = json_decode($response, true);
            if (isset($response['description'])) {
                error_log("Request was successfull: {$response['description']}\n");
            }
            $response = $response['result'];
        }



        return $response;
    }

    static function apiRequest($method, $parameters, $api_url) {

        if (!is_string($method)) {
            error_log("Method name must be a string\n");
            return false;
        }

        if (!$parameters) {
            $parameters = array();
        } else if (!is_array($parameters)) {
            error_log("Parameters must be an array\n");
            return false;
        }

        foreach ($parameters as $key => &$val) {
            // encoding to JSON array parameters, for example reply_markup
            if (!is_numeric($val) && !is_string($val)) {
                $val = json_encode($val);
            }
        }
        $url = $api_url.$method.'?'.http_build_query($parameters);

        //print 'Url is ' . $url . '<br />';

        $handle = curl_init($url);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($handle, CURLOPT_TIMEOUT, 60);

        return self::exec_curl_request($handle);
    }

}


?>