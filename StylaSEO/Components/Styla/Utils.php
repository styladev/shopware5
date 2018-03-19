<?php

class StylaUtils{

    const STYLA_URL = 'http://live.styla.com/';
    const CACHE_TTL = 3600; // Cache expires in 1 hour
    protected static $_username = '';
    protected static $_res = '';

    public static function getJsEmbedCode($username, $js_url = null){
        if(!$js_url)
            $js_url = self::STYLA_URL;


	    $url = preg_filter('/https?:(.+)/i', '$1', (rtrim($js_url, '/').'/')).'scripts/preloader/'.$username.'.js';
	    return '<script type="text/javascript" src="'.$url.'" async></script>';
    }

    public static function getActionFromUrl($basedir = 'magazin'){
        $url = $_SERVER['REQUEST_URI'];
        $action = preg_filter('(/en)?/'.$basedir.'/([^\/]+).*/i', '$2', $url);
	    return $action;
    }

    public static function getCurrentPath($basedir = 'magazin'){
        $arr = parse_url($_SERVER['REQUEST_URI']);
        $url = $arr['path'];
        if(($start = strpos($url,$basedir))===false)
            return false;

        $ret = substr($url, $start+strlen($basedir)+1);
        return rtrim($ret, '/');
    }

    public static function getQueryFromUrl(){
        $url = parse_url($_SERVER['REQUEST_URI']);
        $query = $url['query'];
        return $query;
    }

    public static function getRemoteContent($username, $path, $query_params, $src_url = null, $caching_enabled=true){
        $cache = Shopware()->Cache();
        $config = Shopware()->Config();

        if(!$src_url)
            $src_url =  self::STYLA_URL;

        self::$_username = $username;
        if($query_params)
            $query_params = '?'.$query_params;
        $url = $src_url.'clients/'.$username.'?url='.$path.$query_params;

        $cache_key = self::getCacheKey($url);

        if ($caching_enabled && !empty($config->caching)) {
            if (!$cache->test($cache_key)) {
                $arr = self::_loadRemoteContent($url, $query_params);
                if(!$arr)
                    return;

                $cache->save($arr, $cache_key, array('Shopware_Plugin'), self::CACHE_TTL);
            } else {
                $arr = $cache->load($cache_key);
            }
        } else {
            $arr = self::_loadRemoteContent($url, '');
        }

        return $arr;

    }

    private static function _loadRemoteContent($url, $query_params){
        $curl = new StylaCurl();

        $curl_opts = array(
            CURLOPT_HTTPHEADER => array('Shopware Styla SEO Module for ' . self::$_username),
            CURLOPT_PORT => 80,
            CURLOPT_POST => 0
        );

        try{
            if(!self::$_res = $curl->call($url, $curl_opts))
                return false;

            $ret = array();
            $ret['meta'] = array();
            $json = json_decode(self::$_res);

            if(isset($json->status)) {
                $ret['status_code'] = $json->status;
                if ($json->status == 200) {
                    // head content
                    if(isset($json->html->head)){
                        $ret['head_content'] = $json->html->head;
                        // erase title and description from html head because duplicated
                        $titleRegex = '/<title>(.|\r\n)*?<\/title>/i';
                        $descriptionContent = array();
                        $descriptionRegex = '/<meta name="description" content="([^\"]*)">/i';
                        $description = preg_match($descriptionRegex, $ret['head_content'], $descriptionContent);
                        $descriptionContent = $descriptionContent[1];
                        $ret['head_content'] = preg_replace($titleRegex, "", $ret['head_content']);
                        $ret['head_content'] = preg_replace($descriptionRegex, "", $ret['head_content']);
                    }
                    // Noscript content
                    if(isset($json->html->body)){
                        $ret['noscript_content'] = $json->html->body;
                    }
                    $ret['page_title'] = $json->tags[0]->content;
                    $ret['meta_description'] = $descriptionContent;
                    $ret['query_params'] = $query_params;
                }
            }
            return $ret;

        }catch (Exception $e){
            echo 'ERROR: '.$e->getMessage().' url:'.$url;
        }

    }

    public static function getCacheKey($text){
        $de_chars = array('ä' => 'ae', 'ö' => 'oe', 'ü' => 'ue', 'ß' => 'ss');
        $text = preg_replace('/https?:\/\/(.+)/', '$1', $text); // remove protocol from the url
        $text = urldecode($text); //decode
        $text = str_replace(array_keys($de_chars), $de_chars, strtolower($text)); // replace German special chars with expanded version
        $text = preg_replace('/[\/:\.\-]/i','_','stylaseo_'.$text); // replace non-letter characters with underscore, prefix the key with 'stylaseo'
        $text = strtr($text,'àáâãçèéêëìíîïñòóôõùúûýÿ','aaaaceeeeiiiinoooouuuyy');// Removes any non-german accents from letter chars.
        $text = preg_replace('/[^A-Za-z0-9\_]/', '', $text); // Removes any remaining chars.
        return $text;
    }


}
