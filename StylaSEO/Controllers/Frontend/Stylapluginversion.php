<?php

class Shopware_Controllers_Frontend_Stylapluginversion extends Shopware_Controllers_Frontend_Checkout {

    public function indexAction(){
        $Bootstrap = new Shopware_Plugins_Frontend_StylaSEO_Bootstrap('StylaSEO');
        $this->View()->assign('versionNumber', $Bootstrap->getVersion());
    }

}

?>
