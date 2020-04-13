<?php
if ($action == 'load-tl-articles' && IS_LOGGED) {
    if (!empty($_GET['offset']) && is_numeric($_GET['offset'])) {
        $last_id  = $_GET['offset'];
        $posts = $db->arrayBuilder()->where('id',$last_id,'<')->orderBy('id','DESC')->get(T_BLOG,20);
        $data     = array('status' => 404);
        $html     = "";

        if (len($posts) > 0) {
            foreach ($posts as $key => $post_data) {
                $post_data['category_name'] = $context['lang'][$post_data['category']];
                $post_data['full_thumbnail'] = media($post_data['thumbnail']);
                $post_data['text_time'] = time2str($post_data['created_at']);
                $html  .= $pixelphoto->PX_LoadPage('blog/templates/blog/includes/list');
            }
            $data['status'] = 200;
            $data['html']   = $html;
        }
    }
}