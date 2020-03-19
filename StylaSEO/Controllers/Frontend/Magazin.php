<?php

class Shopware_Controllers_Frontend_Magazin extends Enlight_Controller_Action {

    protected $_username          = null;
    protected $_source_url        = null;
    protected $_snippet_url       = null;
    protected $_feed_params       = array();
    protected $_base_dir          = null;



    public function preDispatch(Enlight_Event_EventArgs $args=null){

        $this->View()->setScope(Enlight_Template_Manager::SCOPE_PARENT);
        $this->View()->extendsTemplate('frontend/custom/index.tpl');
        $this->View()->loadTemplate('frontend/magazin/index.tpl');

        $config = Enlight_Application::Instance()->Bootstrap()->Config();
        $this->_username    = $config->get('styla_username');
        $this->_source_url  = $config->get('styla_seo_url');
        $this->_snippet_url = $config->get('styla_js_url');
        $this->_base_dir    = $config->get('styla_basedir');

        // make sure there is always (exactly 1) trailing slash
        $this->_source_url = rtrim($this->_source_url, '/').'/';

        if(!$this->_username) {
            die('No username set for Styla SEO plugin'); // TODO maybe something better than die, but then again since it's a required field this should never really be empty
        }
    }

    public function postDispatch(){
        $type = $this->_feed_params['type'];
        $js_include = StylaUtils::getJsEmbedCode($this->_snippet_url);

        $uri = parse_url($_SERVER['REQUEST_URI']);
        $path = $uri['path'];

        $ret = StylaUtils::getRemoteContent($this->_username, $path, $this->_source_url);

        $custom_page = $this->View()->getAssign('sCustomPage');
        if($ret){
            $custom_page['title'] = $ret['title'];
            $custom_page['metaTags'] = $ret['metaTags'];
            $stylaDiv = '<div data-styla-client="'.$this->_username.'">'.$ret['noscript_content'].'</div>';
            $this->View()->assign('sContent', "\r\n".$js_include."\r\n".$stylaDiv);
            $status_code = $ret['status_code'];
        }

        $this->View()->assign('sCustomPage', $custom_page);
        $this->View()->assign('feed_type', $type);
        $this->Response()->setHttpResponseCode($status_code);
    }

    public function indexAction(){

    }

}
