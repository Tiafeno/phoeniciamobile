<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class PhoeniciamobilePaymentModuleFrontController extends ModuleFrontController {
    public $auth = false;

    /** @var bool */
    public $ajax;
    public $payments = [];
    protected $salt = "WF0nn4LyAdcbQC1vcgE0gpm0gCxlUmAr";

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * It gets all the payment modules installed on the shop, then it gets all the active cards from
     * the database, then it adds the module name and the image path to the card, then it returns the
     * cards as a json object.
     */
    public function display()
    {
        $this->ajax = 1;
        $modules = PaymentModule::getInstalledPaymentModules();
        foreach ($modules as $module) {
            if ($module['name'] == "epayment") {
                $sql = new DbQuery();
                $sql->select('*');
                $sql->from('paybox_card', 'card');
                $sql->where('card.active = 1');
                $results = Db::getInstance()->executeS($sql);

                foreach($results as $result) {
                    $result['module'] = $module['name'];
                    $result['image'] = _PS_BASE_URL_ .__PS_BASE_URI__  ."modules/epayment/img/{$result['type_card']}.png";
                    $this->payments[] = $result;
                }
            } else {
                // Seulement le paiement par vodafone est autorisée
            }
        }
        echo Tools::jsonEncode($this->payments);
		die();
    }


}