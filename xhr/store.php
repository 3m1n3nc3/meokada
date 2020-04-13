<?php
require_once('sys/import3p/PayPal/vendor/autoload.php');
use PayPal\Api\Payer;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Details;
use PayPal\Api\Amount;
use PayPal\Api\Transaction;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;

$paypal = new \PayPal\Rest\ApiContext(
    new \PayPal\Auth\OAuthTokenCredential(
        $config['paypal_id'],
        $config['paypal_secret']
    )
);
$paypal->setConfig(
    array(
        'mode' => $config['paypal_mode']
    )
);

if ($action == 'get_paypal_link' && IS_LOGGED && !empty($config['paypal_id']) && !empty($config['paypal_secret'])) {
    $type = 'store';
    $sum = 0;
    $id = 0;
    $title = '';
    if (!empty($_POST['id']) && is_numeric($_POST['id']) && $_POST['id'] > 0) {
        $id = Generic::secure($_POST['id']);
    }
    if (!empty($_POST['title'])) {
        $title = ' [ '.Generic::secure($_POST['title']). ' ]';
    }
    $dec = "Buy image" . $title;
    if (!empty($_POST['amount']) && is_numeric($_POST['amount']) && $_POST['amount'] > 0) {
        $sum = Generic::secure($_POST['amount']);
    }
    if( $sum > 0 ){
    $payer = new Payer();
    $payer->setPaymentMethod('paypal');
    $item = new Item();
    $item->setName($dec)->setQuantity(1)->setPrice($sum)->setCurrency($config['currency']);
    $itemList = new ItemList();
    $itemList->setItems(array(
        $item
    ));
    $details = new Details();
    $details->setSubtotal($sum);
    $amount = new Amount();
    $amount->setCurrency($config['currency'])->setTotal($sum)->setDetails($details);
    $transaction = new Transaction();
    $transaction->setAmount($amount)->setItemList($itemList)->setDescription($dec)->setInvoiceNumber(time());
    $redirectUrls = new RedirectUrls();
    $redirectUrls->setReturnUrl($config['site_url'] . "/aj/store/get_paid&success=1&amount=".$sum."&id=".$id)->setCancelUrl($config['site_url']);
    $payment = new Payment();
    $payment->setIntent('sale')->setPayer($payer)->setRedirectUrls($redirectUrls)->setTransactions(array(
        $transaction
    ));
    try {
        $payment->create($paypal);
    }
    catch (Exception $e) {
        $data = array(
            'status' => 400,
            'message' => json_decode($e->getData())
        );
        if (empty($data['message'])) {
            $data['message'] = json_decode($e->getCode());
        }
    }
    if (empty($data['message'])) {
        $data = array(
            'status' => 200,
            'url' => $payment->getApprovalLink()
        );
    }

    }else{
        $data = array(
            'status' => 400,
            'message' => ''
        );
    }

}
else if ($action == 'get_paid' && IS_LOGGED && !empty($config['paypal_id']) && !empty($config['paypal_secret']) && $_GET['success'] == 1 && !empty($_GET['paymentId']) && !empty($_GET['PayerID'])) {
    $paymentId = $_GET['paymentId'];
    $PayerID = $_GET['PayerID'];

    $amount = Generic::secure($_GET['amount']);
    $id = Generic::secure($_GET['id']);

    $payment = Payment::get($paymentId, $paypal);
    $execute = new PaymentExecution();
    $execute->setPayerId($PayerID);
    $error = '';
    try {
        $result = $payment->execute($execute, $paypal);
    }
    catch (Exception $e) {
        $error = json_decode($e->getData(), true);
    }

    if (empty($error)) {
//        $datax = array(
//            'paymentId' => $paymentId,
//            'PayerID' => $PayerID,
//            'payment' => $payment,
//            'result' => $result,
//            'user_id' => $me['user_id'],
//            'amount' => $amount,
//            'id' => $id,
//        );
//        var_dump($datax);

        $store_image = $db->arrayBuilder()->where('id',$id)->getOne(T_STORE);
        $u = $db->arrayBuilder()->where('user_id',$store_image['user_id'])->getOne(T_USERS);
        $commesion = $amount / 2;
        $wallet = $u['balance'] + $commesion;
        $update = $user->updateStatic($store_image['user_id'],array('balance' => $wallet));
        $db->insert(T_TRANSACTIONS,array(
            'user_id'       => $me['user_id'],
            'amount'        => $amount,
            'type'          => 'store',
            'item_store_id' => $id,
            'admin_com'     => $commesion,
            'time'          => time())
        );

        $db->where('id',$id)->update(T_STORE, array( 'sells' => $db->inc(1)));

        $notif   = new Notifications();


        $re_data = array(
            'notifier_id' => $me['user_id'],
            'recipient_id' => $store_image['user_id'],
            'type' => 'store_purchase',
            'url' => $config['site_url'] . "/store/".$id,
            'time' => time()
        );
        try {
            $notif->notify($re_data);
        } catch (Exception $e) {
        }

        header("Location: " . $config['site_url'] . "/store/".$id);
        exit();
    }
    else{
        header("Location: " . $config['site_url'] . "/oops");
        exit();
    }
}

