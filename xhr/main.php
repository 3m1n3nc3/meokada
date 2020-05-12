<?php 

if ($action == 'follow' && IS_LOGGED) {
	if (!empty($_GET['user_id']) && is_numeric($_GET['user_id'])) {
		$follower_id  = $me['user_id'];
		$following_id = Generic::secure($_GET['user_id']);
		$notif        = new Notifications();
		$user->setUserById($follower_id);
		$status       = $user->follow($following_id);
		$data['status'] = 400;
		if ($status === 1) {
			$data['status'] = 200;
			$data['code'] = 1;

			#Notify post owner
			$notif_conf = $notif->notifSettings($following_id,'on_follow');
			if ($notif_conf) {
				$re_data = array(
					'notifier_id' => $me['user_id'],
					'recipient_id' => $following_id,
					'type' => 'followed_u',
					'url' => un2url($me['username']),
					'time' => time()
				);
				
				$notif->notify($re_data);
			}	
		}

		else if($status === -1){
			$data['status'] = 200;
			$data['code'] = 0;
		}

		goto exit_xhr;
	}
}

else if($action == 'get_notif' && IS_LOGGED){
	$notif = new Notifications();
	$data  = array();

	$notif->setUserById($me['user_id']);
	$notif->type    = 'all';
	$notif->limit   = 1000;
	$queryset       = $notif->getNotifications();

	if (!empty($queryset) && is_array($queryset)) {
		$new_notif      = o2array($queryset);
		$context['notifications'] = $new_notif;
		$data['html']    = $pixelphoto->PX_LoadPage('main/templates/header/notifications');
		$data['status'] = 200;
	}

	else{
		$data['status']  = 304;
		$data['message'] = lang('u_dont_have_notif');
	}
}

else if($action == 'get_requests' && IS_LOGGED){
	

	$db->where('following_id',$me['user_id']);
	$db->where('type',2);
	$db->orderBy('id','DESC');
	$requests = $db->get(T_CONNECTIV,10);
	$db->where('following_id',$me['user_id'])->where('active',0)->update(T_CONNECTIV,array('active' => 1));
	$user = new User();
	$html = '';

	foreach ($requests as $key => $request) {
		$context['request'] = $request;
		$context['user_data'] = $user->getUserDataById($request->follower_id);
		$html .= $pixelphoto->PX_LoadPage('main/templates/header/requests');
		$data['status'] = 200;
		$data['html'] = $html;

	}
	if (empty($html)) {
		$data['status']  = 304;
		$data['message'] = lang('u_dont_have_requests');
	}
}
else if($action == 'accept_requests' && IS_LOGGED){
	$data['status'] = 400;
	if (!empty($_POST['user_id']) && is_numeric($_POST['user_id']) && $_POST['user_id'] > 0) {
		$db->where('following_id',$me['user_id']);
		$db->where('follower_id',Generic::secure($_POST['user_id']));
		$db->where('type',2);
		$request = $db->getOne(T_CONNECTIV);
		$user = new User();
		$follower = $user->getUserDataById($request->follower_id);
		if (!empty($request) && !empty($follower)) {
			$db->where('id',$request->id)->update(T_CONNECTIV,array('type' => 1,'active' => 1));
			$notif        = new Notifications();
			$re_data = array(
				'notifier_id' => $me['user_id'],
				'recipient_id' => $follower->user_id,
				'type' => 'accept_request',
				'url' => un2url($me['username']),
				'time' => time()
			);
				
			$notif->notify($re_data);
			$data['status'] = 200;
			$data['message'] = $follower->name . ' '. lang('is_following_you');
		}
		else{
			$data['message'] = lang('please_check_details');
		}
	}
	else{
		$data['message'] = lang('please_check_details');
	}
}
else if($action == 'delete_requests' && IS_LOGGED){
	$data['status'] = 400;
	if (!empty($_POST['user_id']) && is_numeric($_POST['user_id']) && $_POST['user_id'] > 0) {
		 $db->where('following_id',$me['user_id']);
		 $db->where('follower_id',Generic::secure($_POST['user_id']));
		 $db->where('type',2);
		 $request = $db->delete(T_CONNECTIV);
		$data['status'] = 200;
	}
	else{
		$data['message'] = lang('please_check_details');
	}
}

