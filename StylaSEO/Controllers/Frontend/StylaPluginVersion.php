<?php

class Shopware_Controllers_Frontend_StylaPluginVersion extends Shopware_Controllers_Frontend_Checkout {

    public function indexAction(){
        $Bootstrap = new Shopware_Plugins_Frontend_StylaSEO_Bootstrap;
        $this->View()->assign('versionNumber', $Bootstrap->getVersion());
    }

}

?>