<?php

class StylaUtils{

    const STYLA_URL = 'http://dev.styla.com/';	//'http://www.amazine.com/';
    const CACHE_TTL = 3600; // Cache expires in 1 hour
    protected static $_username = '';
    protected static $_res = '';

    public static function getJsEmbedCode($username, $js_url = null){
        if(!$js_url)
                $js_url = self::STYLA_URL;

        //$url = preg_filter('/https?:(.+)/i', '$1', (rtrim($js_url, '/').'/')).'scripts/embed/'.$username.'.js';
        //return '<script id="amazineEmbed" type="text/javascript" src="'.$url.'" defer="defer"></script>';
	$url = preg_filter('/https?:(.+)/i', '$1', (rtrim($js_url, '/').'/')).'scripts/preloader/'.$username.'.js';
        //return '<script id="stylaMagazine" type="text/javascript" src="'.$url.'" defer="defer"></script>';
	return '<script type="text/javascript" src="'.$url.'" defer="defer"></script>';
    }

    public static function getActionFromUrl($basedir = 'magazin'){
        $url = $_SERVER['REQUEST_URI'];
        $action = preg_filter('(/en)?/'.$basedir.'/([^\/]+).*/i', '$2', $url);
	return $action;        
	///return empty($action) ? 'index' : $action;
    }

    public static function getParamFromUrl($search){
        $arr = parse_url($_SERVER['REQUEST_URI']);
        $url = $arr['path'];
        if(($start = strpos($url,$search))===false)
            return false;

        $ret = substr($url, $start+strlen($search)+1);
        return rtrim($ret,'/');
    }

    public static function getRemoteContent($username, $params, $src_url = null){

        $cache = Shopware()->Cache();
        $config = Shopware()->Config();

        if(!$src_url)
            $src_url =  self::STYLA_URL;

        $type = $params['type'];
        self::$_username = $username;

        if($type=='tag')
            $url = $src_url.'user/'.$username.'/tag/'.$params['tagname'];
        elseif($type=='story')
            $url = $src_url.'story/'.$params['storyname'];
        elseif($type=='user')
            $url = $src_url.'user/'.$params['username'];
        else
            $url = $src_url.'user/'.$username; // magazine default

        $cache_key = self::getCacheKey($url);

        if (!empty($config->caching)) {
            if (!$cache->test($cache_key)) {
                $arr = self::_loadRemoteContent($url, $type);
                if(!$arr)
                    return;

                $cache->save($arr, $cache_key, array('Shopware_Plugin'), self::CACHE_TTL);
            } else {
                $arr = $cache->load($cache_key);
            }
        } else {
            $arr = self::_loadRemoteContent($url, $type);
        }

        return $arr;

    }

    private static function _loadRemoteContent($url, $type = null){
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

            /** DEFAULT SET OF METADATA  */
            // Noscript content
            if(preg_match('/<noscript>(.*)<\/noscript>/is', self::$_res, $matches)){
                $ret['noscript_content'] = $matches[1];
            }

            // Meta description
            $ret['meta']['description'] = self::_getMetadataValueByName('description');

            // Page title
            if(preg_match('/<title>(.*)<\/title>/is', self::$_res, $matches)){
                $ret['page_title'] = $matches[1];
            }

            // Canonical link
            if(preg_match('/(<link rel="canonical"[^>]+>)/is', self::$_res, $matches)){
                $ret['canonical_link'] = $matches[1];
            }

            if($type == 'user' || $type == 'magazine' || $type == 'story'){
                // Facebook & opengraph tags
                $ret['meta']['fb_app_id'] = self::_getMetadataTagsByProperty('fb:app_id');
                $ret['meta']['og'] = array();
                $ret['meta']['og']['type'] = self::_getMetadataTagsByProperty('og:type');
                $ret['meta']['og']['title'] = self::_getMetadataTagsByProperty('og:title');
                $ret['meta']['og']['image'] = self::_getMetadataTagsByProperty('og:image');
                $ret['meta']['og']['url'] = self::_getMetadataTagsByProperty('og:url');
                // Author link
                if(preg_match('/(<link rel="author"[^>]+>)/is', self::$_res, $matches)){
                    $ret['author'] = $matches[1];
                }
            }

            if($type == 'story'){
                // Meta keywords
                $ret['meta']['keywords'] = self::_getMetadataValueByProperty('keywords');
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

    private static function _getMetadataValueByName($name){
        return self::_getMetadataValue('name', $name);
    }

    private static function _getMetadataValueByProperty($property){
        return self::_getMetadataValue('property', $property);
    }

    private static function _getMetadataTagsByProperty($property){
        return self::_getMetadataTags('property', $property);
    }

    private static function _getMetadataValue($type, $key){
        if(preg_match('/<meta [^>]*'.$type.'="'.$key.'" (.*?)content="([^"]+)"\W?\/>/is', self::$_res, $matches)){
            return $matches[2];
        }
    }

    private static function _getMetadataTags($type, $key){
        if(preg_match_all('/(<meta [^>]*'.$type.'="'.$key.'" (.*?)content="([^"]+)"\W?\/>)+/is', self::$_res, $matches)){
            $ret = $matches[0];
            if(!is_array($ret))
                return false;

            return implode("\r\n", $ret);
        }
    }

}
