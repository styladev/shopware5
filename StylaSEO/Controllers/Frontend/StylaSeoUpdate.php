<?php

class Shopware_Controllers_Frontend_StylaSeoUpdate extends Enlight_Controller_Action {
    // TODO consider 30 seconds max execution time
    public function indexAction(){
        $Bootstrap = new Shopware_Plugins_Frontend_StylaSEO_Bootstrap;
        $config = Enlight_Application::Instance()->Bootstrap()->Config();
        $username = $config->get('styla_modular_content_username');
        $seo_url = $config->get('styla_seo_url');
        $api = $config->get('styla_modular_content_api');
        $locale = $this->getCurrentLocale();
        $storiesObj = json_decode($this->fetchStories($api, $username));

        foreach($storiesObj->stories as $singleStory){
            $path = 'story/' . ltrim($singleStory->externalPermalink, '/');
            $seoContent = StylaUtils::getRemoteContent($username, $path, '', rtrim($seo_url, '/') . '/', false);
            $result = $this->updateStory($locale, $path, $this->escapeHtml($seoContent['noscript_content']));
        }
    }

    public function fetchLatestTimeUpdated(){
        return '1483278406'; // TODO: use real value
    }

    public function escapeHtml($html) {
        $html = htmlentities($html);
        $html = str_replace("'", "\'", $html);
        return $html;
    }

    public function selectStories($locale, $path) {
        $seoQuery = "SELECT * FROM s_styla_seo_content WHERE locale = '" . $locale . "' AND path = '" . $path . "'";
        $queryResult = Shopware()->Db()->fetchAll($seoQuery);
        return $queryResult;
    }

    public function updateStory ($locale, $path, $content) {
        $result = $this->selectStories($locale, $path);
            if (count($result) > 0){
                $query = "UPDATE s_styla_seo_content SET `content` = '$content', `time_updated` = now() WHERE `locale` = '$locale' AND `path` = '$path'";
            }
            else {
                $query = "INSERT INTO s_styla_seo_content (`path`, `locale`, `content`, `time_updated`, `time_created`) VALUES  ('$path', '$locale', '$content', now(), now())";
            }
        $queryResult = Shopware()->Db()->query($query);
        return $query;
    }

    public function fetchStories($api, $username){
        $fetchUrl = rtrim($api, '/') . '/api/feeds/all?offset=0&limit=5&domain=' . $username . '&timeLastUpdatedEpoch=' . $this->fetchLatestTimeUpdated(); //TODO create a method for URL building
        $response = $this->makeCurlCall($fetchUrl);
        // $this->View()->assign('stories', $response);
        return $response;
    }

    public function makeCurlCall($url){
        $curl = new StylaCurl();

        $curl_opts = array(
            CURLOPT_PORT => 80,
            CURLOPT_POST => 0
        );

        if(!$res = $curl->call($url, $curl_opts))
            return false;

        return $res;
    }

    public function getCurrentLocale(){
        $shopContext = $this->get('shopware_storefront.context_service')->getShopContext();
        $lang = $shopContext->getShop()->getLocale()->getLocale();
        return $lang;
    }

    // TODO: remove this function - only used for debugging
    public function kprint($var){
        echo '<pre>';
        print_r($var);
        echo '</pre>';
    }
}

?>
