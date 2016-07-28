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
        $this->_source_url  = $config->get('styla_source_url');
        $this->_snippet_url = $config->get('styla_js_url');

	    $this->_base_dir    = $config->get('styla_basedir');
    /*// to submit search
	$url = trim($_SERVER['REQUEST_URI'], '/');
	$arr = explode('/', $url);
	if($arr[0] == $this->_base_dir && !empty($arr[1]) && !in_array($arr[1], array('search','story','user','tag','category','magazine',$this->_username))){
		$this->report($arr[1]);
	}*/

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
            $custom_page['meta_description'] = $ret['meta']['description'];
            $custom_page['page_title'] = $ret['page_title'];
            $custom_page['canonical_link'] = $ret['canonical_link'];

            if($type == 'user' || $type == 'magazine' || $type == 'story'){
                $custom_page['meta_og_type'] = $ret['meta']['og']['type'];
                $custom_page['meta_og_title'] = $ret['meta']['og']['title'];
                $custom_page['meta_og_image'] = $ret['meta']['og']['image'];
                $custom_page['meta_og_url'] = $ret['meta']['og']['url'];
                $custom_page['meta_fb_app_id'] = $ret['meta']['fb_app_id'];
                $custom_page['author'] = $ret['author'];

        		if(!empty($ret['meta']['og']['image'])){
        			$meta = (array) new SimpleXMLElement($ret['meta']['og']['image']);
        			$attribs = current($meta);
        			list($width, $height) = getimagesize($attribs['content']);
        			$custom_page['meta_og_image_width'] = $width;
        			$custom_page['meta_og_image_height'] = $height;
        		}
            }

            if($type == 'story'){
                $custom_page['meta_keywords'] = $ret['meta']['keywords'];
            }

            $this->View()->assign('sContent', '<noscript>'.$ret['noscript_content'].'</noscript>'."\r\n".$js_include."\r\n".'<div id="stylaMagazine"></div>');
        }

        $this->View()->assign('sCustomPage', $custom_page);
        $this->View()->assign('feed_type', $type);
    }

    public function indexAction(){
    	$this->_feed_params = array('type' => 'magazine');
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
        $this->_feed_params = array('type' => 'story', 'storyname' => $storyname);
    }

    public function userAction(){
        $username = StylaUtils::getParamFromUrl('user');
        if(!$username){
            $this->redirect('/');
        }
        $this->_feed_params = array('type' => 'user', 'username' => $username);

    }

    public function searchAction(){
        $searchterm = StylaUtils::getParamFromUrl('search');
        if(!$searchterm){
            $this->redirect('/');
        }
        $this->_feed_params = array('type' => 'search', 'searchterm' => $searchterm);

    }

	/*public function report($searchKeyword=null){
		StylaCurl::call($this->_source_url, array(), array('keyword' => $searchKeyword));
		$this->redirect('/'.$this->_base_dir);
	}*/


    public function __call($name, $value = null) {
        $url = trim(strtok($_SERVER["REQUEST_URI"],'?'), '/');
        $arr = explode('/', $url);
        if($arr[0] == $this->_base_dir && !empty($arr[1]) && !in_array($arr[1], array('search','story','user','tag','category','magazine',$this->_username))){
            StylaCurl::call($this->_source_url, array(), array('keyword' => $arr[1]));
        }
    }

}
