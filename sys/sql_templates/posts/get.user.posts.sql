SELECT p.`type`,ANY_VALUE(m.id) AS id, ANY_VALUE(m.post_id) AS post_id, ANY_VALUE(m.user_id) AS user_id, ANY_VALUE(m.file) AS file,ANY_VALUE(m.extra) AS extra, (SELECT COUNT(`id`) FROM `{%t_likes%}` l WHERE l.`post_id` = p.`post_id`) AS likes, (SELECT COUNT(`id`) FROM `{%t_comm%}` c WHERE c.`post_id` = p.`post_id`) AS comments FROM `{%t_posts%}` p 
	
	INNER JOIN `{%t_media%}` m ON m.`post_id` = p.`post_id`

	WHERE p.`user_id` = '{%user_id%}'

	{%if offset%}
		AND p.`post_id` < {%offset%}
	{%endif%}

	GROUP BY p.`post_id` ORDER BY p.`post_id` DESC LIMIT {%total_limit%}