elseif ($action == 'update-data' && IS_LOGGED) {
	$data  = array();
	$notif = new Notifications();

	$notif->setUserById($me['user_id']);
	$notif->type    = 'new';
	$new_notif      = $notif->getNotifications();
	$data['notif']  = (is_numeric($new_notif)) ? $new_notif : 0;

	$db->where('following_id',$me['user_id']);
	$db->where('type',2);
	$db->where('active',0);
	$data['requests'] = $db->getValue(T_CONNECTIV,"COUNT(*)");

	if (!empty($_GET['new_messages'])) {
		$messages     = new Messages();
		$messages->setUserById($me['user_id']);
		$new_messages = $messages->countNewMessages();
		$data['new_messages'] = $new_messages;
	}
}

elseif ($action == 'explore-people' && IS_LOGGED) {
    if (!empty($_GET['offset']) && is_numeric($_GET['offset'])) {
        $user->limit = 100;
        $offset      = $_GET['offset'];
        $users       = $user->explorePeople($offset);
        $data        = array('status' => 404);

        if (!empty($users)) {
            $users = o2array($users);
            $html  = "";

            foreach ($users as $udata) {
                $html    .= $pixelphoto->PX_LoadPage('explore/templates/explore/includes/row');
            }

            $data = array(
                'status' => 200,
                'html' => $html
            );
        }
    }
}

elseif ($action == 'explore-winners' && IS_LOGGED) {
    if (!empty($_GET['offset']) && is_numeric($_GET['offset'])) { 
        $page       = $_GET['offset'];
        $challenges = $cObj->getRecentChallenges($page);
        $data       = array('status' => 404);

        if (!empty($challenges)) {
            $users = o2array($challenges);
            $html  = "";

            foreach ($users as $udata) {
                $html    .= $pixelphoto->PX_LoadPage('explore/templates/explore/includes/col');
            }

            $data = array(
                'status' => 200,
                'html' => $html
            );
        }
    }
}

elseif ($action == 'report-profile' && IS_LOGGED && !empty($_POST['id'])){
	if (is_numeric($_POST['id']) && !empty($_POST['t'])) {
		$user_id = $_POST['id'];
		$type    = $_POST['t'];
		$data    = array('status' => 304);
		if (in_array($type, range(1, 8)) || $type == -1) {
			$code = $user->reportUser($user_id,$type);
			$code = ($code == -1) ? 0 : 1;
			$data = array(
				'status' => 200,
				'code' => $code,
			);

			if ($code == 0) {
				$data['message'] = lang('report_canceled');
			}

			else if($code == 1){
				$data['message'] = lang('report_sent');
			}
		}
	}
}

elseif ($action == 'block-user' && IS_LOGGED && !empty($_POST['id'])){
	if (is_numeric($_POST['id'])) {
		$user_id = $_POST['id'];
		$data    = array('status' => 304);
		$notif   = new Notifications();
		$code    = $user->blockUser($user_id);
		$code    = ($code == -1) ? 0 : 1;

		if (in_array($code, array(0,1))) {
			$data    = array(
				'status' => 200,
				'code' => $code,
			);

			if ($code == 0) {
				$data['message'] = lang('user_unblocked');
			}

			else if($code == 1){
				$data['message']    = lang('user_blocked');
				$notif->notifier_id = $user_id; 
				$notif->setUserById($me['user_id'])->clearNotifications();
			}
		}
	}
}

