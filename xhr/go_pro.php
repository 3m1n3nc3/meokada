<?php
require_once('sys/import3p/PayPal/vendor/autoload.php');
use PayPal\Api\Payer;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Details;
use PayPal\Api\Amount;
use PayPal\Api\Transaction;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;

$paypal = new \PayPal\Rest\ApiContext(
  new \PayPal\Auth\OAuthTokenCredential(
    $config['paypal_id'],
    $config['paypal_secret']
  )
);
$paypal->setConfig(
    array(
      'mode' => $config['paypal_mode']
    )
);

$uObj      = new User;

if (!is_array($_POST['type']) && $p_t = json_decode($_POST['type'])) {
    $_POST['type'] = o2array($p_t);
}
$post_type = (is_array($_POST['type']) ? key($_POST['type']) : $_POST['type']); 

if ($post_type == 'community') {
    $community  = $uObj->listCommunityPlans($_POST['type'][$post_type]); 
    $com_is_pro = 1;
    $com_is_ver = 1;
}

if ($action == 'get_paypal_link' && IS_LOGGED && !empty($config['paypal_id']) && !empty($config['paypal_secret'])) {
    $type = 'pro';
    $sum  = $config['pro_price'];
    $dec  = "Upgrade to pro";
    if (!empty($_POST['type']) && $_POST['type'] == 'wallet' && !empty($_POST['amount']) && is_numeric($_POST['amount']) && $_POST['amount'] > 0) {
        $sum  = Generic::secure($_POST['amount']);
        $type = 'wallet';
        $dec  = "Wallet top up";
    }
    if (!empty($_POST['type']) && $post_type == 'community' && !empty($_POST['amount']) && is_numeric($_POST['amount']) && $_POST['amount'] > 0) {
        $sum  = Generic::secure($_POST['amount']);
        $type = 'community'; 
        $dec  = 'Join ' . $community['title'] . ' Community';
    }
    
    $payer = new Payer();
    $payer->setPaymentMethod('paypal');
    $item = new Item();
    $item->setName($dec)->setQuantity(1)->setPrice($sum)->setCurrency($config['currency']);
    $itemList = new ItemList();
    $itemList->setItems(array(
        $item
    ));
    $details = new Details();
    $details->setSubtotal($sum);
    $amount = new Amount();
    $amount->setCurrency($config['currency'])->setTotal($sum)->setDetails($details);
    $transaction = new Transaction();
    $transaction->setAmount($amount)->setItemList($itemList)->setDescription($dec)->setInvoiceNumber(time());
    $redirectUrls = new RedirectUrls();
    if ($type == 'pro') {
        $redirectUrls->setReturnUrl($config['site_url'] . "/aj/go_pro/get_paid&success=1")->setCancelUrl($config['site_url']);
    }
    elseif ($type == 'community') {
        $redirectUrls->setReturnUrl($config['site_url'] . "/aj/go_pro/get_community&success=1")->setCancelUrl($config['site_url']);
    }
    elseif ($type == 'standard') {
        $redirectUrls->setReturnUrl($config['site_url'] . "/aj/go_pro/get_standard&success=1")->setCancelUrl($config['site_url']);
    }
    elseif ($type == 'wallet') {
        $redirectUrls->setReturnUrl($config['site_url'] . "/aj/go_pro/wallet_top_up&success=1&amount=".$sum)->setCancelUrl($config['site_url']);
    }
    $payment = new Payment();
    $payment->setIntent('sale')->setPayer($payer)->setRedirectUrls($redirectUrls)->setTransactions(array(
        $transaction
    ));
    try {
        $payment->create($paypal);
    }
    catch (Exception $e) {
        $data = array(
            'status' => 400,
            'message' => json_decode($e->getData())
        );
        if (empty($data['message'])) {
            $data['message'] = json_decode($e->getCode());
        }
    }
    if (empty($data['message'])) {
        $data = array(
            'status' => 200,
            'url' => $payment->getApprovalLink()
        );
    }
    
}


