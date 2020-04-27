<?php 

class Admin extends User{
	public $pages = array();
	public $currp = 'dashboard';

	public function loadPage($path = ""){
		global $admin,$context,$config,$me;
		$admin = $this;
		$path  = self::secure($path);
		$cd    = $context; #short name of context data array
	    $page  = "admin-panel/pages/$path.phtml";

	    if (!file_exists($page)) {
	        die("File not Exists : $page");
	    }
		
	    $content = '';
	    ob_start();
	    require($page);
	    $content = ob_get_contents();
	    ob_end_clean();
	    return $content;
	}

	public function totalUsers(){
		return self::$db->getValue(T_USERS,'COUNT(`user_id`)');
	}

	public function totalPosts(){
		return self::$db->getValue(T_POSTS,'COUNT(`post_id`)');
	}

	public function totalVideos(){
		self::$db->where('type',array('youtube','vimeo','dailymotion','video'),"IN");
		return self::$db->getValue(T_POSTS,'COUNT(`post_id`)');
	}

	public function totalPhotos(){
		self::$db->where('type',array('image','gif'),"IN");
		return self::$db->getValue(T_POSTS,'COUNT(`post_id`)');
	}

	public function totalLikes(){
		return self::$db->getValue(T_POST_LIKES,'COUNT(`id`)');
	}

	public function onlineUsers(){
		self::$db->where('last_seen',(time() - 60),'>');
		return self::$db->getValue(T_USERS,'COUNT(`user_id`)');
	}

	public function totalComments(){
		return self::$db->getValue(T_POST_COMMENTS,'COUNT(`id`)');
	}

	public function totalMessages(){
		return self::$db->getValue(T_MESSAGES,'COUNT(`id`)');
	}

	public function userStatistic(){
		$months    = range(1, 12);
		$usr_statc = array();
		$date      = date('Y');
		foreach ($months as $value) {
	       $monthNum    = $value;
	       $dateObj     = DateTime::createFromFormat('!m', $monthNum);
	       $monthName   = $dateObj->format('F');
	       $new_users   = self::$db->where('registered', "$date/$value")->getValue(T_USERS, 'COUNT(`user_id`)');
	       $usr_statc[] = array('month' => $monthName, 'new_users' => $new_users);
	    }

	    return json_encode($usr_statc);
	}

	public function postStatistic(){
		$months    = range(1, 12);
		$pst_statc = array();
		$date      = date('Y');

		foreach ($months as $value) {
	       $monthNum    = $value;
	       $dateObj     = DateTime::createFromFormat('!m', $monthNum);
	       $monthName   = $dateObj->format('F');
	       $new_posts   = self::$db->where('registered', "$date/$value")->getValue(T_POSTS, 'COUNT(`user_id`)');
	       $pst_statc[] = array('month' => $monthName, 'new_posts' => $new_posts);
	    }

		return json_encode($pst_statc);
	}

	public function activeMenu($page = 'dashboard'){
		if ($page == $this->currp) {
			return 'active';
		}

		elseif (in_array($page, array_keys($this->pages)) && in_array($this->currp, $this->pages[$page])) {
			return 'active';
		}

		elseif ($page == $this->currp) {
			return 'active';
		}
	}

	public function updateSettings($up_data = array()){
		if (empty($up_data)) {
			return false;
		}

		foreach ($up_data as $name => $value) {
			try {
				self::$db->where('name', $name)->update(T_CONFIG,array('value' => $value));
			} 
			catch (Exception $e) {
				return false;
			}
		}

		return true;
	}

	public function updateCommunityChallengeSettings($up_data = array()){
		if (empty($up_data)) {
			return false;
		} 
		try {
			self::$db->where('id', $up_data['id'])->update(T_COMMUNITY, $up_data);
		} 
		catch (Exception $e) {
			return false;
		} 

		return true;
	}

	function Pxp_UploadLogo($data = array(),$type = 'logo') {
	    if (isset($data['file']) && !empty($data['file'])) {
	        $data['file'] =  self::secure($data['file']);
	    }
	    if (isset($data['name']) && !empty($data['name'])) {
	        $data['name'] =  self::secure($data['name']);
	    }
	    if (isset($data['name']) && !empty($data['name'])) {
	        $data['name'] =  self::secure($data['name']);
	    }
	    if (empty($data)) {
	        return false;
	    }
	    $allowed           = 'jpg,png,jpeg,gif';
	    $new_string        = pathinfo($data['name'], PATHINFO_FILENAME) . '.' . strtolower(pathinfo($data['name'], PATHINFO_EXTENSION));
	    $extension_allowed = explode(',', $allowed);
	    $file_extension    = pathinfo($new_string, PATHINFO_EXTENSION);
	    if (!in_array($file_extension, $extension_allowed)) {
	        return false;
	    }
	    $dir      = "media/img/";
	    if ($type == 'fav') {
	    	$filename = $dir . "icon.{$file_extension}";
		    if (move_uploaded_file($data['file'], $filename)) {
		        if ($this->updateSettings(array('favicon_extension' => $file_extension))) {
		            return true;
		        }
		    }
	    } else if ($type == 'logo-light') {
            $filename = $dir . "light-logo.{$file_extension}";
            if (move_uploaded_file($data['file'], $filename)) {
                if ($this->updateSettings(array('logo_extension' => $file_extension))) {
                    return true;
                }
            }
        }
	    else{
	    	$filename = $dir . "logo.{$file_extension}";
		    if (move_uploaded_file($data['file'], $filename)) {
		        if ($this->updateSettings(array('logo_extension' => $file_extension))) {
		            return true;
		        }
		    }
	    }
	}