elseif ($action == 'search-users' && !empty($_POST['kw'])){
	if (len($_POST['kw']) >= 0) {
		$kword    = $_POST['kw'];
		$data     = array('status' => 304);
		$queryset = $user->seachUsers($kword);
		$html     = "";

		if( substr($kword, 0,1) == '#' ){

            if (len($_POST['kw']) >= 0) {
                $posts    = new Posts();
                $kword    = $_POST['kw'];
                $data     = array('status' => 304);
                $queryset = $posts->searchPosts($kword);
                $html     = "";

                if(!empty($queryset)){
                    $queryset = o2array($queryset);

                    foreach ($queryset as $htag) {
                        $htag['url'] = sprintf('%s/explore/tags/%s',$site_url,$htag['tag']);
                        $context['htag'] = $htag;
                        $html    .= $pixelphoto->PX_LoadPage('main/templates/header/search-posts');
                    }

                    $data['status'] = 200;
                    $data['html']   = $html;
                }
            }

//            $data['status'] = 200;
//            $data['html']   = $kword;
        }else {
            if (!empty($queryset)) {
                $queryset = o2array($queryset);

                foreach ($queryset as $udata) {
                    $html .= $pixelphoto->PX_LoadPage('main/templates/header/search-usrls');
                }

                $data['status'] = 200;
                $data['html'] = $html;
            } else {

//                $html = '';
//                $posts = new Posts();
//                $queryset2 = $posts->searchPosts($kword);
//                if (!empty($queryset2)) {
//                    $queryset2 = o2array($queryset2);
//                    foreach ($queryset2 as $htag) {
//                        $htag['url'] = sprintf('%s/explore/tags/%s', $site_url, $htag['tag']);
//                        $context['htag'] = $htag;
//                        $html .= $pixelphoto->PX_LoadPage('main/templates/header/search-posts');
//                    }
//
//                    $data['status'] = 200;
//                    $data['html'] = $html;
//                }
            }
        }
	}
}

elseif ($action == 'search-blog' && !empty($_POST['kw'])){
    if (len($_POST['kw']) >= 0) {
        $kword    = $_POST['kw'];
        $data['status'] = 200;
        $data['html']   = $pixelphoto->PX_LoadPage('blog/templates/blog/includes/no-articles-found');
        $html     = "";

        if (len($_POST['kw']) >= 0) {
            $queryset = $db->arrayBuilder()->where('title','%' . $kword . '%', 'like')->orWhere('content','%' . $kword . '%', 'like')->orWhere('description','%' . $kword . '%', 'like')->orderBy('id','DESC')->get(T_BLOG,20);
            $html     = "";
            if(!empty($queryset)){
                $queryset = o2array($queryset);
                foreach ($queryset as $key => $post_data) {
                    $post_data['category_name'] = $context['lang'][$post_data['category']];
                    $post_data['full_thumbnail'] = media($post_data['thumbnail']);
                    $post_data['text_time'] = time2str($post_data['created_at']);
                    $html    .= $pixelphoto->PX_LoadPage('blog/templates/blog/includes/list');
                }
                $data['status'] = 200;
                $data['html']   = $html;
            }
        }
    }
}

elseif ($action == 'search-posts' && !empty($_POST['kw'])){
	if (len($_POST['kw']) >= 0) {
		$posts    = new Posts();
		$kword    = $_POST['kw'];
		$data     = array('status' => 304);
		$queryset = $posts->searchPosts($kword);
		$html     = "";

		if(!empty($queryset)){
			$queryset = o2array($queryset);

			foreach ($queryset as $htag) {
				$htag['url'] = sprintf('%s/explore/tags/%s',$site_url,$htag['tag']);
				$context['htag'] = $htag;
				$html    .= $pixelphoto->PX_LoadPage('main/templates/header/search-posts');
			}

			$data['status'] = 200;
			$data['html']   = $html;
		}
	}
}
elseif ($action == 'contact_us'){
	$data['status'] = 400;
	if (empty($_POST['first_name']) || empty($_POST['last_name']) || empty($_POST['email']) || empty($_POST['message'])) {
		$data['message'] = lang('please_check_details');
	}
	else if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $data['message'] = lang('email_invalid_characters');
    }
	else{
		$first_name        = Generic::secure($_POST['first_name']);
        $last_name         = Generic::secure($_POST['last_name']);
        $email             = Generic::secure($_POST['email']);
        $message           = Generic::secure($_POST['message']);
        $name              = $first_name . ' ' . $last_name;
		$message_text = "<p><strong>Name</strong> : {$name}</p>
						 <br>
						 <p><strong>Email</strong> : {$email}</p>
						 <br>
						 <p><strong>Message</strong> : {$message}</p>
						 ";

        $send_email_data = array(
            'from_email' => $email,
            'from_name' => $name,
            'reply-to' => $email,
            'to_email' => $config['site_email'],
            'to_name' => $user->user_data->name,
            'subject' => 'Contact us new message',
            'charSet' => 'UTF-8',
            'message_body' => $message_text,
            'is_html' => true
        );
        $send_message = Generic::sendMail($send_email_data);
        if ($send_message) {
            $data['status'] = 200;
            $data['message'] = lang('email_sent');
        }else{
        	$data['message'] = lang('unknown_error');
        }
	}
}

