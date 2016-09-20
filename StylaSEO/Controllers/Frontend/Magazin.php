<?php

class Shopware_Controllers_Frontend_Magazin extends Enlight_Controller_Action {

    protected $_allowed_actions = array('tag','story','user','report');
    protected $_username        = null;
    protected $_source_url      = null;
    protected $_snippet_url     = null;
    protected $_feed_params     = array();
    protected $_base_dir     	= null;

    public function preDispatch(Enlight_Event_EventArgs $args){

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

        if(!$this->_username)
            die('No username set for Styla SEO plugin'); // TODO maybe something better than die, but then again since it's a required field this should never really be empty

    }

    public function postDispatch(){

        $type = $this->_feed_params['type'];
        $js_include = StylaUtils::getJsEmbedCode($this->_username, $this->_snippet_url);

        $ret = null;

        if($type != 'search'){// for now at least we don't need any metadata coming back for search results
            $ret = StylaUtils::getRemoteContent($this->_username, $this->_feed_params, $this->_source_url);
        }

        $custom_page = $this->View()->getAssign('sCustomPage');
        if($ret){
            $custom_page['head_content'] = $ret['head_content'];

            $this->View()->assign('sContent', $ret['noscript_content']."\r\n".$js_include."\r\n".'<div id="stylaMagazine"></div>');
        }

        $this->View()->assign('sCustomPage', $custom_page);
        $this->View()->assign('feed_type', $type);
    }

    public function indexAction(){
    	$this->_feed_params = array('type' => 'magazine', 'route' => '/');
    }

    public function tagAction(){
        $tagname = StylaUtils::getParamFromUrl('tag');
        if(!$tagname){
            $this->redirect('/');
        }
        $this->_feed_params = array('type' => 'tag', 'tagname' => $tagname);
    }

    public function storyAction(){
        $storyname = StylaUtils::getParamFromUrl('story');
        if(!$storyname){
            $this->redirect('/');
        }
        $this->_feed_params = array('type' => 'story', 'storyname' => $storyname, 'route' => 'story/'.$storyname);
    }

    public function userAction(){
        $username = StylaUtils::getParamFromUrl('user');
        if(!$username){
            $this->redirect('/');
        }
        $this->_feed_params = array('type' => 'user', 'username' => $username, 'route' => 'user/'.$username);
    }

    public function searchAction(){
        $searchterm = StylaUtils::getParamFromUrl('search');
        if(!$searchterm){
            $this->redirect('/');
        }
        $this->_feed_params = array('type' => 'search', 'searchterm' => $searchterm, 'route' => 'search/'.$searchterm);
    }

    public function __call($name, $value = null) {
        $url = trim(strtok($_SERVER["REQUEST_URI"],'?'), '/');
        $arr = explode('/', $url);
        if($arr[0] == $this->_base_dir && !empty($arr[1]) && !in_array($arr[1], array('search','story','user','tag','category','magazine',$this->_username))){
            StylaCurl::call($this->_source_url, array(), array('keyword' => $arr[1]));
        }
    }

}
