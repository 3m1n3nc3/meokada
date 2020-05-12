<?php
if (IS_LOGGED !== true) {
	header("Location: $site_url/welcome");
	exit;
}
$pages    = array('challenge','wallet');
$set_page = !empty($_GET['page']) && in_array($_GET['page'], $pages);
$page     = !empty($_GET['info']) ? 'info' : (!empty($_GET['exclusive']) ? 'exclusive' : '');

$user     = new User();
$posts    = new Posts();

$post_sys  = array(
	($config['upload_images'] == 'on'),
	($config['upload_videos'] == 'on'),
	($config['import_videos'] == 'on'),
	($config['import_images'] == 'on'),
	($config['story_system'] == 'on'),
);
$context['post_sys'] = (in_array(true, $post_sys));

$wallet_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" class="feather feather-wallet"><path fill="#ff7400" d="M21,18V19A2,2 0 0,1 19,21H5C3.89,21 3,20.1 3,19V5A2,2 0 0,1 5,3H19A2,2 0 0,1 21,5V6H12C10.89,6 10,6.9 10,8V16A2,2 0 0,0 12,18M12,16H22V8H12M16,13.5A1.5,1.5 0 0,1 14.5,12A1.5,1.5 0 0,1 16,10.5A1.5,1.5 0 0,1 17.5,12A1.5,1.5 0 0,1 16,13.5Z"></path></svg>';

$eid        = !empty($_GET['exclusive']) ? $_GET['exclusive'] : NULL;
$challenges = $posts->challengeData(['exclusive' => $eid]);
if (!empty($challenges)) {
	$context['challenges'] = o2array($challenges); 
}	

if ($set_page && $_GET['page'] == 'challenge' && !empty($page)) { 
	$exclusive         = !empty($_GET[$page]) ? $_GET[$page] : '';
	$exlsv_data        = $context['exclusive'] = $user->listExclusivePlans($exclusive);
	$user_level		   = $user->verifyProLevel(NULL, $exlsv_data['pro_level']);

	if ($page !== 'info' && $exlsv_data['pro_level'] > $user_level['pro_level'] || ($exlsv_data['pro_level'] = 3 && !$user_level['community_access'])) {
		header("Location: $site_url/go_pro");
		exit;
	}

	$exlsv_data['icon'] = decode($exlsv_data['icon']);
	$exlsv_data['description'] = decode($exlsv_data['description']);
	$exclusive_url         = isset($_GET[$page]) ? '/' . $page . '/' . $_GET[$page] : '';
	$context['page_link']  = 'navigation/challenge' . $exclusive_url;
	$context['exjs']       = true;
	$context['app_name']   = 'navigation';
	$context['page_name']  = $page;
	$context['page_title'] = $context['lang']['challenge'];
	$context['header']     = $pixelphoto->PX_LoadPage('navigation/templates/navigation/header', $exlsv_data);
	$context['content']    = $pixelphoto->PX_LoadPage('navigation/templates/navigation/challenge', $exlsv_data);
}
elseif ($set_page && $_GET['page'] == 'wallet') {
	$wallet_url            = isset($_GET[$page]) ? '/' . $page . '/' . $_GET[$page] : '';
	$context['wallets']    = $user->socialWallet(NULL, 1);
	$context['page_link']  = 'navigation/wallet' . $wallet_url;
	$context['exjs']       = true;
	$context['app_name']   = 'navigation';
	$context['page_view']  = isset($_GET[$page]) ? $_GET[$page] : '';
	$context['page_name']  = $page;
	$context['page_title'] = $context['lang']['social_wallet']; 

	$wallet_data['title']   = $context['page_title'];
	$wallet_data['icon']    = $wallet_icon;
	$context['content']     = $pixelphoto->PX_LoadPage('navigation/templates/navigation/wallet', $wallet_data);
}
else{
	$context['user_ads']   = $user->GetUserAds();
	$context['page_link']  = 'navigation';
	$context['exjs']       = true;
	$context['app_name']   = 'navigation';
	$context['page_title'] = $context['lang']['challenge'];
	$context['exclusive']  = $user->listExclusivePlans();
	$context['content']    = $pixelphoto->PX_LoadPage('navigation/templates/navigation/index');
}
