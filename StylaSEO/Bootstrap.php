<?php
class Shopware_Plugins_Frontend_StylaSEO_Bootstrap extends Shopware_Components_Plugin_Bootstrap {

    protected $_styla_username, $_magazin_basedir;

    public function getCapabilities(){
        return array(
            'install' => true,
            'update' => true,
            'enable' => true
        );
    }

    public function getLabel()
    {
        return 'Styla Magazine Plugin';
    }

    public function getVersion()
    {
        return '5.3.0';
    }

    public function getInfo()
    {
        return array(
            'version' => $this->getVersion(),
            'label' => $this->getLabel(),
            'author' => 'Styla GmbH',
            'supplier' => 'Styla GmbH',
            'description' => 'Generates magazine wildcart route and product api',
            'copyright' => '',
            'support' => 'Styla GmbH',
            'link' => 'http://www.styla.com'
        );
    }

    public function createDbTables(){
        $tableNames = array("s_styla_seo_content");
        $schemaManager = Shopware()->Container()->get('models')->getConnection()->getSchemaManager();
        if (!$schemaManager->tablesExist($tableNames)) {
            Shopware()->Db()->query("CREATE TABLE s_styla_seo_content (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `path` varchar(255) DEFAULT NULL,
                `locale` varchar(10) DEFAULT NULL,
                `content` text,
                `time_updated` datetime DEFAULT NULL,
                `time_created` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `path` (`path`,`locale`)
            )");
        }
    }

    public function install(){
        $this->registerEvents();
        $this->createConfigForm();
        $this->registerController('Frontend', 'stylaApi');
        $this->registerController('Frontend', 'magazin');
        $this->registerController('Frontend', 'stylapluginversion');
        $this->registerController('Frontend', 'stylaseoupdate');
        $this->createDbTables();
        return true;
    }

    protected function createConfigForm(){
        $form = $this->Form();

        $form->setElement('text', 'styla_username', array(
            'label' => 'Styla Magazine ID',
            'required' => true,
            'value' => 'ci-shopware5', // TODO: change this to live api
            'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
        ));
        $form->setElement('text', 'styla_seo_url', array(
            'label' => 'Styla SEO Server URL',
            'required' => true,
            'value' => 'http://seoapi.stage.eu.magalog.net', // TODO: change this to live api
            'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
        ));
        $form->setElement('text', 'styla_api_url', array(
            'label' => 'Styla API Server URL',
            'required' => true,
            'value' => 'http://client-scripts.stage.eu.magalog.net', // TODO: change this to live api
            'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
        ));
        $form->setElement('text', 'styla_basedir', array(
            'label' => 'Styla Base Folder',
            'required' => true,
            'value' => 'magazine',
            'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
        ));
        $form->setElement('text', 'styla_modular_content_username', array(
            'label' => 'Styla Modular Content ID',
            'required' => true,
            'value' => 'ci-shopware5', // TODO: change this to live api
            'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
        ));
        $form->setElement('text', 'styla_modular_content_api', array(
            'label' => 'Styla Modular Content Api',
            'required' => true,
            'value' => 'http://backend-storyapi.stage.eu.magalog.net/', // TODO: change this to live api
            'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
        ));
    }

    protected function registerEvents(){
        $this->subscribeEvent('Enlight_Controller_Front_PreDispatch', 'onPreDispatch');
        $this->subscribeEvent('Enlight_Controller_Front_PostDispatch', 'onPostDispatch');
        $this->subscribeEvent('Enlight_Controller_Action_PostDispatchSecure_Frontend', 'onGetControllerPathDetail');
        $this->subscribeEvent('Enlight_Controller_Dispatcher_ControllerPath_Frontend_Magazin', 'onGetControllerPathFrontend');
        $this->subscribeEvent('Enlight_Controller_Dispatcher_ControllerPath_Frontend_StylaApi', 'onGetControllerPathFrontend');
        $this->subscribeEvent('Enlight_Controller_Dispatcher_ControllerPath_Frontend_StylaPluginVersion', 'onGetControllerPathFrontend');
        $this->subscribeEvent('Enlight_Controller_Dispatcher_ControllerPath_Frontend_StylaSeoUpdate', 'onGetControllerPathFrontend');

        return array(
            'success' => true,
            'invalidateCache' => array('backend')
        );
    }

