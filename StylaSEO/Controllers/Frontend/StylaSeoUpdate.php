<?php

class Shopware_Controllers_Frontend_StylaSeoUpdate extends Enlight_Controller_Action {

    public function indexAction(){
        $Bootstrap = new Shopware_Plugins_Frontend_StylaSEO_Bootstrap;
        $config = Enlight_Application::Instance()->Bootstrap()->Config();
        $username = $config->get('styla_modular_content_username');
        $api = $config->get('styla_modular_content_api');
        $this->fetchStories($username, $api);
    }

    public function fetchLatestTimeUpdated(){
        return '1514764800'; // TODO: use real value
    }

    public function fetchStories($username, $api){
        $this->View()->assign('username', $username);
        $this->View()->assign('api', $api);
        $fetchUrl = rtrim($api, '/') . '/api/feeds/all?offset=0&limit=5&domain=' . $username . '&timeLastUpdatedEpoch=' . $this->fetchLatestTimeUpdated();

        $curl = new StylaCurl();

        $curl_opts = array(
            CURLOPT_PORT => 80,
            CURLOPT_POST => 0
        );

        $this->View()->assign('fetchUrl', $fetchUrl);

        if(!$res = $curl->call($fetchUrl, $curl_opts))
                return false;

        $obj = json_decode($res);

        $this->View()->assign('json', $res);
    }

}

?>
