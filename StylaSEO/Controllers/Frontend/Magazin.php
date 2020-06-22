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

        $custom_page = $this->View()->getAssign('sCustomPage');
        
        $ret = StylaUtils::getRemoteContent($this->_username, $path, $this->_source_url);
        if($ret){
            $custom_page['title'] = $ret['title'];
            $custom_page['description'] = $ret['description'];
            $custom_page['canonical'] = $ret['canonical'];
            $custom_page['robots'] = $ret['robots'];
            $custom_page['openGraph'] = $ret['openGraph'];
            $custom_page['hreflang'] = $ret['hreflang'];
            $custom_page['otherTags'] = $ret['otherTags'];

            $stylaDiv = '<div data-styla-client="'.$this->_username.'">'.$ret['noscript_content'].'</div>';
            $status_code = $ret['status'];
        } else {
            $stylaDiv = '<div data-styla-client="'.$this->_username.'"></div>';
        }

        $this->View()->assign('sContent', $stylaDiv . "\r\n" . $js_include);
        $this->View()->assign('sCustomPage', $custom_page);
        $this->View()->assign('feed_type', $type);

        if ($status_code) {
            $this->Response()->setHttpResponseCode($status_code);
        }
    }

    public function indexAction(){

    }

}
