<?php
if (IS_LOGGED !== true) {
	header("Location: $site_url/welcome");
	exit;
} 

$uObj = new User; 

$context['page_link']  = 'upgraded';
$context['exjs']       = true;
$context['app_name']   = 'upgraded';
$context['page_title'] = $context['lang']['upgraded'];

$context['upgrade_type'] = 'Pro';

if (isset($_GET['type'])) {
    if ($_GET['type'] == 'community' || $_GET['type'] == 'community_member') 
    {
        $cid = (!empty($_GET['community']) ? $_GET['community'] : (!empty($_GET['communityid']) ? $_GET['communityid'] : $_GET['community_id']));

        $community               = $uObj->listCommunityPlans($cid); 
        $context['upgrade_type'] = $community['title'] . ' Community';
    } else {
        $context['upgrade_type'] = ucwords($_GET['type']);
    }
} 

$context['content']    = $pixelphoto->PX_LoadPage('upgraded/templates/upgraded/index');
