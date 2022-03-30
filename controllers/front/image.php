<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class PhoeniciamobileImageModuleFrontController extends ModuleFrontController {
    public $ajax;
    public function __construct()
    {
        parent::__construct();
    }

    public function postProcess() {
        $this->ajax = 1;
        $id_product = Tools::getValue('pi', null);
        $id_image = Tools::getValue('ii', null);
        if (!$id_product && !$id_image) return '';
        $id_product = (int)$id_product;
        $id_image = (int)$id_image;
        $p = new Product($id_product);
		if (is_array($p->link_rewrite)) {
			// With lang id, passed 1 value
			$link = $p->link_rewrite[1];
		} else {
			$link = $p->link_rewrite;
		}
        //$context = Context::getContext();
        echo $this->context->link->getImageLink($link, $id_image, "home_default");
        die();
    }
}