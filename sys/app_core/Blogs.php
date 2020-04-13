<?php

/**
 * Blogs class, everything related to blogs.
 */

class Blogs extends User{
    protected $article_id = 0;

    public function all(){
        global $context;
        $posts = self::$db->get(T_BLOG,$this->limit);
        $data  = array();
        foreach ($posts as $key => $post_data) {
            $post_data['category_name'] = $context['lang'][$post_data['category']];
            $post_data['full_thumbnail'] = media($post_data['thumbnail']);
            $data[]    = $post_data;
        }
        return $data;
    }
}