	public function payUser($action = '', $user_data = array())
	{
		global $admin,$context,$config,$me;

        $data = array( 
            'currency'       =>  $config['currency'],
            'source'         => 'balance',
            'reason'         => 'Transfer from ' . $config['site_name'] 
        );

        $user_data = array_merge($user_data, $data);

        $process = array();

        if ($user_data) 
        {
			$process = $this->paystackProcessor($action, $user_data)->data;
        }
		return $process;
	} 

	public function paystackProcessor($action = 'balance', $post_data = array()) 
	{
		global $admin,$context,$config,$me;

    	require_once('sys/import3p/Paystack/src/autoload.php'); 

        $secret = $config['paystack_secret'];
        $paystack = new Yabacon\Paystack($secret);

        $data = array();
        // print_r($post_data);
        
        $domain_path = pathinfo($_SERVER['SERVER_NAME'], PATHINFO_FILENAME);
	    $arr = array("localhost","127.0.0.1","::1", $domain_path.".te", $domain_path.".test");

	    if( !in_array( $_SERVER['SERVER_NAME'], $arr ) OR $config['offline_access'] == 'on' ){
	        if ($action == 'balance') 
	        {
	        	$response = $paystack->balance->getList();
	        	if ($response) 
	        	{
	        		// ResultSet: [currency], [balance]
	        		$data = self::toArray($response);
	        	}
	        }
	        elseif ($action == 'bank') 
	        {
	        	$response = $paystack->bank->getList();
	        	if ($response) 
	        	{
	        		// ResultSet:  [name], [slug], [code], [longcode], [gateway], [pay_with_bank], 
	        		// [active], [is_deleted], [country], [currency], [type], [id], [createdAt], [updatedAt]
	        		$data = self::toArray($response);
	        	}
	        }
	        elseif ($action == 'create_recipient') 
	        {
	        	$response = $paystack->transferrecipient->create($post_data);
	        	if ($response) 
	        	{
	        		// DataSet: [type], [name], [description], [account_number], [bank_code], [currency]

	        		// ResultSet:   [active], [createdAt], [currency], [description], [domain], [email], [id],
	        		// [integration], [metadata], [name], [recipient_code], [type], [updatedAt], [is_deleted],
	        		// [details] => ( [authorization_code], [account_number], [account_name], [bank_code], [bank_name] )
	        		$data = self::toArray($response);
	        	}
	        }
	        elseif ($action == 'list_recipient') 
	        {
	        	$response = $paystack->transferrecipient->getList();
	        	if ($response) 
	        	{ 
	        		$data = self::toArray($response);
	        	}
	        }
	        elseif ($action == 'initiate_transfer') 
	        {
	        	$response = $paystack->transfer->initiate($post_data); 
	        	if ($response) 
	        	{
	        		$data = self::toArray($response);
	        	}
	        }
	        elseif ($action == 'initiate_bulk_transfer') 
	        {
	        	$response = $paystack->transfer->initiateBulk($post_data); 
	        	if ($response) 
	        	{
	        		$data = self::toArray($response);
	        	}
	        }
	        elseif ($action == 'otp_state' || isset($action['otp'])) 
	        { 
	        	if (isset($action['otp'])) 
	        	{
	        		if (is_numeric($action['otp']))
	        		{
	        			$response = $paystack->transfer->finalizeTransfer($action);
	        		}
	        		else
	        		{ 
	        			$response = $paystack->transfer->resendOtp($action);
	        		}
	        	}
	        	elseif ($post_data && is_numeric($post_data))
	        	{ 
	        		$response = $paystack->transfer->disableOtpFinalize(['otp' => $post_data]);
	        	} 
	        	elseif ($post_data && $post_data == 'enable') 
	        	{
	        		$response = $paystack->transfer->enableOtp();
	        	}
	        	else
	        	{
	        		$response = $paystack->transfer->disableOtp();
	        	}

	        	if ($response) 
	        	{ 
	        		$data = self::toArray($response);
	        	}
	        }  
       	}

        $this->data = $data;

        return $this;
	}
}
