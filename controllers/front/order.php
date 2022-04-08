<?php

use MrShan0\CryptoLib\CryptoLib;
use MrShan0\CryptoLib\Exceptions\UnableToDecrypt;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PhoeniciamobileOrderModuleFrontController extends ModuleFrontController
{
    protected $payment_module = "epayment";

    public function __construct()
    {
        parent::__construct();
    }

    public function postProcess()
    {
        $ic = Tools::getValue('ic');
        $iv = Tools::getValue('iv');
        $crypted_customer = Tools::getValue('u');

        try {
            if (!$ic || !$iv || !$crypted_customer) {
                throw new Exception("Information incomplete. Veuillez contatez l'administrateur");
            }

            /**
             * Phase one decrypt data base64
             */
            $ic = base64_decode($ic);
            $iv = base64_decode($iv);
            $crypted_customer = base64_decode($crypted_customer);

            /**
             * Phase two decrypt with cryptolib
             */
            $encryption = new CryptoLib();
            $encryption_key = Configuration::get('CRYPTO_KEY');

            $customer_arg = $encryption->decrypt($crypted_customer, $encryption_key, $iv);
            $cart_id = $encryption->decrypt($ic, $encryption_key, $iv);
            $customer_data = explode(':', $customer_arg);
            $customer_email = $customer_data[0];
            $customer_pwd = $customer_data[1];
            $sql = new DbQuery();
            $sql->select('*');
            $sql->from('customer', 'c');
            $sql->where("c.passwd = '{$customer_pwd}'");
            $sql->where("c.email = '{$customer_email}'");
            $result = Db::getInstance()->executeS($sql);
            // Vérifier l'utilisateur est valide (auto-authentification)
            if (empty($result)) {
                throw new Exception("Signature url invalide");
            }
            $customer = reset($result);

            // Add cart un cookie
            $cart = new Cart((int)$cart_id);
            if (!$cart->id) {
                throw new Exception("Une erreur s'est produite. Panier introuvable");
            }

            // Authentification customer
            $id_customer = (int) $customer['id_customer'];
            $customer = new Customer((int) $id_customer);
            $context = Context::getContext();
            if (Validate::isLoadedObject($customer)) {
                $context->updateCustomer($customer);
            }
            //$order_total = $cart->getOrderTotal(true, Cart::BOTH);
            Context::getContext()->cart = $cart;
            CartRule::autoAddToCart(Context::getContext());
            Context::getContext()->cookie->id_cart = $cart->id;

            // $epayment = Module::getInstanceByName($this->payment_module);
            // $response = $epayment->validateOrder($cart->id, $order_status, $order_total, $card_label, null, array(), (int)$currency_id, false, $secure_key);
            //$url = "index.php?fc=module&module=epayment&controller=redirect&a=r&method=6";

            // Get cart total
            $total = $cart->getOrderTotal(true, Cart::BOTH);
            if ((int)$total == 0) {
                throw new Exception("Le montant total de votre panier est égale à zero (0)");
            }

            Tools::redirect(Context::getContext()->link->getModuleLink($this->payment_module, 'redirect', [
                "a" => "r",
                "method" => 6
            ]));

        } catch(UnableToDecrypt | Exception $e) {
            echo $e->getMessage();
        }
        die();
    }



}