elseif ($action == 'change_mode') {

	if ($_COOKIE['mode'] == 'day') {
		setcookie("mode", 'night', time() + (10 * 365 * 24 * 60 * 60), "/");
		$data = array('status' => 200,
	                  'type' => 'night',
	                  'link' => $config['site_url'].'/apps/'.$config['theme'].'/main/static/css/styles.master_night.css');
	}
	else{
		setcookie("mode", 'day', time() + (10 * 365 * 24 * 60 * 60), "/");
		$data = array('status' => 200,
	                  'type' => 'day');
	}
}

elseif ($action == 'get_more_activities') {
	$data = array('status' => 400);

	if (!empty($_POST['id']) && is_numeric($_POST['id'])) {
		$html = '';
		$posts  = new Posts();
		$offset = Generic::secure($_POST['id']);
		$activities = $posts->getUsersActivities($offset,5);
		$activities = o2array($activities);
		if (!empty($activities)) {
			foreach ($activities as $key => $value) {
				$context['activity'] = $value;
				$html    .= $pixelphoto->PX_LoadPage('home/templates/home/includes/activity');
			}
			$data = array('status' => 200,
		                  'html'   => $html);
		}
		else{
			$data['text'] = lang('no_more_activities');
		}
	}
	
}
elseif ($action == 'update_user_lastseen') {
	if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
        $db->where('session_id', $_SESSION['user_id'])->update(T_SESSIONS, array('time' => time()));
    } else if (!empty($_COOKIE['user_id']) && !empty($_COOKIE['user_id'])) {
    	$db->where('session_id', $_COOKIE['user_id'])->update(T_SESSIONS, array('time' => time()));
	}
	
	$data = array('status' => 200);
}

elseif ($action == 'get_payment_methods') 
{
    $uObj = new User;
	$context['pay_type'] = '\'pro\'';
    $post_type = (is_array($_POST['type']) ? key($_POST['type']) : $_POST['type']);
	$pay_type  = array('pro', 'standard', 'wallet', 'social_donation');

	if (!empty($post_type) && in_array($post_type, $pay_type)) {
        $context['pay_type']  = '\''.$post_type.'\'';
		$context['pay_price'] = $config[$post_type == 'pro' ? 'pro_price' : 'standard_price'];
	}
    elseif (is_array($_POST['type'])) {
        $context['pay_type']  = '{\''.$post_type.'\':'.$_POST['type'][$post_type].'}';
        $context['pay_price'] = $uObj->listCommunityPlans($_POST['type'][$post_type])['price'] ?? 0;
    }
	$html = $pixelphoto->PX_LoadPage('main/templates/modals/go_pro');
	$data = array('status' => 200,'html' => $html);
}

