<?php

require_once('./config.php');

class DORURURU_BOT
{
    /**
     * sending message to LINE BOT API SERVER
     **/
    private function _post($post)
    {
        $url = 'https://trialbot-api.line.me/v1/events';
        $headers = [
            'Content-Type: application/json',
            'X-Line-ChannelID: '.LINE_CHANNEL_ID,
            'X-Line-ChannelSecret: '.LINE_CHANNEL_SECRET,
            'X-Line-Trusted-User-With-ACL: '.LINE_MID
        ];

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($curl);
        syslog(LOG_INFO, $post);
    }

    /**
     * create sending message from received message
     */
    private function _getContent($received_text)
    {
        $text = '(^o^ ∋ )卍ﾄﾞｩﾙﾙﾙﾙﾙﾙﾙﾙﾙ';
        $image = null;

        if (preg_match('/\Aこんにちは/', $received_text)) {
            $text = 'こんにちは！';
        } elseif (preg_match('/ﾄﾞｩﾙﾙﾙ/m', $received_text)) {
            $image = 'dorururu';
        } elseif (preg_match('/最高/m', $received_text)) {
            $image = 'saiko';
        }

        if ($image) {
            return [
                'toType' => 1,
                'contentType' => 2,
                'originalContentUrl' => API_SERVER_URL."/img/{$image}.jpg",
                'previewImageUrl' => API_SERVER_URL."/img/{$image}_thum.jpg"
            ];
        } else {
            return [
                'toType' => 1,
                'contentType' => 1,
                'text' => $text
            ];
        }
    }

    public function run()
    {
        $json_string = file_get_contents('php://input');
        $json_object = json_decode($json_string);
        $content = $json_object->result{0}->content;
        $text = $content->text;
        $from = $content->from;
        $message_id = $content->id;
        $content_type = $content->contentType;

        syslog(LOG_INFO, $text);

        $content = $this->_getContent($text);

        $post = [
            'to' => [$from],
            'toChannel' => '1383378250',
            'eventType' => '138311608800106203',
            'content' => $content
        ];

        $this->_post(json_encode($post));
    }
}

try {
    $bot = new DORURURU_BOT();
    $bot->run();
} catch (Exception $e) {
    syslog(LOG_ERROR, $e->getMessage());
}