if ($action == 'get_paid' && IS_LOGGED && !empty($config['paypal_id']) && !empty($config['paypal_secret']) && $_GET['success'] == 1 && !empty($_GET['paymentId']) && !empty($_GET['PayerID'])) {
    $paymentId = $_GET['paymentId'];
    $PayerID = $_GET['PayerID'];
    $payment = Payment::get($paymentId, $paypal);
    $execute = new PaymentExecution();
    $execute->setPayerId($PayerID);
    $error = '';
    try {
        $result = $payment->execute($execute, $paypal);
    }
    catch (Exception $e) {
        $error = json_decode($e->getData(), true);
    }

    if (empty($error)) {
        $update = $user->updateStatic($me['user_id'],array('is_pro' => 1,'verified' => 1));
        $amount = $config['pro_price'];
        $date   = time();

        $db->insert(T_PAYMENTS,array('user_id' => $me['user_id'],
                                      'amount' => $amount,
                                      'type' => 'pro_member',
                                      'date' => $date));

        $db->insert(T_TRANSACTIONS,array('user_id' => $me['user_id'],
                                      'amount' => $amount,
                                      'type' => 'pro_member',
                                      'time' => $date));

        $uObj->payUpgradeCommissions(null, 'pro');

        header("Location: " . $config['site_url'] . "/upgraded");
        exit();
    }
    else{
        header("Location: " . $config['site_url'] . "/oops");
        exit();
    }
}

if ($action == 'get_community' && IS_LOGGED && !empty($config['paypal_id']) && !empty($config['paypal_secret']) && $_GET['success'] == 1 && !empty($_GET['paymentId']) && !empty($_GET['PayerID'])) {
    $paymentId = $_GET['paymentId'];
    $PayerID = $_GET['PayerID'];
    $payment = Payment::get($paymentId, $paypal);
    $execute = new PaymentExecution();
    $execute->setPayerId($PayerID);
    $error = '';
    try {
        $result = $payment->execute($execute, $paypal);
    }
    catch (Exception $e) {
        $error = json_decode($e->getData(), true);
    }

    if (empty($error)) {   
        $update = $user->updateStatic($me['user_id'],array('is_pro' => $com_is_pro, 'verified' => $com_is_ver, 'community' => $community['id']));
        $amount = $community['price'];
        $date   = time();

        $db->insert(T_PAYMENTS,array(
            'user_id' => $me['user_id'],
            'amount' => $amount,
            'type' => 'community_' . $community['id'],
            'date' => $date));

        $db->insert(T_TRANSACTIONS,array(
            'user_id' => $me['user_id'],
            'amount' => $amount,
            'type' => 'community_' . $community['id'],
            'time' => $date)); 

        $uObj->payUpgradeCommissions(null, 'community');

        header("Location: " . $config['site_url'] . "/upgraded?type=community_member&community=" . $community['id']);
        exit();
    }
    else{
        header("Location: " . $config['site_url'] . "/oops");
        exit();
    }
}

if ($action == 'get_standard' && IS_LOGGED && !empty($config['paypal_id']) && !empty($config['paypal_secret']) && $_GET['success'] == 1 && !empty($_GET['paymentId']) && !empty($_GET['PayerID'])) {
    $paymentId = $_GET['paymentId'];
    $PayerID = $_GET['PayerID'];
    $payment = Payment::get($paymentId, $paypal);
    $execute = new PaymentExecution();
    $execute->setPayerId($PayerID);
    $error = '';
    try {
        $result = $payment->execute($execute, $paypal);
    }
    catch (Exception $e) {
        $error = json_decode($e->getData(), true);
    }

    if (empty($error)) {
        $update = $user->updateStatic($me['user_id'],array('is_standard' => 1));
        $amount = $config['standard_price'];
        $date   = time();

        $db->insert(T_PAYMENTS,array('user_id' => $me['user_id'],
                                      'amount' => $amount,
                                      'type' => 'standard_member',
                                      'date' => $date));

        $db->insert(T_TRANSACTIONS,array('user_id' => $me['user_id'],
                                      'amount' => $amount,
                                      'type' => 'standard_member',
                                      'time' => $date));

        $uObj->payUpgradeCommissions(null, 'standard');

        header("Location: " . $config['site_url'] . "/upgraded?type=standard");
        exit();
    }
    else{
        header("Location: " . $config['site_url'] . "/oops");
        exit();
    }
}

