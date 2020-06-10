<?php
require_once('./sys/init.php'); 

header('Content-type: text/json'); 

if (!empty($_GET) OR !empty($_POST) OR !IS_LOGGED) { 
    header('HTTP/1.1 401 Access Denied.', TRUE, 401);
    echo 'Access Denied: You do not have sufficient privileges to access this script...';
    exit(1);
} 

$admin  = new Admin();
$userC  = new User;
$postC  = new Posts;
$notifC = new Notifications;


$last_auto_pay_wallet = ($_SESSION['last_auto_pay_wallet']??null);
$last_auto_payouts    = ($_SESSION['last_auto_payouts']??null);
$last_generic_action  = ($_SESSION['last_generic_action']??null);
 
$api_refreshed        = strtotime("NOW - {$config['api_refresh_interval']} Hour");


$auto_payout = ($config['auto_payout_approved_challenge'] == 'on' OR $config['auto_payout_won_challenge'] == 'on' OR $config['auto_payout_referals'] == 'on');

if ($auto_payout && (empty($last_auto_payouts) || $last_auto_payouts < $api_refreshed)) {

    // Pay cashout request to bank
    $requests = o2array($admin::$db->where('status','0')->where('paid','0')->get(T_WITHDRAWAL));
    foreach ($requests as $request_data) 
    { 
        if ($request_data['paid'] != 1)
        {
            $requiring = o2array($admin::$db->where('user_id',$request_data['user_id'])->getOne(T_USERS));
            $user_data = array(
                'type'           => $request_data['account_type'],
                'name'           => $user->fullName($requiring),
                'description'    => $requiring['about'],
                'account_number' => $request_data['account_number'],
                'bank_code'      => $request_data['bank_code'],
                'amount'         => $request_data['amount']*($config['currency'] == 'NGN' || $config['currency'] == 'GHS' ? 100 : 0)
            );

            $create_customer = $admin->payUser('create_recipient', $user_data);
            $recipient_code = $create_customer['data']['recipient_code'] ?? '';
            if ($recipient_code) {
                $admin::$db->where('id',$request_data['id'])->update(T_WITHDRAWAL,array('recipient_code' => $recipient_code));
                $meta[] = array('recipient' => $recipient_code, 'amount' => $request_data['amount']);  
            } else {
                $meta = [];
            }
        }  
    }

    if (!empty($meta)) {
        $process = $admin->payUser('initiate_bulk_transfer', ['transfers' => $meta]);
        if (!empty($process['data'])) {
            foreach ($process['data'] as $request_id) 
            { 
                $user->updateBalance($request_id['recipient'], true, true);
            }
        }
    }


    // Pay challenge winners
    if ($config['auto_payout_won_challenge']) 
    {
        $nat_win    = false;  
        $challenges = $postC->getRecentChallenges();

        if ($challenges) 
        {
            foreach ($challenges as $key => $cdata) 
            {
                // Check If challenge has ended
                if (!$postC->challengeIsActive($cdata->challenge_id)) 
                { 
                    $user_data = o2array($postC->getApprovedContestants($cdata->challenge_id));
                    if ($user_data) 
                    {
                        $i = 0;
                        foreach ($user_data as $key => $udata) 
                        {
                            $i++; 
                            $win  = o2array($postC->getWinnersOnly($udata['username'], $cdata->challenge_id)); 
                            $postC->payChallengeWinner($win['post_id'], $win['challenge_id'], $i, $nat_win); 
                        }
                    } 

                    // Check for National Winners
                    $national_challengers = $postC->getPartakingCountries($cdata->challenge_id);

                    if ($national_challengers) 
                    {         
                        foreach ($national_challengers as $key => $country) 
                        {
                            $user_data = $postC->nationalWinnersOnly($country->country_id, $cdata->challenge_id);
                            if ($user_data) 
                            {
                                $i = 0;
                                foreach ($user_data as $udata) 
                                {
                                    $i++;
                                    $win = o2array($postC->getWinnersOnly($udata->username, $cdata->challenge_id)); 
                                    $postC->payChallengeWinner($win['post_id'], $win['challenge_id'], $i, $nat_win); 
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    $_SESSION['last_auto_payouts'] = time();
}


// Make socialWallet Payouts
if ($config['auto_pay_wallet'] !== 'off' && (empty($last_auto_pay_wallet) || $last_auto_pay_wallet < $api_refreshed)) {
    $wallets = $admin->socialWallet();
    $i = 0;
    if ($wallets) { 
        foreach ($wallets as $key => $wallet) {
            $i++;  
            
            $amount = ($wallet['balance']-$wallet['paidout']);
            if ($wallet['account_number'] && $amount > $config['auto_pay_wallet_limit']) { 
                $user_data = array(
                    'paidout'        => $wallet['paidout'],
                    'type'           => $wallet['account_type'],
                    'name'           => $wallet['title'],
                    'description'    => 'Social Wallet Payouts from ' . $config['site_name'],
                    'account_number' => $wallet['account_number'],
                    'bank_code'      => $wallet['bank_code'],
                    'amount'         => $amount*($config['currency'] == 'NGN' || $config['currency'] == 'GHS' ? 100 : 0)
                );

                $create_customer = $admin->payUser('create_recipient', $user_data);
                $recipient_code  = $create_customer['data']['recipient_code'] ?? '';
                if ($recipient_code) {
                    $wallet_meta[] = array('recipient' => $recipient_code, 'amount' => $amount);  
                    $admin::$db->where('id',$wallet['id'])->update(T_SOCIAL_WALLET, array('recipient_code' => $recipient_code));
                } else {
                    $wallet_meta = [];
                } 
            } 
        }

        if (!empty($wallet_meta)) {
            $wallet_process = $admin->payUser('initiate_bulk_transfer', ['transfers' => $wallet_meta]);
            if (!empty($wallet_process['data'])) {
                foreach ($wallet_process['data'] as $request_id) 
                {
                    $admin::$db->where('recipient_code', $request_id['recipient'])->update(T_SOCIAL_WALLET,array('paidout' => $admin::$db->inc($request_id['amount']), 'balance' => $admin::$db->dec($request_id['amount'])));
                }
            }
        }
        $_SESSION['last_auto_pay_wallet'] = time();
    }
}


// Send TV notifications
if ($config['send_tv_notifs'] == 'on') { 

    $t_notif = T_NOTIF;
    $t_users = T_USERS; 
    $t_posts = T_POSTS; 

    $all_users = $admin->getAllUsers();
 
    $admin::$db->where("tv = 1 AND time >= $api_refreshed");
    $all_tv = $admin::$db->get("{$t_posts} p", NULL, "post_id, user_id, time, tv"); 

    foreach ($all_tv as $tv) 
    {
        foreach ($all_users as $user) 
        {

            $username = $user->username;

            try 
            { 
                $notif_conf     = null; 
                if (is_numeric($user->user_id)) 
                {
                    $notif_conf = $notifC->notifSettings($user->user_id,'on_tv'); 
                }
        
                $admin::$db->where('n.type',"new_tv_post.ar.{$tv->post_id}"); 
                $query = $admin::$db->get("{$t_notif} n",NULL,"n.*");
                if (empty($query) && $notif_conf) 
                {
                    if ($tv->user_id && $tv->user_id != $user->user_id) 
                    {
                        $re_data = array(
                            'notifier_id' => $tv->user_id,
                            'recipient_id' => $user->user_id,
                            'type' => 'new_tv_post.ar.'.$tv->post_id,
                            'url' => pid2url($tv->post_id),
                            'time' => time()
                        ); 
                        $notifC->notify($re_data);
                    } 
                }
            } 
            catch (Exception $e) 
            {  
                echo $e;
            }
        }
    }
}

// Delete Expired Coupons
if ($config['coupon_system'] == 'on' && $last_generic_action < $api_refreshed) {
    $admin::$db->where('expiry_date', date('Y-m-d H:i:s', strtotime('NOW - 12 Hours')), '<')->delete(T_COUPONS);  
    $_SESSION['last_generic_action'] = time();
}
// echo $admin::$db->getLastQuery();
// echo $db->getLastError();
