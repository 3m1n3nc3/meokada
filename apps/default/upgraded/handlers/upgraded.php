<?php
if (IS_LOGGED !== true) {
	header("Location: $site_url/welcome");
	exit;
}
// $context['posts']  = array();
// $posts             = new Posts();
// $posts->orderBy('post_id','DESC');
// $posts->limit      = 40;
// $query_posts       = $posts->explorePosts();
// $follow            = array();

// if (IS_LOGGED) {
// 	$follow = $user->followSuggestions();
// }


// if (!empty($query_posts)) {
// 	$context['posts'] = o2array($query_posts);
// }

// $follow = (!empty($follow) && is_array($follow)) ? o2array($follow) : array();

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