if ($action == 'wallet_top_up' && IS_LOGGED && !empty($config['paypal_id']) && !empty($config['paypal_secret']) && $_GET['success'] == 1 && !empty($_GET['paymentId']) && !empty($_GET['PayerID']) && !empty($_GET['amount'])) {
    $paymentId = $_GET['paymentId'];
    $PayerID = $_GET['PayerID'];
    $payment = Payment::get($paymentId, $paypal);
    $execute = new PaymentExecution();
    $execute->setPayerId($PayerID);
    $error = '';
    try {
        $result = $payment->execute($execute, $paypal);
    }
    catch (Exception $e) {
        $error = json_decode($e->getData(), true);
    }

    if (empty($error)) {
        $wallet = $me['wallet'] + $_GET['amount'];
        $update = $user->updateStatic($me['user_id'],array('wallet' => $wallet));

        $db->insert(T_TRANSACTIONS,array('user_id' => $me['user_id'],
                                      'amount' => Generic::secure($_GET['amount']),
                                      'type' => 'Advertise',
                                      'time' => time()));

        header("Location: " . $config['site_url'] . "/ads/wallet");
        exit();
    }
    else{
        header("Location: " . $config['site_url'] . "/oops");
        exit();
    }
}

if ($action == 'stripe_payment' && IS_LOGGED && $config['credit_card'] == 'on' && !empty($config['stripe_id']) && !empty($config['stripe_id'])) { 

    require_once('sys/import3p/stripe-php-3.20.0/vendor/autoload.php');
    $stripe = array(
      "secret_key"      =>  $config['stripe_secret'],
      "publishable_key" =>  $config['stripe_id']
    );

    \Stripe\Stripe::setApiKey($stripe['secret_key']);
    $token = $_POST['stripeToken'];
 
    $post_type = (is_array($_POST['type']) ? key($_POST['type']) : $_POST['type']);

    if (!empty($_POST['type']) && $_POST['type'] == 'pro' && !empty($_POST['amount'])) {
        if ($config['pro_price'].'00' == $_POST['amount']) {
            try {
                $customer = \Stripe\Customer::create(array(
                    'source' => $token
                ));
                $charge   = \Stripe\Charge::create(array(
                    'customer' => $customer->id,
                    'amount' => $config['pro_price'].'00',
                    'currency' => 'usd'
                ));
                if ($charge) {
                    $update = $user->updateStatic($me['user_id'],array('is_pro' => 1,'verified' => 1));
                    $amount = $config['pro_price'];
                    $date   = time();

                    $db->insert(T_PAYMENTS,array('user_id' => $me['user_id'],
                                              'amount' => $amount,
                                              'type' => 'pro_member',
                                              'date' => $date));

                    $db->insert(T_TRANSACTIONS,array('user_id' => $me['user_id'],
                                      'amount' => $amount,
                                      'type' => 'pro_member',
                                      'time' => $date));
        
                    $uObj->payUpgradeCommissions(null, 'pro');
                    
                    $data = array(
                        'status' => 200,
                        'url' => $config['site_url'] . "/upgraded"
                    );
                }
            }
            catch (Exception $e) {
                $data = array(
                    'status' => 400,
                    'error' => $e->getMessage()
                );
            }
        }
    }

    if (!empty($_POST['type']) && $post_type == 'community' && !empty($_POST['amount'])) { 

        if ($community['price'].'00' == $_POST['amount']) {
            try {
                $customer = \Stripe\Customer::create(array(
                    'source' => $token
                ));
                $charge   = \Stripe\Charge::create(array(
                    'customer' => $customer->id,
                    'amount' => $community['price'].'00',
                    'currency' => 'usd'
                ));
                if ($charge) {
                    $update = $user->updateStatic($me['user_id'],array('is_pro' => $com_is_pro, 'verified' => $com_is_ver, 'community' => $community['id']));
                    $amount = $community['price'];
                    $date   = time();

                    $db->insert(T_PAYMENTS,array('user_id' => $me['user_id'],
                                              'amount' => $amount,
                                              'type' => 'community_' . $community['id'],
                                              'date' => $date));

                    $db->insert(T_TRANSACTIONS,array('user_id' => $me['user_id'],
                                      'amount' => $amount,
                                      'type' => 'community_' .$community['id'],
                                      'time' => $date));
        
                    $uObj->payUpgradeCommissions(null, 'community');

                    $data = array(
                        'status' => 200,
                        'url' => $config['site_url'] . "/upgraded?type=community_member&community=" . $community['id']
                    );
                }
            }
            catch (Exception $e) {
                $data = array(
                    'status' => 400,
                    'error' => $e->getMessage()
                );
            }
        }
    }

    if (!empty($_POST['type']) && $_POST['type'] == 'standard' && !empty($_POST['amount'])) {
        if ($config['standard_price'].'00' == $_POST['amount']) {
            try {
                $customer = \Stripe\Customer::create(array(
                    'source' => $token
                ));
                $charge   = \Stripe\Charge::create(array(
                    'customer' => $customer->id,
                    'amount' => $config['standard_price'].'00',
                    'currency' => 'usd'
                ));
                if ($charge) {
                    $update = $user->updateStatic($me['user_id'],array('is_standard' => 1));
                    $amount = $config['standard_price'];
                    $date   = time();

                    $db->insert(T_PAYMENTS,array('user_id' => $me['user_id'],
                                              'amount' => $amount,
                                              'type' => 'standard_member',
                                              'date' => $date));

                    $db->insert(T_TRANSACTIONS,array('user_id' => $me['user_id'],
                                      'amount' => $amount,
                                      'type' => 'standard_member',
                                      'time' => $date));
        
                    $uObj->payUpgradeCommissions(null, 'standard');

                    $data = array(
                        'status' => 200,
                        'url' => $config['site_url'] . "/upgraded?type=standard"
                    );
                }
            }
            catch (Exception $e) {
                $data = array(
                    'status' => 400,
                    'error' => $e->getMessage()
                );
            }
        }
    }
    elseif (!empty($_POST['type']) && $_POST['type'] == 'wallet' && !empty($_POST['amount'])) {
        $amount = Generic::secure($_POST['amount']);
        try {
            $customer = \Stripe\Customer::create(array(
                'source' => $token
            ));
            $charge   = \Stripe\Charge::create(array(
                'customer' => $customer->id,
                'amount' => $_POST['amount'].'00',
                'currency' => 'usd'
            ));
            if ($charge) {
                $wallet = $me['wallet'] + $amount;
                $update = $user->updateStatic($me['user_id'],array('wallet' => $wallet));

                $db->insert(T_TRANSACTIONS,array('user_id' => $me['user_id'],
                                      'amount' => $amount,
                                      'type' => 'Advertise',
                                      'time' => time()));
                $data = array(
                    'status' => 200,
                    'url' => $config['site_url'] . "/ads/wallet"
                );
            }
        }
        catch (Exception $e) {
            $data = array(
                'status' => 400,
                'error' => $e->getMessage()
            );
        }
    }
}

