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
        $this->registerController('Frontend', 'Stylaapi');
        $this->registerController('Frontend', 'Magazin');
        $this->registerController('Frontend', 'Stylapluginversion');
        $this->registerController('Frontend', 'Stylaseoupdate');
        $this->createDbTables();
        return true;
    }

    protected function createConfigForm(){
        $form = $this->Form();

        $form->setElement('text', 'styla_username', array(
            'label' => 'Styla Magazine ID',
            'required' => true,
            'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
        ));
        $form->setElement('text', 'styla_seo_url', array(
            'label' => 'Styla SEO Server URL',
            'required' => true,
            'value' => 'http://seoapi.styla.com',
            'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
        ));
        $form->setElement('text', 'styla_api_url', array(
            'label' => 'Styla API Server URL',
            'required' => true,
            'value' => 'http://client-scripts.styla.com',
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
            'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
        ));
        $form->setElement('text', 'styla_modular_content_api', array(
            'label' => 'Styla Modular Content Api',
            'required' => true,
            'value' => 'http://live.styla.com',
            'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
        ));
    }

    protected function registerEvents(){
        $this->subscribeEvent('Enlight_Controller_Front_PreDispatch', 'onPreDispatch');
        $this->subscribeEvent('Enlight_Controller_Front_PostDispatch', 'onPostDispatch');
        $this->subscribeEvent('Enlight_Controller_Action_PostDispatchSecure_Frontend_Detail', 'onGetControllerPathDetail');

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

        if (strpos($request->getRequestUri(), $this->_magazin_basedir) !== false){
		    $controller	= 'magazin';
        } else if(strpos($request->getRequestUri(), '/styla-plugin-version') !== false) {
		    $controller	= 'stylapluginversion';
        } else if(strpos($request->getRequestUri(), '/stylaapi/update') !== false){
            $controller	= 'stylaseoupdate';
        } else if(strpos($request->getRequestUri(), '/stylaapi') !== false) {
            $controller = 'stylaapi';
        } else {
            return;
        }

        if(!$this->_styla_username){
            die('No Styla Magazine ID set in Plugin settings');
        }

        require_once $this->Path() . 'Components/Styla/Utils.php';
        require_once $this->Path() . 'Components/Styla/Curl.php';

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

        $this->registerTemplateDir();

        if(!$request->isDispatched()||$response->isException()||$request->getModuleName()!='frontend') {
            return;
        }
    }

    protected function registerTemplateDir(){
        $this->Application()->Template()->addTemplateDir(
            $this->Path() . 'Views/'
        );
    }

    public function onGetControllerPathDetail(Enlight_Event_EventArgs $args){
        if ($this->Config()->get('styla_modular_content_username')) {
            $controller = $args->getSubject();
            $request = $controller->Request();
            $view = $controller->View();

            if ($request->getControllerName() == 'detail'){
                $article = $view->getAssign('sArticle');
                $view->assign('styla_seo_content',  $this->stylaLoadContent('story/' . $article['articleID']));
            }
        }
    }

    public function stylaLoadContent($path){
        $shopContext = $this->get('shopware_storefront.context_service')->getShopContext();
        $lang = $shopContext->getShop()->getLocale()->getLocale();
        $query = "SELECT * FROM s_styla_seo_content WHERE locale = '" . $lang . "' AND path = '" . $path . "'";
        $queryResult = Shopware()->Db()->fetchAll($query);
        $html = "";
        if (count($queryResult) > 0){
            $html = html_entity_decode($queryResult[0]['content']);
        }
        return $html;
    }


}
