<?php

class StylaUtils{

    const STYLA_URL = 'http://live.styla.com/';
    const CACHE_TTL = 3600; // Cache expires in 1 hour
    protected static $_username = '';
    protected static $_res = '';

    public static function getJsEmbedCode($js_url = null){
        if(!$js_url) {
            return '';
        }

	    return '<script type="text/javascript" src="'.$js_url.'" async></script>';
    }

    public static function createTag($tagObj) {
        if ($tagObj->tag == "title" || $tagObj->tag == "script" || $tagObj->tag == "style") {
            $selfClosing = false;
        }
        else {
            $selfClosing = true;
        }

        $tag = '<';
        $tag .= $tagObj->tag;

        if ($tagObj->attributes && !empty((array) $tagObj->attributes)) {
            foreach ($tagObj->attributes as $key => $value) {
                $tag .= ' ' . $key . '="' . $value . '"';
            }
        }

        if ($selfClosing){
            if ($tagObj->tag == "meta" || $tagObj->tag == "link") {
                $tag .= '>';
            }
            else{
                $tag .= ' />';
            }
        }
        else {
            $tag .= '>';
            if ($tagObj->content) {
                $tag .= $tagObj->content;
            }
            $tag .= '</';
            $tag .= $tagObj->tag;
            $tag .= '>';
        }

        return $tag;
    }

    public static function getActionFromUrl($basedir = 'magazin'){
        $url = $_SERVER['REQUEST_URI'];
        $action = preg_filter('(/en)?/'.$basedir.'/([^\/]+).*/i', '$2', $url);
	    return $action;
    }

    public static function getQueryFromUrl(){
        $url = parse_url($_SERVER['REQUEST_URI']);
        $query = $url['query'];
        return $query;
    }

    public static function getRemoteContent($username, $path, $src_url = null, $caching_enabled=true){
        $cache = Shopware()->Cache();
        $config = Shopware()->Config();

        if(!$src_url)
            $src_url =  self::STYLA_URL;

        self::$_username = $username;

        $url = $src_url.'clients/'.$username.'?url='.$path;

        $cache_key = self::getCacheKey($url);

        if ($caching_enabled && !empty($config->caching)) {
            if (!$cache->test($cache_key)) {
                $arr = self::_loadRemoteContent($url);
                if(!$arr)
                    return;

                $cache->save($arr, $cache_key, array('Shopware_Plugin'), self::CACHE_TTL);
            } else {
                $arr = $cache->load($cache_key);
            }
        } else {
            $arr = self::_loadRemoteContent($url);
        }

        return $arr;

    }

    private static function _loadRemoteContent($url){
        $curl = new StylaCurl();

        $curl_opts = array(
            CURLOPT_HTTPHEADER => array('Shopware Styla SEO Module for ' . self::$_username),
            CURLOPT_PORT => 80,
            CURLOPT_POST => 0
        );

        try{
            if(!self::$_res = $curl->call($url, $curl_opts)) {
                return false;
            }

            $json = json_decode(self::$_res);

            if(isset($json->status)) {
                if ($json->status != 200) {
                    return array();
                }

                return self::parseSeoResponse($json);
            }
        }catch (Exception $e){
            $ret['status_code'] = 500;
            return $ret;
        }

    }

    public static function parseSeoResponse($response) {
        $ret = array(
            'status_code' => $response->status,
            'noscript_content' => $response->html->body,
            'robots' => 'index,follow',
            'openGraph' => '',
            'hreflang' => '',
            'otherTags' => '',
        );
        foreach($response->tags as $singleTag){
            if ($singleTag->tag == "title") {
                $ret['title'] = $singleTag->content;
            }
            else if ($singleTag->tag == "meta" && isset($singleTag->attributes->property) &&
                (self::startsWith($singleTag->attributes->property, "og") ||
                self::startsWith($singleTag->attributes->property, "twitter"))) {
                $ret['openGraph'] .= self::createTag($singleTag) . PHP_EOL;
            }
            else if ($singleTag->tag == "link" && isset($singleTag->attributes->property) &&
                $singleTag->attributes->property == "alternate") {
                $ret['hreflang'] .= self::createTag($singleTag) . PHP_EOL;
            }
            else if ($singleTag->tag == "meta" && isset($singleTag->attributes->name) &&
                $singleTag->attributes->name == "robots") {
                $ret['robots'] = $singleTag->attributes->content;
            }
            else if ($singleTag->tag == "link" && isset($singleTag->attributes->rel) &&
                $singleTag->attributes->rel == "canonical") {
                $ret['canonical'] = self::createTag($singleTag);
            }
            else if ($singleTag->tag == "meta" && isset($singleTag->attributes->name) &&
                $singleTag->attributes->name == "description") {
                $ret['description'] = $singleTag->attributes->content;
            }
            else {
                // These are dns prefetch and custom fonts
                $ret['otherTags'] .= self::createTag($singleTag) . PHP_EOL;
            }
        }

        return $ret;
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

    public static function startsWith($string, $startString) {
        return substr($string, 0, strlen($startString)) === $startString;
    }

}
