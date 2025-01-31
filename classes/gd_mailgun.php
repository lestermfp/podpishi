<?php

include_once($_CFG['root'] . 'ajax/api/vendor/autoload.php');

use Mailgun\Mailgun;

class gdMailgun {


    static function getSenderDetails($_mail){
        Global $_CFG ;

        $sDetails = [];

        $send_from = $_CFG['mailgun']['default_from'] ;
        $send_title = $_CFG['mailgun']['default_title'];
        $send_domain = $_CFG['mailgun']['default_domain'] ;

        $subject = $_mail->read_attribute('subject');

        // base64 support
        if (substr($subject, 0, 2) == '==')
            $subject = base64_decode(substr($subject, 2));

        if ($_mail->send_from != '')
            $send_from = $_mail->send_from;

        if (isset($_CFG['mailgun']['customTitles'][$send_from]))
            $send_title = $_CFG['mailgun']['customTitles'][$send_from] ;

        if ($_mail->send_title != '')
            $send_title = $_mail->send_title;


        $extraParams = array();

        $from_details = '{send_title} <{send_from}@{send_domain}>';

        $from_details = str_replace('{send_title}', $send_title, $from_details);
        $from_details = str_replace('{send_from}', $send_from, $from_details);
        $from_details = str_replace('{send_domain}', $send_domain, $from_details);

        $sDetails['from_details'] = $from_details ;
        $sDetails['subject'] = $subject ;
        $sDetails['extraParams'] = $extraParams ;

        return $sDetails ;

    }

    static function sendCollection ($mailQueue){
        Global $_CFG ;

        # Instantiate the client.
        $mgClient = new Mailgun($_CFG['mailgun']['apiKey']);
        $domain = $_CFG['mailgun']['domain'] ;

        foreach ($mailQueue as $_mail){

            $sDetails = self::getSenderDetails($_mail);

            //print '<pre>' . print_r($sDetails, true) . '</pre>';
            //exit();

            $params = array(
                'from'    => $sDetails['from_details'],
                'to'      => $_mail->read_attribute('receiver'),
                'subject' => $sDetails['subject'],
                'html'    => $_mail->read_attribute('text'),
                'o:campaign' => $_mail->read_attribute('type'),
                'o:tracking-opens' => 'yes',
                'v:hook_arg' => $_mail->read_attribute('hook_arg'),
                'v:hook_connected_to' => $_mail->read_attribute('id'),
                'o:tag' => array($_mail->read_attribute('type')),
            );

            //print '<pre>' . print_r($params, true) . '</pre>';

            //exit();

            if (strpos($_mail->details, 'attaches') !== false){


                $details = json_decode($_mail->read_attribute('details'), true);

                $attachmentDir = $_CFG['root'] . 'static/mails/attach/';

                $extraParams['attachment'] = array();

                foreach ($details['attaches'] as $_attach)
                    $extraParams['attachment'][] = ['filePath'=> $attachmentDir . $_attach, 'remoteName'=> str_replace(' (1)', '', basename($_attach))] ;

            }

            try {
                # Make the call to the client.
                $result = $mgClient->sendMessage($domain, $params, $sDetails['extraParams']);

                if ($result->http_response_code == 200){

                    if ($_mail->read_attribute('details') != ''){

                        $details = json_decode($_mail->read_attribute('details'), true);

                        if (isset($details['strip'])){

                            foreach ($details['strip'] as $_stripWhat){

                                $_mail->text = str_replace($_stripWhat, '{stripped}', $_mail->read_attribute('text'));

                            }

                            $details['strip'] = array('ok');
                            $_mail->details = json_encode($details);
                        }

                    }

                    $_mail->result = 'sent';
                    $_mail->save();

                }
                else {

                    $_mail->result = 'rejected';
                    $_mail->drop_details = $result->http_response_code ;
                    $_mail->save();

                }

            } catch (Exception $e) {

                print '<pre>' . print_r($e, true) . '</pre>';

                $_mail->result = 'rejected';
                $_mail->drop_details = json_encode($e);
                $_mail->save();

            }

            continue ;

        }

    }


}

?>