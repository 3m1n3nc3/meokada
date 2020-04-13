<?php
if (IS_LOGGED !== true || $config['blog_system'] == 'off') {
    header("Location: $site_url/welcome");
    exit;
}

$context['page_link'] = 'blog';
$catid = null;
$category_name = $context['lang']['blog'];
$category_desc = $context['lang']['explore_blog_desc'];

$context['category_name'] = $category_name;
$context['category_desc'] = $category_desc;

if( isset($_GET['id']) ){
    if (empty($_GET['id']) || !is_numeric($_GET['id'])) {
        header("Location: $site_url/404");
        exit;
    }

    $catid = Generic::secure($_GET['id']);

    $context['category_name'] = $context['lang'][$catid];
    $context['category_desc'] = '';
    $context['page_link'] = 'blog/category/'.$catid;
    $db->where('category', $catid);
}

$context['blogs']  = array();
$posts = $db->arrayBuilder()->orderBy('id','DESC')->get(T_BLOG,20);
foreach ($posts as $key => $post_data) {
    $post_data['category_name'] = $context['lang'][$post_data['category']];
    $post_data['full_thumbnail'] = media($post_data['thumbnail']);
    $post_data['text_time'] = time2str($post_data['created_at']);
    $context['blogs'][]    = $post_data;
}

$context['exjs'] = true;
$context['app_name'] = 'blog';
$context['page_title'] = $context['lang']['blog'];
$context['content'] = $pixelphoto->PX_LoadPage('blog/templates/blog/index');