else if ($action == 'upload_store_image' && IS_LOGGED) {
    $data    = array('status' => 400);
    $me      = $user->user_data;
    if (!empty($_FILES['photo'])) {
        $inserted_data = array();
        $is_ok = false;





        $media = new Media();
        $media->setFile(array(
            'file' => $_FILES['photo']['tmp_name'],
            'name' => $_FILES['photo']['name'],
            'size' => $_FILES['photo']['size'],
            'type' => $_FILES['photo']['type'],
            'allowed' => 'jpeg,jpg,png',
            'crop' => array(),
            'avatar' => false
        ));

        $upload = $media->uploadFile();

        if (!empty($upload['filename'])) {

            $size = getimagesize($upload['filename']);
            if( $size[0] < $config['min_image_width'] || $size[1] < $config['min_image_height'] ){
                @unlink($upload['filename']);
                $media->uploadToFtp($upload['filename'], true);
                $media->uploadToS3($upload['filename'], true);
                $data['message'] = str_replace(array('{0}','{1}'), array($config['min_image_width'],$config['min_image_height']), lang('image_dimension_error')) ;
                echo json_encode($data, JSON_PRETTY_PRINT);
                exit();
            }
            $is_ok = true;
            $inserted_data['full_file'] = $upload['filename'];

            $logo = $config['site_url'] . '/media/img/logo.' . $config['logo_extension'];

            $dir         = "media/upload";
            $generate    = date('Y') . '/' . date('m') . '/' . date('Y') . md5(time()) . date('m') . '_' . date('d') . '_' . md5(time());
            $file_path   = "photos/" . $generate . "_image.jpg";
            $filename    = $dir . '/' . $file_path;
            try {
                $image = new \claviska\SimpleImage();

                $image
                    ->fromFile($upload['filename'])
                    ->autoOrient()
                    ->overlay($logo, $config['watermark_anchor'], $config['watermark_opacity'], 0, 0)
                    ->toFile($filename, 'image/jpeg');

                $inserted_data['small_file'] = $filename;

            } catch(Exception $err) {

                $data['message'] = lang('unknown_error');
            }


        }
        else{
            $data['message'] = lang('your_photo_invalid');
        }

        if ($is_ok == true) {
            $inserted_data['title'] = !empty($_POST['title']) ? Generic::secure($_POST['title']) : '';
            $inserted_data['tags'] = !empty($_POST['tags']) ? Generic::secure($_POST['tags']) : '';
            $inserted_data['license'] = !empty($_POST['license']) ? Generic::secure($_POST['license']) : 'none';
            $inserted_data['price'] = !empty($_POST['price']) ? Generic::secure($_POST['price']) : '0.00';
            $inserted_data['category'] = !empty($_POST['category']) ? Generic::secure($_POST['category']) : '';
            $inserted_data['user_id'] = $me->user_id;
            $inserted_data['created_date'] = time();
            $id = Generic::$db->insert(T_STORE, $inserted_data);
            if ($id > 0) {
                $data['message'] = lang('img_upload_success');
                $data['status'] = 200;
            }
            else{
                $data['message'] = lang('unknown_error');
            }
        }
    }
    else{
        $data['message'] = lang('please_check_details');
    }
}

elseif ($action == 'manifest-modal') {
    $users    = new User;

    $post_data['id']   = Generic::secure($_POST['id']);
    $post_data['page'] = Generic::secure($_POST['page']);

    $response = $users->noticeModalContent(null, true, $post_data['page']);

    $html = '';
    if ($response) {
        foreach ($response as $key => $info) {
            if ($info['status'] !== 'off') { 
                $context['title']   = $info['title'];
                $context['content'] = decode($info['content']);
                $use_title          = $info['use_title'] !== 'off' ? $info['title'] : null;
            }
        }
    } else {
        $context['title']   = 'No content';
        $context['content'] = 'There is no available content';
        $use_title          = null;
    }
    $html .= $pixelphoto->PX_LoadPage('main/templates/includes/manifest-content');

    $config['always_show_manifest'];

    if ($config['always_show_manifest'] != 'never') {
        if ($config['always_show_manifest'] == 'off') {
            $_SESSION['manifest_shown'] = time();
        } elseif ($config['always_show_manifest'] == 'on' && isset($_SESSION['manifest_shown'])) {
            unset($_SESSION['manifest_shown']);
        }
    }

    $data['title']  = $use_title;
    $data['html']   = $html;
    $data['status'] = 200; 
}

elseif ($action == 'verify_account_number') {
    $post_data['account_number'] = Generic::secure($_POST['account_number']);
    $post_data['bank_code']      = Generic::secure($_POST['bank_code']);

    $process = $admin->paystackProcessor('resolve_bank', $post_data)->data; 
    if ($process['status'] == 1 && !empty($process['data']['account_name'])) {
        $data['name']   = $process['data']['account_name']; 
        $data['status'] = 200;  
    } else {
        $data['name']   = 'Account not found, only proceed if you are sure'; 
        $data['status'] = 400;
    }
}

elseif ($action == 'accepted_terms') {
    $data['status'] = 400;  
    if (isset($_SESSION['terms_accepted'])) {
        $data['status']  = 200;  
        $data['message'] = 'Well Accepted';  
    } else {
        $_SESSION['terms_accepted'] = true;
        $data['status'] = 200;  
        $data['message'] = 'Now Accepted'; 
    }
}

exit_xhr:
