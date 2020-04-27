<?php 
if (IS_LOGGED !== true) {
    header("Location: $site_url/welcome");
    exit;
}
$cObj = $context['cObj'] = new Posts; 
$challenges  = $cObj->getRecentChallenges();

$context['page_link'] = 'explore/winners';
$context['exjs'] = true;
$context['app_name'] = 'explore';
$context['page'] = 'winners';
$context['page_title'] = $context['lang']['explore_winners']; 
$context['challenges'] = o2Array($challenges); 
$context['content'] = $pixelphoto->PX_LoadPage('explore/templates/explore/winners');