if ($action == 'paystack_payment' && IS_LOGGED && $config['paystack'] == 'on' && !empty($config['paystack_public']) && !empty($config['paystack_secret'])) 
{
    require_once('sys/import3p/Paystack/src/autoload.php'); 
    $pinit      = new Yabacon\Paystack($config['paystack_secret']); 
    $reference  = $_POST['reference'];
    $verify_pay = $pinit->transaction->verify( [ 'reference' => $reference ] ); 
 
    $post_type = (is_array($_POST['type']) ? key($_POST['type']) : $_POST['type']);

    if (!empty($_POST['type']) && $_POST['type'] == 'pro' && !empty($_POST['amount'])) {
        if ($config['pro_price'] == $_POST['amount']) {
            if ($verify_pay->data->status === 'success') 
            { 
                $update = $user->updateStatic($me['user_id'],array('is_pro' => 1,'verified' => 1));
                $amount = $config['pro_price'];
                $date   = time();

                $db->insert(T_PAYMENTS,array('user_id' => $me['user_id'],
                                          'amount' => $amount,
                                          'type' => 'pro_member',
                                          'date' => $date));

                $db->insert(T_TRANSACTIONS,array('user_id' => $me['user_id'],
                                  'amount' => $amount,
                                  'type' => 'pro_member',
                                  'time' => $date));
        
                $uObj->payUpgradeCommissions(null, 'pro');

                $data = array(
                    'status' => 200,
                    'url' => $config['site_url'] . "/upgraded"
                ); 
            }
            else {
                $data = array(
                    'status' => 400,
                    'error' => '<b>'.$verify_pay->data->message. ':</b> '.$verify_pay->message
                );
            }
        } 
    }
    elseif (!empty($_POST['type']) && $post_type == 'community' && !empty($_POST['amount'])) { 

        if ($community['price'] == $_POST['amount']) {
            if ($verify_pay->data->status === 'success') 
            { 
                $update = $user->updateStatic($me['user_id'],array('is_pro' => $com_is_pro, 'verified' => $com_is_ver, 'community' => $community['id']));
                $amount = $community['price'];
                $date   = time();

                $db->insert(T_PAYMENTS,array('user_id' => $me['user_id'],
                                          'amount' => $amount,
                                          'type' => 'community_' . $community['id'],
                                          'date' => $date));

                $db->insert(T_TRANSACTIONS,array('user_id' => $me['user_id'],
                                  'amount' => $amount,
                                  'type' => 'community_' . $community['id'],
                                  'time' => $date));
        
                $uObj->payUpgradeCommissions(null, 'community');

                $data = array(
                    'status' => 200,
                    'url' => $config['site_url'] . "/upgraded?type=community_member&community=" . $community['id']
                ); 
            }
            else {
                $data = array(
                    'status' => 400,
                    'error' => '<b>'.$verify_pay->data->message. ':</b> '.$verify_pay->message
                );
            }
        }
    }
    elseif (!empty($_POST['type']) && $_POST['type'] == 'standard' && !empty($_POST['amount'])) {
        if ($config['standard_price'] == $_POST['amount']) {
            if ($verify_pay->data->status === 'success') 
            { 
                $update = $user->updateStatic($me['user_id'],array('is_standard' => 1));
                $amount = $config['standard_price'];
                $date   = time();

                $db->insert(T_PAYMENTS,array('user_id' => $me['user_id'],
                                          'amount' => $amount,
                                          'type' => 'standard_member',
                                          'date' => $date));

                $db->insert(T_TRANSACTIONS,array('user_id' => $me['user_id'],
                                  'amount' => $amount,
                                  'type' => 'standard_member',
                                  'time' => $date));
        
                $uObj->payUpgradeCommissions(null, 'standard');

                $data = array(
                    'status' => 200,
                    'url' => $config['site_url'] . "/upgraded?type=standard"
                ); 
            }
            else {
                $data = array(
                    'status' => 400,
                    'error' => '<b>'.$verify_pay->data->message. ':</b> '.$verify_pay->message
                );
            }
        }
    }
    elseif (!empty($_POST['type']) && $_POST['type'] == 'wallet' && !empty($_POST['amount'])) {
        $amount = Generic::secure($_POST['amount']);
        if ($verify_pay->data->status === 'success') 
        { 
            $wallet = $me['wallet'] + $amount;
            $update = $user->updateStatic($me['user_id'],array('wallet' => $wallet));

            $db->insert(T_TRANSACTIONS,array('user_id' => $me['user_id'],
                                  'amount' => $amount,
                                  'type' => 'Advertise',
                                  'time' => time()));
            $data = array(
                'status' => 200,
                'url' => $config['site_url'] . "/ads/wallet"
            ); 
        }
        else {
            $data = array(
                'status' => 400,
                'error' => '<b>'.$verify_pay->data->message. ':</b> '.$verify_pay->message
            );
        }
    }
}