elseif($action == 'explore-user-store' && IS_LOGGED && ($config['image_sell_system'] == 'on')) {
    if (!empty($_GET['offset']) && is_numeric($_GET['offset'])) {
        $last_id      = Generic::secure($_GET['offset']);
        $context['images']  = array();
        $store_images = $db->arrayBuilder()->where('user_id',$context['user']['user_id'])->where('id', $last_id , '<')->orderBy('id','DESC')->get(T_STORE,20);
        foreach ($store_images as $key => $image_data) {
            $image_data['post_id'] = $image_data['id'];
            $image_data['type'] = 'image';
            $image_data['thumb'] = $image_data['small_file'];
            $image_data['boosted'] = 0;
            $image_data['avatar'] = $context['user']['avatar'];
            $image_data['username'] = $context['user']['username'];
            $image_data['category_name'] = $context['lang'][$image_data['category']];
            $image_data['text_time'] = time2str($image_data['created_at']);
            $context['images'][]    = $image_data;
        }
        $data['status'] = 404;
        $data['html']   = "";
        $context['app_name'] = 'store';
        if (!empty($store_images)) {
            foreach ($context['images'] as $key => $post_data) {
                $data['html']    .= $pixelphoto->PX_LoadPage('store/templates/store/includes/list-item');
            }
            $data['status'] = 200;
        }
    }
} else if($action == 'explore-all-store' && IS_LOGGED && ($config['image_sell_system'] == 'on')) {
    if (isset( $_GET['offset'])) {
        $last_id      = ( isset( $_GET['offset'] ) ) ? (int)Generic::secure($_GET['offset']) : 0;
        $sort         = ( isset( $_GET['sort'] ) ) ? Generic::secure($_GET['sort']) : 'id';

        if( isset( $_GET['mode']) && !empty( $_GET['mode']) ){
            if( $_GET['mode'] == 'search' ){

                if( isset( $_GET['search_title']) && !empty( $_GET['search_title']) ){
                    $db->where('title',"%".Generic::secure($_GET['search_title'])."%",'LIKE');
                }
                if( isset( $_GET['search_tags']) && !empty( $_GET['search_tags']) ){
                    $db->where('tags',"%".Generic::secure($_GET['search_tags'])."%",'LIKE');
                }
                if( isset( $_GET['search_category']) && !empty( $_GET['search_category']) ){
                    $db->where('category',Generic::secure($_GET['search_category']));
                }
                if( isset( $_GET['search_license']) && !empty( $_GET['search_license']) ){
                    $db->where('license',Generic::secure($_GET['search_license']));
                }
                if( isset( $_GET['search_min']) && !empty( $_GET['search_min']) ){
                    $db->where('price',(int)Generic::secure($_GET['search_min']) , ">=");
                }
                if( isset( $_GET['search_max']) && !empty( $_GET['search_max']) ){
                    $db->where('price',(int)Generic::secure($_GET['search_max']) , "<=");
                }

            }
        }
        if($sort == 'popularity_desc'){
            if (isset( $_GET['last_views'] ) ) {
                if( !isset( $_GET['scroll'] ) ) {
                    $db->orderBy('id', 'DESC');
                }else{
                    $ids = array_unique($_GET['viewed_store_ids'], SORT_REGULAR);
                    $db->orderBy('views', 'DESC')->orderBy('id', 'DESC')->where('id', $ids , 'NOT IN')->having('views', Generic::secure($_GET['last_views']) , '<=');
                }
            }
        }elseif($sort == 'popularity_asc'){
            if (isset( $_GET['last_views'] ) ) {
                if( !isset( $_GET['scroll'] ) ) {
                    $db->orderBy('id', 'ASC');
                }else{
                    $ids = array_unique($_GET['viewed_store_ids'], SORT_REGULAR);
                    $db->orderBy('views', 'ASC')->orderBy('id', 'ASC')->where('id', $ids , 'NOT IN')->having('views', Generic::secure($_GET['last_views']) , '>=');
                }
            }
        }elseif($sort == 'downloads_desc'){
            if (isset( $_GET['last_download'] ) ) {
                if( !isset( $_GET['scroll'] ) ) {
                    $db->orderBy('id', 'DESC');
                }else{
                    $ids = array_unique($_GET['viewed_store_ids'], SORT_REGULAR);
                    $db->orderBy('downloads', 'DESC')->orderBy('id', 'DESC')->where('id', $ids , 'NOT IN')->having('downloads', Generic::secure($_GET['last_download']) , '<=');
                }
            }
        }elseif($sort == 'downloads_asc'){
            if (isset( $_GET['last_download'] ) ) {
                if( !isset( $_GET['scroll'] ) ) {
                    $db->orderBy('id', 'ASC');
                }else{
                    $ids = array_unique($_GET['viewed_store_ids'], SORT_REGULAR);
                    $db->orderBy('downloads', 'ASC')->orderBy('id', 'ASC')->where('id', $ids , 'NOT IN')->having('downloads', Generic::secure($_GET['last_download']) , '>=');
                }
            }
        }elseif($sort == 'sales_desc'){
            if (isset( $_GET['last_sells'] ) ) {
                if( !isset( $_GET['scroll'] ) ) {
                    $db->orderBy('id', 'DESC');
                }else{
                    $ids = array_unique($_GET['viewed_store_ids'], SORT_REGULAR);
                    $db->orderBy('sells', 'DESC')->orderBy('id', 'DESC')->where('id', $ids , 'NOT IN')->having('sells', Generic::secure($_GET['last_sells']) , '<=');
                }
            }
        }elseif($sort == 'sales_asc'){
            if (isset( $_GET['last_sells'] ) ) {
                if( !isset( $_GET['scroll'] ) ) {
                    $db->orderBy('id', 'ASC');
                }else{
                    $ids = array_unique($_GET['viewed_store_ids'], SORT_REGULAR);
                    $db->orderBy('sells', 'ASC')->orderBy('id', 'ASC')->where('id', $ids , 'NOT IN')->having('sells', Generic::secure($_GET['last_sells']) , '>=');
                }
            }
        }elseif($sort == 'date_desc'){
            if (isset( $_GET['last_date'] ) ) {
                if( !isset( $_GET['scroll'] ) ) {
                    $db->orderBy('id', 'DESC');
                }else{
                    $ids = array_unique($_GET['viewed_store_ids'], SORT_REGULAR);
                    $db->orderBy('created_date', 'DESC')->orderBy('id', 'DESC')->where('id', $ids , 'NOT IN')->having('created_date', Generic::secure($_GET['last_date']) , '<=');
                }
            }
        }elseif($sort == 'date_asc'){
            if (isset( $_GET['last_date'] ) ) {
                if( !isset( $_GET['scroll'] ) ) {
                    $db->orderBy('id', 'ASC');
                }else{
                    $ids = array_unique($_GET['viewed_store_ids'], SORT_REGULAR);
                    $db->orderBy('created_date', 'ASC')->orderBy('id', 'ASC')->where('id', $ids , 'NOT IN')->having('created_date', Generic::secure($_GET['last_date']) , '>=');
                }
            }
        }

        if($sort == 'id' && $last_id > 0){
            $db->orderBy('id','DESC')->where('id', $last_id , '<');
        }

        $context['images']  = array();
        $store_images = $db->get(T_STORE,6);
        foreach ($store_images as $key => $image_data) {
            $image_data = o2array($image_data);

            $image_data['post_id'] = $image_data['id'];
            $image_data['type'] = 'image';
            $image_data['thumb'] = $image_data['small_file'];
            $image_data['boosted'] = 0;

            $_user_data = $user->getUserDataById($image_data['user_id']);
            $_user_data = o2array($_user_data);

            $image_data['avatar'] = $_user_data['avatar'];
            $image_data['username'] = $_user_data['username'];
            $image_data['category_name'] = $context['lang'][$image_data['category']];
            $image_data['text_time'] = time2str($image_data['created_at']);
            $context['images'][]    = $image_data;
        }
        $data['status'] = 404;
        $data['html']   = "";
        $context['app_name'] = 'store';
        if (!empty($store_images)) {
            foreach ($context['images'] as $key => $post_data) {
                $data['html']    .= $pixelphoto->PX_LoadPage('store/templates/store/includes/list-item');
            }
            $data['status'] = 200;
            $data['last_id'] = end($context['images'])['id'];
            $data['data'] = $context['images'];
        }else{
            if( isset( $_GET['mode']) && !empty( $_GET['mode']) ) {
                if ($_GET['mode'] == 'search') {
                    $data['status'] = 200;
                    $data['html'] = $pixelphoto->PX_LoadPage('blog/templates/blog/includes/no-articles-found');
                }
            }
        }
    }
}else if($action == 'lightbox') {
    if ((!empty($_GET['post_id']) && is_numeric($_GET['post_id']))) {

        $post_id = $_GET['post_id'];
        $page    = (!empty($_GET['page'])) ? $_GET['page'] : false;

        $context['images']  = array();
        $store_images = $db->arrayBuilder()->where('id',$post_id)->getOne(T_STORE);
        $store_images['category_name'] = $context['lang'][$store_images['category']];
        $store_images['text_time'] = time2str($store_images['created_at']);

        $_user_data = $user->getUserDataById($store_images['user_id']);
        $_user_data = o2array($_user_data);

        $store_images['avatar'] = $_user_data['avatar'];
        $store_images['username'] = $_user_data['username'];
        $store_images['is_verified'] = $_user_data['verified'];

        list($_width, $_height, $_type) = getimagesize($store_images['full_file']);
        $store_images['dimensions'] = $_width . 'px X ' . $_height . 'px';

        $types_array = array(
            '1' => 'GIF',
            '2' => 'JPG',
            '3' => 'PNG',
            '4' => 'SWF',
            '5' => 'PSD',
            '6' => 'BMP',
            '7' => 'TIFF',
            '8' => 'TIFF',
            '9' => 'JPC',
            '10' => 'JP2',
            '11' => 'JPX',
            '12' => 'JB2',
            '13' => 'SWC',
            '14' => 'IFF',
            '15' => 'WBMP',
            '16' => 'XBM'
        );
        $store_images['ext'] = $types_array[$_type];

        $data['status'] = 404;
        $data['html']   = "";
        if (!empty($store_images) && !empty($page)) {
            $context['post_data'] = $store_images;
            $data['html'] = $pixelphoto->PX_LoadPage('store/templates/store/includes/lightbox');
            $data['status'] = 200;
        }
    }
}