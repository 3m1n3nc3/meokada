<?php 
if (IS_LOGGED !== true) {
	header("Location: $site_url/welcome");
	exit;
}
 
$channel    = isset($_GET['channel']) ? $_GET['channel'] : null;
$restricted = false;
$context['this_tv']    = $channel;
$context['restricted'] = $restricted;

$context['posts']  = array();
$posts             = new Posts(); 
$posts->limit      = 3;
$posts->comm_limit = 4;
$tlposts           = $posts->setUserById($me['user_id'])->getTvPosts(false, 5, $channel);
$boost_post_query = $db->where('boosted',1)->orderBy('RAND()')->getOne(T_POSTS);
$boost_post = array();
if (!empty($boost_post_query)) {
	$posts->setPostId($boost_post_query->post_id);
	$boost_post = $posts->postData('');
}

$challenge = $posts->challengeData();

if (!empty($challenge)) {
	$context['challenge'] = o2array($challenge);
	// print_r($context['challenge']);	
}	
$follow    = $user->followSuggestions();  
$post_sys  = array( 
	($config['upload_videos'] == 'on'),
	($config['import_videos'] == 'on') 
);

if (!empty($tlposts)) {
	$context['posts'] = o2array($tlposts);
}
if (!empty($boost_post) && !empty($context['posts'])) {
	array_unshift($context['posts'],o2array($boost_post));
}
elseif (empty($context['posts']) && !empty($boost_post)) {
	$context['posts'][] = o2array($boost_post);
}
$context['is_boosted'] = true;
$activities = $posts->getUsersActivities(0,5);
$activities = o2array($activities);

$user = new User();
$context['pro_members'] = $user->GetProUsers();
$context['funding'] = $user->GetFunding(4);
$context['sidebar_ad'] = $user->GetRandomAd('sidebar');
$context['page_link'] = '';
$trending = array();
$context['posts'] = $context['posts'];
$context['follow'] = o2array($follow); 
$context['trending'] = $trending; 
$context['activities'] = $activities;
$context['post_sys'] = (in_array(true, $post_sys));
$context['exjs'] = true;
$config['footer'] = false;
$context['app_name'] = 'tv';
$context['page_title'] = $config['site_name'] . ' TV';
$context['content'] = $pixelphoto->PX_LoadPage('tv/templates/tv/index');