if ($action == 'bank_transfer' && IS_LOGGED) { 
    if (!empty($_FILES['image'])) {
        if (!empty($_FILES['image']) && file_exists($_FILES['image']['tmp_name'])) {
            $media = new Media();
            $media->setFile(array(
                'file' => $_FILES['image']['tmp_name'],
                'name' => $_FILES['image']['name'],
                'size' => $_FILES['image']['size'],
                'type' => $_FILES['image']['type'],
                'allowed' => 'jpeg,jpg,png'
            ));

            $upload = $media->uploadFile();

            $description = 'Upgrade to pro';
            $price = $config['pro_price'];
            $mode  = 'pro_member';
            $funding_id  = 0;

            if (!is_array($_POST['type']) && $p_t = json_decode($_POST['type'])) {
                $_POST['type'] = o2array($p_t);
            }
            $post_type = (is_array($_POST['type']) ? key($_POST['type']) : $_POST['type']); 

            if (!empty($_POST['type']) && $post_type == 'community' && !empty($_POST['price']) && is_numeric($_POST['price']) && $_POST['price'] > 0) {
                $community   = $uObj->listCommunityPlans($_POST['type'][$post_type]);
                $description = 'Join ' . $community['title'] . ' Community';
                $mode        = 'community_' . $community['id'];
                $price       = Generic::secure($_POST['price']);
            }
            if (!empty($_POST['type']) && $_POST['type'] == 'standard' && !empty($_POST['price']) && is_numeric($_POST['price']) && $_POST['price'] > 0) {
                $description = 'Upgrade to standard';
                $mode        = 'standard_member';
                $price       = Generic::secure($_POST['price']);
            }
            if (!empty($_POST['type']) && $_POST['type'] == 'wallet' && !empty($_POST['price']) && is_numeric($_POST['price']) && $_POST['price'] > 0) {
                $description = 'Wallet top up';
                $mode        = 'wallet';
                $price       = Generic::secure($_POST['price']);
            }
            if (!empty($_POST['type']) && $_POST['type'] == 'donate' && !empty($_POST['price']) && is_numeric($_POST['price']) && $_POST['price'] > 0 && !empty($_POST['fund_id'])) {
                $description = 'Donate to funding ';
                $mode        = 'donate';
                $price       = Generic::secure($_POST['price']);
                $funding_id = Generic::secure($_POST['fund_id']);
            }
            if (!empty($upload)) { 
                $image = $upload['filename'];
                $db->insert(T_BANK_TRANSFER,array('user_id' => $me['user_id'],
                                          'receipt_file' => $image,
                                          'description' => $description,
                                          'price' => $price,
                                          'mode' => $mode,
                                          'funding_id' => $funding_id));
                $data['status']  = 200;
                $data['message'] = lang('bank_transfer_request');
            }
        }
    }
    else{
        $data = array(
            'status' => 400,
            'message' => lang('please_fill_fields')
        );
    }
}




