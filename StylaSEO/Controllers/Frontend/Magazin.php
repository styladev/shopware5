<?php

class Shopware_Controllers_Frontend_Magazin extends Enlight_Controller_Action {

    protected $_allowed_actions   = array('tag','story','user','query');
    protected $_username          = null;
    protected $_source_url        = null;
    protected $_snippet_url       = null;
    protected $_feed_params       = array();
    protected $_url_query_params  = null;
    protected $_base_dir          = null;



    public function preDispatch(Enlight_Event_EventArgs $args=null){

        $this->View()->setScope(Enlight_Template_Manager::SCOPE_PARENT);
        $this->View()->extendsTemplate('frontend/custom/index.tpl');
        $this->View()->loadTemplate('frontend/magazin/index.tpl');

        $config = Enlight_Application::Instance()->Bootstrap()->Config();
        $this->_username    = $config->get('styla_username');
        $this->_source_url  = $config->get('styla_seo_url');
        $this->_snippet_url = $config->get('styla_api_url');
        $this->_base_dir    = $config->get('styla_basedir');
        $this->_source_url = rtrim($this->_source_url, '/').'/'; // make sure there is always (exactly 1) trailing slash
        $this->_snippet_url = rtrim($this->_snippet_url, '/').'/'; // make sure there is always (exactly 1) trailing slash
        $this->_url_query_params = StylaUtils::getQueryFromUrl();

        if(!$this->_username) {
            die('No username set for Styla SEO plugin'); // TODO maybe something better than die, but then again since it's a required field this should never really be empty
        }
    }

    public function postDispatch(){

        $type = $this->_feed_params['type'];
        $js_include = StylaUtils::getJsEmbedCode($this->_username, $this->_snippet_url);

        $ret = null;

        if($type != 'search'){// for now at least we don't need any metadata coming back for search results
            $path = StylaUtils::getCurrentPath($this->_base_dir);
            $ret = StylaUtils::getRemoteContent($this->_username, $path, $this->_url_query_params, $this->_source_url);
        }

        $custom_page = $this->View()->getAssign('sCustomPage');
        if($ret){
            $custom_page['head_content'] = $ret['head_content'];
            $custom_page['page_title'] = $ret['page_title'];
            $custom_page['meta_description'] = $ret['meta_description'];
            $custom_page['query_params'] = $ret['query_params'];
            $stylaDiv = '<div id="stylaMagazine" data-magazinename="'.$this->_username.'" data-rootpath="'.$this->_base_dir.'"></div>';
            $this->View()->assign('sContent', $ret['noscript_content']."\r\n".$js_include."\r\n".$stylaDiv);
            $status_code = $ret['status_code'];
        }

        $this->View()->assign('sCustomPage', $custom_page);
        //$this->View()->assign('page_title', $page_title);
        //$this->View()->assign('meta_description', $meta_description);
        $this->View()->assign('feed_type', $type);
        $this->Response()->setHttpResponseCode($status_code);
    }

    public function indexAction(){

    }

    public function tagAction(){

    }

    public function storyAction(){

    }

    public function userAction(){

    }

    public function searchAction(){

    }

    public function queryAction(){
        $this->_url_query_params = StylaUtils::getQueryFromUrl();
    }

}
