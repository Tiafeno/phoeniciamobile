<?php
if (!defined('_PS_VERSION_')) {
    exit;
}


// Needed for installing process 
require_once __DIR__ . '/vendor/autoload.php';

class Phoeniciamobile extends Module {
    public function __construct() {
        $this->name = 'phoeniciamobile';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Tiafeno Finel';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Phoenicia API MOBILE');
        $this->description = $this->l('Application mobile de Phoenicia');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

    }

    public function install()
    {
        Configuration::updateValue('CRYPTO_KEY', 'WF0nn4LyAdcbQC1vcgE0gpm0gCxlUmAr');
        return parent::install();
    }

    public function uninstall()
    {
        Configuration::deleteByName('CRYPTO_KEY');
        if (!parent::uninstall())
            return false;
        return true;
    }
}