if ($action == 'paypal_donate' && IS_LOGGED && !empty($config['paypal_id']) && !empty($config['paypal_secret'])) {

    if (!empty($_POST['amount']) && is_numeric($_POST['amount']) && $_POST['amount'] > 0 && !empty($_POST['fund_id']) && is_numeric($_POST['fund_id']) && $_POST['fund_id'] > 0) {

        $user = new User();
        $fund_id = Generic::secure($_POST['fund_id']);

        $fund = $user->GetFundingById($fund_id);
        if (!empty($fund)) {
            $sum = Generic::secure($_POST['amount']);
            $type = 'wallet';
            $dec = "donate";


            $payer = new Payer();
            $payer->setPaymentMethod('paypal');
            $item = new Item();
            $item->setName($dec)->setQuantity(1)->setPrice($sum)->setCurrency($config['currency']);
            $itemList = new ItemList();
            $itemList->setItems(array(
                $item
            ));
            $details = new Details();
            $details->setSubtotal($sum);
            $amount = new Amount();
            $amount->setCurrency($config['currency'])->setTotal($sum)->setDetails($details);
            $transaction = new Transaction();
            $transaction->setAmount($amount)->setItemList($itemList)->setDescription($dec)->setInvoiceNumber(time());
            $redirectUrls = new RedirectUrls();
            $redirectUrls->setReturnUrl($config['site_url'] . "/aj/go_pro/donate_to_user&amount=".$sum."&fund_id=".$fund_id)->setCancelUrl($config['site_url']);
            $payment = new Payment();
            $payment->setIntent('sale')->setPayer($payer)->setRedirectUrls($redirectUrls)->setTransactions(array(
                $transaction
            ));
            try {
                $payment->create($paypal);
            }
            catch (Exception $e) {
                $data = array(
                    'status' => 400,
                    'message' => json_decode($e->getData())
                );
                if (empty($data['message'])) {
                    $data['message'] = json_decode($e->getCode());
                }
            }

            if (empty($data['message'])) {
                $data = array(
                    'status' => 200,
                    'url' => $payment->getApprovalLink()
                );
            }
        }
        else{
            $data = array(
                'status' => 400,
                'message' => lang('fund_not_found')
            ); 
        }
    }
    else{
        $data = array(
            'status' => 400,
            'message' => lang('please_fill_fields')
        ); 
    }
}

