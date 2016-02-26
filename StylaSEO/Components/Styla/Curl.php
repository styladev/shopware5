<?php

class StylaCurl{

    static $default_opts = array(
        CURLOPT_HEADER => 0,
        CURLOPT_HTTPHEADER => array('User-Agent: Styla'),
        CURLOPT_FRESH_CONNECT => 1,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_FORBID_REUSE => 1,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_USERPWD => null,
        CURLOPT_VERBOSE => 0,
        CURLOPT_POST => 1,
        CURLOPT_PORT => 443
    );

    static $options = array();

    static function getOptions(){
        return self::$options + self::$default_opts;
    }

    static function call($url, $opts = array(), $post = NULL, $headers = array()) {

        $options = self::getOptions();

        $options[CURLOPT_URL] = $url;
        if(strpos($url, 'http://')===0 && !isset($opts[CURLOPT_PORT])){
            $parsedUrl = parse_url($url);
            if($parsedUrl === false)
                throw new Exception("Curl->call() cant parse url");
            $options[CURLOPT_PORT] = isset($parsedUrl['port']) ? $parsedUrl['port'] : 80; // if url is http and no port was specified, assume it's port 80 we want
        }

        if (is_array($post)) {
            $options[CURLOPT_POST] = 1;
            $options[CURLOPT_POSTFIELDS] = http_build_query($post);
        } else {
            if($post !== null)
                $options[CURLOPT_POSTFIELDS] = $post;
        }
        $ch = curl_init();
        $options = $opts + $options;
        $headers += self::$default_opts[CURLOPT_HTTPHEADER];

        curl_setopt_array($ch, $options);
        curl_setopt ($ch, CURLOPT_HTTPHEADER, $headers);

        //ob_start();
        $result = curl_exec($ch);

        //ob_end_clean();
        if($result === false) {
            $error_no = curl_errno($ch);
            throw new Exception('Curl->call() error #'. $error_no);
        }
        curl_close ($ch);

        return $result;
    }
}