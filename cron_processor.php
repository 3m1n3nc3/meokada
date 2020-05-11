<?php
require_once('./sys/init.php');

if (!empty($_GET) OR !empty($_POST) OR !IS_LOGGED) { 
    header('HTTP/1.1 401 Access Denied.', TRUE, 401);
    echo 'Access Denied: You do not have sufficient privileges to access this script...';
    exit(1);
} 

$admin = new Admin();
$postC = new Posts;

$auto_payout = ($config['auto_payout_approved_challenge'] == 'on' OR $config['auto_payout_won_challenge'] == 'on' OR $config['auto_payout_referals'] == 'on');

if ($auto_payout) {

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
                $admin::$db->where('id',$request_id)->update(T_WITHDRAWAL,array('recipient_code' => $recipient_code));
            }

            if ($recipient_code) {
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
        $_SESSION['last_auto_payouts'] = time();
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
}

if ($config['auto_pay_wallet'] !== 'off') {
 // && $config['auto_pay_wallet_limit'] >=0
    $wallets        = $admin->socialWallet();
    foreach ($wallets as $key => $wallet) {
        // $new_percent = (100 / $distro_percent) * $wallet['percentage'];
        // $balance     = ($new_percent / 100)*$amount; 

        // $r_data['balance'] = self::$db->inc($balance);

        // self::$db->where('id', $wallet['id'])->update(T_SOCIAL_WALLET, $r_data); 
        
        $amount = ($wallet['balance']-$wallet['paidout']);
        $user_data = array(
            'type'           => $wallet['account_type'],
            'name'           => $wallet['title'],
            'description'    => 'Social Wallet Payouts from ' . $config['site_name'],
            'account_number' => $wallet['account_number'],
            'bank_code'      => $wallet['bank_code'],
            'amount'         => $amount*($config['currency'] == 'NGN' || $config['currency'] == 'GHS' ? 100 : 0)
        );
    }
}
// echo$admin::$db->getLastQuery();