if ($action == 'donate_to_user' && IS_LOGGED && !empty($config['paypal_id']) && !empty($config['paypal_secret']) && !empty($_GET['paymentId']) && !empty($_GET['PayerID']) && !empty($_GET['amount']) && !empty($_GET['fund_id'])) {

    $paymentId = $_GET['paymentId'];
    $PayerID = $_GET['PayerID'];
    $payment = Payment::get($paymentId, $paypal);
    $execute = new PaymentExecution();
    $execute->setPayerId($PayerID);
    $error = '';
    try {
        $result = $payment->execute($execute, $paypal);
    }
    catch (Exception $e) {
        $error = json_decode($e->getData(), true);
    }

    if (empty($error)) {

        $amount = Generic::secure($_GET['amount']);
        $fund_id = Generic::secure($_GET['fund_id']);
        $user = new User();

        $fund = $user->GetFundingById($fund_id);
        if (!empty($fund)) {
            $admin_com = 0;
            if (!empty($config['donate_percentage']) && is_numeric($config['donate_percentage']) && $config['donate_percentage'] > 0) {
                $admin_com = ($config['donate_percentage'] * $amount) / 100;
                $amount = $amount - $admin_com;
            }
            $db->where('user_id',$fund->user_id)->update(T_USERS,array('balance'=>$db->inc($amount)));
            $db->insert(T_FUNDING_RAISE,array('user_id' => $me['user_id'],
                                              'funding_id' => $fund_id,
                                              'amount' => $amount,
                                              'time' => time()));
            
            $db->insert(T_TRANSACTIONS,array('user_id' => $me['user_id'],
                                      'amount' => $amount,
                                      'type' => 'donate',
                                      'time' => time(),
                                      'admin_com' => $admin_com));
            $notif   = new Notifications();
            $hashed_id = $fund_id;
            if (!empty($fund->hashed_id)) {
                $hashed_id = $fund->hashed_id;
            }
            if ($fund->user_id != $me['user_id']) {

                $re_data = array(
                    'notifier_id' => $me['user_id'],
                    'recipient_id' => $fund->user_id,
                    'type' => 'donated',
                    'url' => $config['site_url'] . "/funding/".$hashed_id,
                    'time' => time()
                );
                try {
                    $notif->notify($re_data);
                } catch (Exception $e) {
                }

                
            }

            header("Location: " . $config['site_url'] . "/funding/".$hashed_id);
            exit();
        }
        else{
            header("Location: " . $config['site_url'] . "/oops");
            exit();
        }
    }
    else{
        header("Location: " . $config['site_url'] . "/oops");
        exit();
    }
}


