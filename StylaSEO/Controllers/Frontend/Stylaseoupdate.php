<?php

class Shopware_Controllers_Frontend_Stylaseoupdate extends Enlight_Controller_Action {
    // TODO consider 30 seconds max execution time
    // TODO: set document type of response to JSON
    public function indexAction(){
        $Bootstrap = new Shopware_Plugins_Frontend_StylaSEO_Bootstrap('StylaSEO');
        $config = Enlight_Application::Instance()->Bootstrap()->Config();
        $username = $config->get('styla_modular_content_username');
        $seo_url = $config->get('styla_seo_url');
        $api = $config->get('styla_modular_content_api');
        $locale = $this->getCurrentLocale();

        if (!$username){
            $this->assignViewVariables(0, 0, 0, '', 'Modular content username not set');
            return;
        }

        $storyList = json_decode($this->fetchStories($api, $username));

        if (property_exists($storyList, 'success') && !$storyList->success) {
            $this->assignViewVariables(null, 0, 0, '', 'Modular content username not valid');
            return;
        }

        $processedCount = 0;
        $cachedStories = $this->countStories();
        $limit = $this->getLimit($this->Request()->getParam('limit'));
        $recacheall = $this->Request()->getParam('recacheall');
        $countStories = 1;
        if ($recacheall){
            $this->recacheAll();
            return;
        }
        try {
            foreach($storyList as $singleStory){
                if ($countStories > $limit){
                    break;
                }
                $path = 'story/' . ltrim($singleStory->slug, '/');
                $seoContent = StylaUtils::getRemoteContent($username, $path, rtrim($seo_url, '/') . '/', false);
                $this->updateStory($locale, $path, $this->escapeHtml($seoContent['noscript_content']), $singleStory->timeLastUpdatedEpoch);
                $processedCount++;
                $countStories++;
            }
            $this->assignViewVariables($singleStory, $processedCount, $cachedStories + $processedCount, $path);
        } catch (Exception $e) {
            $this->assignViewVariables($singleStory, $processedCount, $cachedStories + $processedCount, $path, $e->getMessage());
        }

    }

    public function assignViewVariables($lastStory, $processedCount, $totalStories, $lastCachedPath, $error=""){
        $timeLastUpdatedEpoch = 0;
        if ($lastStory) {
            $timeLastUpdatedEpoch = $lastStory->timeLastUpdatedEpoch;
        }
        $this->View()->assign('lastUpdated', $timeLastUpdatedEpoch);
        $this->View()->assign('processedCount', $processedCount);
        $this->View()->assign('totalStories', $totalStories);
        $this->View()->assign('lastCachedPath', $lastCachedPath);
        $this->View()->assign('error', htmlentities($error));
    }

    public function getLimit($limitParam){
        if (!is_numeric($limitParam) || $limitParam < 2){
            return 2;
        }
        if ($limitParam > 25){
            return 25;
        }
        if ($limitParam > 2 && $limitParam <= 25){
            return $limitParam;
        }
        return 2;
    }

    public function fetchLatestTimeUpdated(){
        $lastUpdatedQuery = "SELECT time_updated FROM s_styla_seo_content ORDER BY time_updated DESC LIMIT 1";
        $queryResult = Shopware()->Db()->fetchAll($lastUpdatedQuery);
        if (count($queryResult) > 0){
            return strtotime($queryResult[0]['time_updated']);
        }
        return 0;
    }

    public function escapeHtml($html) {
        $html = htmlentities($html);
        $html = str_replace("'", "\'", $html);
        return $html;
    }

    public function selectStories($locale, $path) {
        $seoQuery = "SELECT * FROM s_styla_seo_content WHERE locale = ? AND path = ?";
        $queryResult = Shopware()->Db()->fetchAll($seoQuery,[$locale,$path]);
        return $queryResult;
    }

    public function countStories() {
        $seoQuery = "SELECT COUNT(*) as total FROM s_styla_seo_content";
        $queryResult = Shopware()->Db()->fetchOne($seoQuery);
        return $queryResult;
    }

    public function updateStory($locale, $path, $content, $timeLastUpdated) {
        if (empty(trim($content))){
            throw new Exception('Seo content should not be empty for locale: ' . $locale . ' and path: '. $path);
        }
        $result = $this->selectStories($locale, $path);
        if (count($result) > 0){
            $query = "UPDATE s_styla_seo_content SET `content` = ?, `time_updated` = ? WHERE `locale` = ? AND `path` = ?"; //TODO: use last update from endpoint instead of now
            $queryResult = Shopware()->Db()->query($query,[$content,$this->timestampToDate($timeLastUpdated),$locale,$path]);
        }
        else {
            $query = "INSERT INTO s_styla_seo_content (`path`, `locale`, `content`, `time_updated`, `time_created`) VALUES  (?, ?, ?, ?, now())"; //TODO: use last update from endpoint instead of now
            $queryResult = Shopware()->Db()->query($query,[$path,$locale,$content,$this->timestampToDate($timeLastUpdated)]);
        }
    }

    public function recacheAll() {
        $query = "UPDATE s_styla_seo_content SET `time_updated` = '0'";
        $queryResult = Shopware()->Db()->query($query);
    }

    public function fetchStories($api, $username){
        $fetchUrl = rtrim($api, '/') . '/api/delta/stories?domain=' . $username . '&timeLastUpdatedEpoch=' . $this->fetchLatestTimeUpdated(); //TODO create a method for URL building
        $response = $this->makeCurlCall($fetchUrl);
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

    public function timestampToDate($timestamp){
        return date('Y-m-d H:i:s', $timestamp);
    }

    public function kprint($var){
        echo '<pre>';
        print_r($var);
        echo '</pre>';
    }
}

?>