    public function onPreDispatch(Enlight_Controller_EventArgs $args){
        $request  = $args->getRequest();
        $url = strtok($request->getRequestUri(),'?');

        $this->_styla_username = $this->Config()->get('styla_username');
        $this->_magazin_basedir = $this->Config()->get('styla_basedir', 'magazine');

        if ($url == '/'.$this->_magazin_basedir || strpos($url, '/'.$this->_magazin_basedir.'/') !== false){
		    $controller	= 'magazin';
        } else if( ($request->getRequestUri() == '/styla-plugin-version' || strpos($request->getRequestUri(), '/styla-plugin-version/') !== false) ) {
		    $controller	= 'stylapluginversion';
        } else if( ($request->getRequestUri() == '/stylaapi/update' || strpos($request->getRequestUri(), '/stylaapi/update/') !== false) ) {
            $controller	= 'stylaseoupdate';
        } else if( ($request->getRequestUri() == '/stylaapi' || strpos($request->getRequestUri(), '/stylaapi/') !== false) ) {
            $controller = 'stylaapi';
        } else {
            return;
        }

        if(!$this->_styla_username){
            die('No Styla Magazine ID set in Plugin settings');
        }

        require_once $this->Path() . 'Components/Styla/Utils.php';
        require_once $this->Path() . 'Components/Styla/Curl.php';

        $this->registerTemplateDir();
        $request->setControllerName($controller);
        if ($controller === 'stylaapi') {
            $request->setActionName(StylaUtils::getActionFromUrl());
        } else {
            $request->setActionName('index');
        }
    }

    public function onPostDispatch(Enlight_Event_EventArgs $args){
        $action = $args->getSubject();
        $request = $action->Request();
        $response = $action->Response();

        if(!$request->isDispatched()||$response->isException()||$request->getModuleName()!='frontend') {
            return;
        }
    }

    public function onGetControllerPathFrontend(Enlight_Event_EventArgs $args){
        $request = $args->getRequest();
        $url = strtok($request->getRequestUri(),'?');
        if ($url == '/'.$this->_magazin_basedir || strpos($url, '/'.$this->_magazin_basedir.'/') !== false){
            return $this->Path() . 'Controllers/Frontend/Magazin.php';
        }
	    else if ($url == '/styla-plugin-version' || strpos($url, '/styla-plugin-version/') !== false) {
            return $this->Path() . 'Controllers/Frontend/StylaPluginVersion.php';
        }
        else if ($url == '/stylaapi/update' || strpos($url, '/stylaapi/update') !== false) {
            return $this->Path() . 'Controllers/Frontend/StylaSeoUpdate.php';
        }
        else {
            return $this->Path() . 'Controllers/Frontend/StylaApi.php';
        }
    }

    protected function registerTemplateDir(){
        $this->Application()->Template()->addTemplateDir(
            $this->Path() . 'Views/', 'styla'
        );
    }

    public function onGetControllerPathDetail(Enlight_Event_EventArgs $args){
        print_r($args->getSubject()->View());
        $args->getSubject()->View()->assign('styla_content', $this->stylaLoadContent('pie')); // TODO: make this dynamic
    }

    public function onGetControllerPathDetail2(Enlight_Event_EventArgs $args){
        $template = \Enlight_Class::Instance('Enlight_Template_Manager');
        $template->registerPlugin('function', 'stylaload', [&$this, 'stylaLoadContent']);
    }

    public function stylaLoadContent($productId){
        $shopContext = $this->get('shopware_storefront.context_service')->getShopContext();
        $lang = $shopContext->getShop()->getLocale()->getLocale();
        $path = 'story/' . $productId;
        $query = "SELECT * FROM s_styla_seo_content WHERE locale = '" . $lang . "' AND path = '" . $path . "'";
        $queryResult = Shopware()->Db()->fetchAll($query);
        $html = "";
        if (count($queryResult) > 0){
            $html = html_entity_decode($queryResult[0]['content']);
        }
        return $html;
    }


}
