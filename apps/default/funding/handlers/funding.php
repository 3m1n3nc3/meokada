<?php
if (IS_LOGGED !== true || $config['raise_money'] == 'off') {
	header("Location: $site_url/welcome");
	exit;
}
$user = new User();
$page = 'all';
if (!empty($_GET['id'])) {
	if (is_numeric($_GET['id']) && $_GET['id'] > 0) {
		$context['fund'] = $user->GetFundingById($_GET['id']);
	}
	else{
		$context['fund'] = $user->GetFundingById($_GET['id'],'hash');
	}
	
	$context['recent'] = $user->GetRecentRaise($context['fund']->id,20);
	$page = 'index';
	$context['page_link'] = 'funding/'.$_GET['id'];
}
else{
    $context['fund'] = $user->GetFunding(10);
    $context['page_link'] = 'funding';
}
if (empty($context['fund'])) {
	header("Location: $site_url/welcome");
	exit();
}



$context['app_name'] = 'funding';
$context['page_title'] = $context['lang']['funding'];
$context['content'] = $pixelphoto->PX_LoadPage('funding/templates/funding/'.$page);
