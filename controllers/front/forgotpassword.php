<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class PhoeniciamobileForgotpasswordModuleFrontController extends ModuleFrontController {
    public $ajax;
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * If the email address is not found in the database, it will send an email to the email address
     * with a link to reset the password.
     * If the email address is found in the database, it will send an email to the email address with a
     * link to reset the password.
     */
    public function postProcess() {
        $this->ajax = 1;
        $errors = [];
        $success = [];
        $email = Tools::getValue('email', null);
        if (!$email) {
            echo Tools::jsonEncode(['success' => false, "message" => "Adresse email introuvable"]);
            die();
        }
        $customer = new Customer();
        $customer->getByEmail($email);
        if (null === $customer->email) {
            $customer->email = $email;
        }
        if (!Validate::isLoadedObject($customer)) {
            $success[] = "If this email address has been registered in our shop, you will receive a link to reset your password at {$customer->email}.";
        } elseif (!$customer->active) {
            $errors[] = 'You cannot regenerate the password for this account.';
        } elseif ((strtotime($customer->last_passwd_gen . '+' . ($minTime = (int) Configuration::get('PS_PASSWD_TIME_FRONT')) . ' minutes') - time()) > 0) {
            $errors[] = 'You can regenerate your password only every %d minute(s)';
        } else {
            if (!$customer->hasRecentResetPasswordToken()) {
                $customer->stampResetPasswordToken();
                $customer->update();
            }

            $mailParams = [
                '{email}' => $customer->email,
                '{lastname}' => $customer->lastname,
                '{firstname}' => $customer->firstname,
                '{url}' => $this->context->link->getPageLink('password', true, null, 'token=' . $customer->secure_key . '&id_customer=' . (int) $customer->id . '&reset_token=' . $customer->reset_password_token),
            ];

            if (
                Mail::Send(
                    $this->context->language->id,
                    'password_query',
                    'Password query confirmation',
                    $mailParams,
                    $customer->email,
                    $customer->firstname . ' ' . $customer->lastname
                )
            ) {
                $success[] = "If this email address has been registered in our shop, you will receive a link to reset your password at {$customer->email}.";
            } else {
                $errors[] = 'An error occurred while sending the email.';
            }
        }

        if (!empty($errors)) {
            echo Tools::jsonEncode(['success' => false, 'message' => implode(".", $errors)]);
        } else {
            echo Tools::jsonEncode(['success' => true, 'message' => implode(".", $success)]);
        }
        die();
    }
}