if ($action == 'stripe_donate' && IS_LOGGED && $config['credit_card'] == 'on' && !empty($config['stripe_id']) && !empty($config['stripe_id'])) {
    if (!empty($_POST['amount']) && is_numeric($_POST['amount']) && $_POST['amount'] > 0 && !empty($_POST['fund_id']) && is_numeric($_POST['fund_id']) && $_POST['fund_id'] > 0) {
        require_once('sys/import3p/stripe-php-3.20.0/vendor/autoload.php');
        $stripe = array(
          "secret_key"      =>  $config['stripe_secret'],
          "publishable_key" =>  $config['stripe_id']
        );

        \Stripe\Stripe::setApiKey($stripe['secret_key']);
        $token = $_POST['stripeToken']; 

        $amount = Generic::secure($_POST['amount']);
        $fund_id = Generic::secure($_POST['fund_id']);
        $user = new User();

        $fund = $user->GetFundingById($fund_id);
        if (!empty($fund)) {
            try {
                $customer = \Stripe\Customer::create(array(
                    'source' => $token
                ));
                $charge   = \Stripe\Charge::create(array(
                    'customer' => $customer->id,
                    'amount' => $_POST['amount'].'00',
                    'currency' => 'usd'
                ));
                if ($charge) {
                    $admin_com = 0;
                    if (!empty($config['donate_percentage']) && is_numeric($config['donate_percentage']) && $config['donate_percentage'] > 0) {
                        $admin_com = ($config['donate_percentage'] * $amount) / 100;
                        $amount = $amount - $admin_com;
                    }

                    $db->where('user_id',$fund->user_id)->update(T_USERS,array('balance'=>$db->inc($amount)));
                    $db->insert(T_FUNDING_RAISE,array('user_id' => $me['user_id'],
                                                      'funding_id' => $fund_id,
                                                      'amount' => $amount,
                                                      'time' => time()));

                    $db->insert(T_TRANSACTIONS,array('user_id' => $me['user_id'],
                                      'amount' => $amount,
                                      'type' => 'donate',
                                      'time' => time(),
                                      'admin_com' => $admin_com));

                    $notif   = new Notifications();
                    $re_data = array(
                        'notifier_id' => $me['user_id'],
                        'recipient_id' => $fund->user_id,
                        'type' => 'donated',
                        'url' => $config['site_url'] . "/funding/".$fund_id,
                        'time' => time()
                    );

                    try {
                        $notif->notify($re_data);
                    } catch (Exception $e) {
                    }
                    $data = array(
                        'status' => 200
                    );
                }
            }
            catch (Exception $e) {
                $data = array(
                    'status' => 400,
                    'error' => $e->getMessage()
                );
            }
        }
    }

    
    
}
