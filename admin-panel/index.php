<?php 

if (empty($_GET['page'])) {
    header("Location: $site_url/admin-panel/dashboard");
    exit();
}
$page  = 'dashboard';
$admin = new Admin();

$admin_role = $context['user']['admin'];

if (!empty($_GET['page'])) {
    $page = $admin::secure($_GET['page']);
}

$context['theme_url'] = $site_url.'/apps/'.$config['theme']; 
$context['me']        = o2array($admin->getLoggedInUser());

$page_content = '';
$pages        = array(
    'settings' => array(
        'general-settings', 
        'site-settings',
        'social-login',
        'email-settings',
        's3',
        'ads',
        'playtube_support',
        'payment-settings',
        'social-wallet'
    ),
    'users' => array(
        'manage-users',
        'payment-requests',
        'manage-verification-requests',
        'blacklist',
        'affiliates-settings',
        'manage-referrers',
        'manage-business-requests',
        'manage_fundings'
    ),
    'pro_system' => array(
        'pro-settings',
        'coupons',
        'manage-pro-users'
    ),
    'advertisement' => array(
        'ads-settings',
        'manage-ads'
    ),
    'bank-receipts' => array(
        'bank-receipts'
    ),
    'earnings' => array(
        'earnings'
    ),
    'posts' => array(
        'manage-posts',
        'manage-challenges',
        'manage-challengers',
        'challenge-winners'
    ),
    'blogs' => array(
        'manage-articles',
        'manage-blog-categories',
        'add-new-article',
        'edit-article'
    ),
    'image_store' => array(
        'manage-store-categories',
        'manage-store-items',
        'store-revenue'
    ),
    'design' => array(
        'themes',
        'manage-site-design'
    ),
    'reports' => array(
        'profile-reports',
        'post-reports',
    ),
    'backup' => array(
        'create-backup',
    ),
    'logs' => array(
        'changelogs',
    ),
    'langs' => array(
        'manage-langs',
        'edit-language',
        'add-language',
    ),
    'pages' => array(
        'modal',
        'modal-writer',
        'terms',
        'privacy-and-policy',
        'about-us',
        'disclaimer',
        'contact_us'
    ),
    'mobile_api' => array(
        'api_keys',
        'push-notifications-system'
    )
);


$admin->pages = $pages;

foreach ($pages as $tab) {
    if (in_array($page, $tab) || $page == 'dashboard') {
        $page_content = $admin->loadPage("$page/content");
        $admin->currp = $page;
        break;
    }
}

if (empty($page_content)) {
    header("Location: $site_url/404");
    exit();
}

?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=Edge">
        <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
        <title>
            Admin Panel
        </title>
        
        <link rel="shortcut icon" type="image/png" href="<?php echo($config['site_url']); ?>/media/img/icon.<?php echo($config['favicon_extension']); ?>"/> 
        
        <link href="https://fonts.googleapis.com/css?family=Roboto:400,700&subset=latin,cyrillic-ext" rel="stylesheet" type="text/css">
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" type="text/css"> 
        <link href="<?php echo pxp_acp_link('plugins/bootstrap/css/bootstrap.css');?>" rel="stylesheet">
        <link href="<?php echo pxp_acp_link('plugins/node-waves/waves.css');?>" rel="stylesheet" /> 
        <link href="<?php echo pxp_acp_link('plugins/animate-css/animate.css');?>" rel="stylesheet" />
        <link href="<?php echo pxp_acp_link('plugins/morrisjs/morris.css');?>" rel="stylesheet" />
        <link href="<?php echo pxp_acp_link('css/style.css');?>" rel="stylesheet"> 
        <link href="<?php echo pxp_acp_link('css/themes/all-themes.css');?>" rel="stylesheet" />
        <link href="<?php echo pxp_acp_link('plugins/bootstrap-toggle/bootstrap-toggle.min.css');?>" rel="stylesheet" />
        <link href="<?php echo pxp_acp_link('plugins/jquery-datatable/skin/bootstrap/css/dataTables.bootstrap.css'); ?>" rel="stylesheet">
        <link href="<?php echo pxp_acp_link('plugins/bootstrap-select/css/bootstrap-select.css');?>" rel="stylesheet" />
        <link href="<?php echo pxp_acp_link('plugins/jquery-ui/jquery-ui.min.css');?>" rel="stylesheet" />
        <link href="<?php echo $context['theme_url'] ?>/main/static/js/libs/toast/src/jquery.m.toast.css">  
        <script src="<?php echo pxp_acp_link('plugins/jquery/jquery.min.js');?>"></script>
        <script src="<?php echo pxp_acp_link('plugins/jquery-ui/jquery-ui.min.js');?>"></script>
        <script>
            function acpajax_url(path) {
                return '<?php echo($config['site_url']); ?>/aj/admin/' + path;
            }
            function link(path){
                var url = xhr_url() + path;
                return url;
            }
            function xhr_url(){
                return '<?php echo($config['site_url']); ?>/aj/';
            }
        </script>
        <style type="text/css">
            .sort-highlight {
              background: #f8f9fa;
              border: 1px dashed #dee2e6;
              margin-bottom: 10px;
            }
        </style>
    </head>

    <body class="theme-red">   
        <div class="page-loader-wrapper">
            <div class="loader">
                <div class="preloader">
                    <div class="spinner-layer pl-red">
                        <div class="circle-clipper left">
                            <div class="circle"></div>
                        </div>
                        <div class="circle-clipper right">
                            <div class="circle"></div>
                        </div>
                    </div>
                </div>
                <p>Please wait...</p>
            </div>
        </div>
        
        
        <div class="overlay"></div>
        
        
        <div class="search-bar">
            <div class="search-icon">
                <i class="material-icons">search</i>
            </div>
            <input type="text" placeholder="START TYPING...">
            <div class="close-search">
                <i class="material-icons">close</i>
            </div>
        </div>
        
        
        <nav class="navbar">
            <div class="container-fluid">
                <div class="navbar-header">
                    <a href="javascript:void(0);" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse" aria-expanded="false"></a>
                    <a href="javascript:void(0);" class="bars"></a>
                    <a class="navbar-brand" href="<?php echo $config['site_url']; ?>">
                        Welcome to <strong><?php echo $config['site_name']; ?></strong> Administration panel
                    </a>
                </div>
            </div>
        </nav>
        
        <section>
            <aside id="leftsidebar" class="sidebar">       
                <div class="user-info">
                    <div class="image">
                        <img src="<?php echo $me['avatar']; ?>" width="48" height="48" alt="<?php echo $me['name']; ?>" />
                    </div>
                    <div class="info-container">
                        <div class="name" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?php echo $me['username']; ?></div>
                        <div class="email"><?php echo $me['email']; ?></div>
                        <div class="btn-group user-helper-dropdown">
                            <i class="material-icons" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">keyboard_arrow_down</i>
                            <ul class="dropdown-menu pull-right">
                                <li>
                                    <a href="<?php echo $me['url']; ?>">
                                        <i class="material-icons">person</i> Profile
                                    </a>
                                </li>
                                <li role="seperator" class="divider"></li>
                                <li>
                                    <a href="<?php echo $me['url']; ?>/followers">
                                        <i class="material-icons">group</i> Followers
                                    </a>
                                </li>
                                <li role="seperator" class="divider"></li>
                                <li>
                                    <a href="<?php echo $site_url; ?>/signout">
                                        <i class="material-icons">input</i> Sign Out
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="menu">
                    <ul class="list">
                        <li class="<?php echo $admin->activeMenu('dashboard'); ?>">
                            <a href="<?php echo pxp_acp_link('dashboard');?>">
                                <i class="material-icons">collections</i>
                                <span>Dashboard</span>
                            </a>
                        </li>

                        <li class="<?php echo $admin->activeMenu('settings'); ?>">
                            <a href="javascript:void(0);" class="menu-toggle">
                                <i class="material-icons">settings</i>
                                <span>Settings</span>
                            </a>
                            <ul class="ml-menu">
                                
                                <?php if (admin_access($admin_role, 3)):?>
                                <li class="<?php echo $admin->activeMenu('general-settings'); ?>">
                                    <a href="<?php echo pxp_acp_link('general-settings'); ?>" class=" waves-effect waves-block">General Settings</a>
                                </li>
                                <li class="<?php echo $admin->activeMenu('site-settings'); ?>">
                                    <a href="<?php echo pxp_acp_link('site-settings'); ?>" class=" waves-effect waves-block">Site Settings</a>
                                </li>
                                <li class="<?php echo $admin->activeMenu('email-settings'); ?>">
                                    <a href="<?php echo pxp_acp_link('email-settings'); ?>" class=" waves-effect waves-block">E-mail Settings</a>
                                </li>
                                <li class="<?php echo $admin->activeMenu('social-login'); ?>">
                                    <a href="<?php echo pxp_acp_link('social-login'); ?>" class=" waves-effect waves-block">Social Login Settings</a>
                                </li> 
                                <li class="<?php echo $admin->activeMenu('s3'); ?>">
                                    <a href="<?php echo pxp_acp_link('s3'); ?>" class=" waves-effect waves-block">Storage Settings</a>
                                </li> 
                                <?php endif;?>

                                <li class="<?php echo $admin->activeMenu('ads'); ?>">
                                    <a href="<?php echo pxp_acp_link('ads'); ?>" class=" waves-effect waves-block">Ads Settings</a>
                                </li>
                                
                                <?php if (admin_access($admin_role, 3)):?>
                                <li class="<?php echo $admin->activeMenu('playtube_support'); ?>">
                                    <a href="<?php echo pxp_acp_link('playtube_support'); ?>" class=" waves-effect waves-block">Playtube Support</a>
                                </li> 
                                <li class="<?php echo $admin->activeMenu('payment-settings'); ?>">
                                    <a href="<?php echo pxp_acp_link('payment-settings'); ?>" class=" waves-effect waves-block">Payment Settings</a>
                                </li> 
                                <li class="<?php echo $admin->activeMenu('social-wallet'); ?>">
                                    <a href="<?php echo pxp_acp_link('social-wallet'); ?>" class=" waves-effect waves-block">Social Wallet Settings</a>
                                </li> 
                                <?php endif;?>
                            </ul>
                        </li>

                        <?php if (admin_access($admin_role, 3)):?>
                        <li class="<?php echo $admin->activeMenu('langs'); ?>">
                            <a href="javascript:void(0);" class="menu-toggle">
                                <i class="material-icons">language</i>
                                <span>Languages</span>
                            </a>
                            <ul class="ml-menu">    
                                <li class="<?php echo $admin->activeMenu('manage-langs'); ?>">
                                    <a href="<?php echo pxp_acp_link('manage-langs'); ?>" class="waves-effect waves-block">
                                        Manage languages
                                    </a>
                                </li>    
                                <li class="<?php echo $admin->activeMenu('add-language'); ?>">
                                    <a href="<?php echo pxp_acp_link('add-language'); ?>" class="waves-effect waves-block">
                                        Add language & keys
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <?php endif;?>

                        <li class="<?php echo $admin->activeMenu('users'); ?>">
                            <a href="javascript:void(0);" class="menu-toggle">
                                <i class="material-icons">account_circle</i>
                                <span>Users</span>
                            </a>
                            <ul class="ml-menu">
                                <li class="<?php echo $admin->activeMenu('manage-users'); ?>">
                                    <a href="<?php echo pxp_acp_link('manage-users'); ?>" class="waves-effect waves-block">
                                        Manage users
                                    </a>
                                </li>
                                <li class="<?php echo $admin->activeMenu('payment-requests'); ?>">
                                    <a href="<?php echo pxp_acp_link('payment-requests'); ?>" class="waves-effect waves-block">
                                        Payment Requests
                                    </a>
                                </li>
                                <li class="<?php echo $admin->activeMenu('manage-verification-requests'); ?>">
                                    <a href="<?php echo pxp_acp_link('manage-verification-requests'); ?>" class="waves-effect waves-block">
                                        Manage Verification Requests
                                    </a>
                                </li>
                                <li class="<?php echo $admin->activeMenu('manage-business-requests'); ?>">
                                    <a href="<?php echo pxp_acp_link('manage-business-requests'); ?>" class="waves-effect waves-block">
                                        Manage Business Requests
                                    </a>
                                </li> 
                                <li class="<?php echo $admin->activeMenu('manage_fundings'); ?>">
                                    <a href="<?php echo pxp_acp_link('manage_fundings'); ?>" class="waves-effect waves-block">
                                        Manage Fundings
                                    </a>
                                </li>
                                <li class="<?php echo $admin->activeMenu('blacklist'); ?>">
                                    <a href="<?php echo pxp_acp_link('blacklist'); ?>" class="waves-effect waves-block">
                                        Black List
                                    </a>
                                </li>
                                <li class="<?php echo $admin->activeMenu('manage-referrers'); ?>">
                                    <a href="<?php echo pxp_acp_link('manage-referrers'); ?>" class="waves-effect waves-block">
                                        View Referrers
                                    </a>
                                </li>
                                
                                <?php if (admin_access($admin_role, 3)):?>
                                <li class="<?php echo $admin->activeMenu('affiliates-settings'); ?>">
                                    <a href="<?php echo pxp_acp_link('affiliates-settings'); ?>" class="waves-effect waves-block">
                                        Affiliates Settings
                                    </a>
                                </li>
                                <?php endif;?>
                            </ul>
                            
                        </li>
                        <li class="<?php echo $admin->activeMenu('pro_system'); ?>">
                            <a href="javascript:void(0);" class="menu-toggle">
                                <i class="material-icons">stars</i>
                                <span>Premium System</span>
                            </a>
                            <ul class="ml-menu">
                                <?php if (admin_access($admin_role, 3)):?>
                                <li class="<?php echo $admin->activeMenu('pro-settings'); ?>">
                                    <a href="<?php echo pxp_acp_link('pro-settings'); ?>" class="waves-effect waves-block">
                                        Pro Settings
                                    </a>
                                </li>
                                <?php endif;?>

                                <li class="<?php echo $admin->activeMenu('manage-pro-users'); ?>">
                                    <a href="<?php echo pxp_acp_link('manage-pro-users'); ?>" class="waves-effect waves-block">
                                        Manage Premium Users
                                    </a>
                                </li>
                                <li class="<?php echo $admin->activeMenu('manage-pro-users'); ?>">
                                    <a href="<?php echo pxp_acp_link('coupons'); ?>" class="waves-effect waves-block">
                                        Manage Premium Coupons
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="<?php echo $admin->activeMenu('advertisement'); ?>">
                            <a href="javascript:void(0);" class="menu-toggle">
                                <i class="material-icons">attach_money</i>
                                <span>Advertisement</span>
                            </a>
                            <ul class="ml-menu">
                                <li class="<?php echo $admin->activeMenu('manage-ads'); ?>">
                                    
                                    <a href="<?php echo pxp_acp_link('manage-ads'); ?>" class="waves-effect waves-block">
                                        Manage Ads
                                    </a>
                                </li>

                                <?php if (admin_access($admin_role, 3)):?>
                                <li class="<?php echo $admin->activeMenu('ads-settings'); ?>">
                                    <a href="<?php echo pxp_acp_link('ads-settings'); ?>" class="waves-effect waves-block">
                                        Ads Settings
                                    </a>
                                </li>
                                <?php endif;?>
                            </ul>
                        </li>

                        <li class="<?php echo $admin->activeMenu('bank-receipts'); ?>">
                            <a href="<?php echo pxp_acp_link('bank-receipts');?>">
                                <i class="material-icons">credit_card</i>
                                <span>Manage bank receipts</span>
                            </a>
                        </li>
                        <li class="<?php echo $admin->activeMenu('earnings'); ?>">
                            <a href="<?php echo pxp_acp_link('earnings');?>">
                                <i class="material-icons">bar_chart</i>
                                <span>Earnings</span>
                            </a>
                        </li>
                        <li class="<?php echo $admin->activeMenu('posts'); ?>">
                            <a href="javascript:void(0);" class="menu-toggle">
                                <i class="material-icons">collections</i>
                                <span>Posts</span>
                            </a>
                            <ul class="ml-menu"> 
                                <li class="<?php echo $admin->activeMenu('manage-posts'); ?>">
                                    <a href="<?php echo pxp_acp_link('manage-posts'); ?>" class="waves-effect waves-block">
                                        Manage posts
                                    </a>
                                </li>
                                <li class="<?php echo $admin->activeMenu('manage-challengers'); ?>">
                                    <a href="<?php echo pxp_acp_link('manage-challengers'); ?>" class="waves-effect waves-block">
                                        Challenge Entries
                                    </a>
                                </li>
                                <li class="<?php echo $admin->activeMenu('challenge-winners'); ?>">
                                    <a href="<?php echo pxp_acp_link('challenge-winners'); ?>" class="waves-effect waves-block">
                                        Challenge Winners
                                    </a>
                                </li>
                                <li class="<?php echo $admin->activeMenu('manage-challenges'); ?>">
                                    <a href="<?php echo pxp_acp_link('manage-challenges'); ?>" class="waves-effect waves-block">
                                        Challenge Categories
                                    </a>
                                </li> 
                            </ul>
                        </li>
                        <li class="<?php echo $admin->activeMenu('image_store'); ?>">
                            <a href="javascript:void(0);" class="menu-toggle">
                                <i class="material-icons">collections</i>
                                <span>Image store</span>
                            </a>
                            <ul class="ml-menu">
                                <li class="<?php echo $admin->activeMenu('manage-store-categories'); ?>">
                                    <a href="<?php echo pxp_acp_link('manage-store-categories'); ?>" class="waves-effect waves-block">
                                        Manage store categories
                                    </a>
                                </li>
                                <li class="<?php echo $admin->activeMenu('manage-store-items'); ?>">
                                    <a href="<?php echo pxp_acp_link('manage-store-items'); ?>" class="waves-effect waves-block">
                                        Manage store items
                                    </a>
                                </li>
                                <li class="<?php echo $admin->activeMenu('store-revenue'); ?>">
                                    <a href="<?php echo pxp_acp_link('store-revenue'); ?>" class="waves-effect waves-block">
                                        Store revenue
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="<?php echo $admin->activeMenu('blogs'); ?>">
                            <a href="javascript:void(0);" class="menu-toggle">
                                <i class="material-icons">collections</i>
                                <span>Blogs</span>
                            </a>
                            <ul class="ml-menu">
                                <li class="<?php echo $admin->activeMenu('manage-articles'); ?><?php echo $admin->activeMenu('edit-article'); ?>">
                                    <a href="<?php echo pxp_acp_link('manage-articles'); ?>" class="waves-effect waves-block">
                                        Manage articles
                                    </a>
                                </li>
                                <li class="<?php echo $admin->activeMenu('manage-blog-categories'); ?>">
                                    <a href="<?php echo pxp_acp_link('manage-blog-categories'); ?>" class="waves-effect waves-block">
                                        Manage blog categories
                                    </a>
                                </li>
                                <li class="<?php echo $admin->activeMenu('add-new-article'); ?>">
                                    <a href="<?php echo pxp_acp_link('add-new-article'); ?>" class="waves-effect waves-block">
                                        Add new article
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="<?php echo $admin->activeMenu('reports'); ?>">
                            <a href="javascript:void(0);" class="menu-toggle waves-effect waves-block">
                                <i class="material-icons">flag</i>
                                <span>Reports</span>
                            </a>
                            <ul class="ml-menu">
                                <li class="<?php echo $admin->activeMenu('profile-reports'); ?>">
                                    <a href="<?php echo pxp_acp_link('profile-reports'); ?>" class="waves-effect waves-block">
                                        Profile reports
                                    </a>
                                </li>
                                <li class="<?php echo $admin->activeMenu('post-reports'); ?>">
                                    <a href="<?php echo pxp_acp_link('post-reports'); ?>" class="waves-effect waves-block">
                                        Post Reports
                                    </a>
                                </li>
                            </ul>
                        </li>
                       <!--  <li class="<?php echo $admin->activeMenu('sitemap'); ?>">
                            <a href="javascript:void(0);" class="menu-toggle waves-effect waves-block">
                                <i class="material-icons">power_input</i>
                                <span>Sitemap</span>
                            </a>
                            <ul class="ml-menu">
                                <li>
                                    <a href="<?php echo pxp_acp_link('create-sitemap'); ?>" class=" waves-effect waves-block">Create Sitemap</a>
                                </li>
                            </ul>
                        </li> -->
                        
                        <?php if (admin_access($admin_role, 3)):?>
                        <li class="<?php echo $admin->activeMenu('design'); ?>">
                            <a href="javascript:void(0);" class="menu-toggle waves-effect waves-block">
                                <i class="material-icons">color_lens</i>
                                <span>Design</span>
                            </a>
                            <ul class="ml-menu" style="display: none;">
                                <li class="<?php echo $admin->activeMenu('themes'); ?>">
                                    <a href="<?php echo pxp_acp_link('themes'); ?>" class="waves-effect waves-block">
                                        Themes
                                    </a>
                                </li>
                                <li class="<?php echo $admin->activeMenu('manage-site-design'); ?>">
                                    <a href="<?php echo pxp_acp_link('manage-site-design'); ?>" class="waves-effect waves-block">
                                        Change Site Design
                                    </a>
                                </li>
                            </ul>
                        </li> 
                        <?php endif;?>
 
                        <li class="<?php echo $admin->activeMenu('pages'); ?>">
                            <a href="javascript:void(0);" class="menu-toggle waves-effect waves-block">
                                <i class="material-icons">web</i>
                                <span>Pages</span>
                            </a>
                            <ul class="ml-menu">
                                <li class="<?php echo $admin->activeMenu('modal'); ?>">
                                    <a href="<?php echo pxp_acp_link('modal'); ?>" class=" waves-effect waves-block">
                                        Manifest and Info Board
                                    </a>
                                </li>
                                
                                <?php if (admin_access($admin_role, 3)):?>
                                <li class="<?php echo $admin->activeMenu('terms'); ?>">
                                    <a href="<?php echo pxp_acp_link('terms'); ?>" class=" waves-effect waves-block">
                                        Terms of use
                                    </a>
                                </li>
                                <li class="<?php echo $admin->activeMenu('privacy-and-policy'); ?>">
                                    <a href="<?php echo pxp_acp_link('privacy-and-policy'); ?>" class=" waves-effect waves-block">
                                        Privacy and policy
                                    </a>
                                </li>
                                <li class="<?php echo $admin->activeMenu('about-us'); ?>">
                                    <a href="<?php echo pxp_acp_link('about-us'); ?>" class=" waves-effect waves-block">
                                        About us
                                    </a>
                                </li>
                                <li class="<?php echo $admin->activeMenu('disclaimer'); ?>">
                                    <a href="<?php echo pxp_acp_link('disclaimer'); ?>" class=" waves-effect waves-block">
                                        Disclaimer
                                    </a>
                                </li>
                                <li class="<?php echo $admin->activeMenu('contact_us'); ?>">
                                    <a href="<?php echo pxp_acp_link('contact_us'); ?>" class=" waves-effect waves-block">
                                        Contact Us
                                    </a>
                                </li>
                                <?php endif;?>
                            </ul>
                        </li>
                        <li class="<?php echo $admin->activeMenu('mobile_api'); ?>">
                            <a href="javascript:void(0);" class="menu-toggle waves-effect waves-block">
                                <i class="material-icons">compare_arrows</i>
                                <span>Mobile & API Settings</span>
                            </a>
                            <ul class="ml-menu">
                                <li class="<?php echo $admin->activeMenu('push-notifications-system'); ?>">
                                    <a href="<?php echo pxp_acp_link('push-notifications-system'); ?>" class=" waves-effect waves-block">
                                        Push Notifications System
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="<?php echo $admin->activeMenu('backup'); ?>">
                            <a href="<?php echo pxp_acp_link('create-backup'); ?>" class="toggled waves-effect waves-block">
                                <i class="material-icons">backup</i>
                                <span>Backup</span>
                            </a>
                        </li>
                        <li class="<?php echo $admin->activeMenu('logs'); ?>">
                            <a href="<?php echo pxp_acp_link('changelogs'); ?>" class="toggled waves-effect waves-block">
                                <i class="material-icons">update</i>
                                <span>Changelogs</span>
                            </a>
                        </li>
                        <li>
                            <a href="http://docs.pixelphotoscript.com" target="_blank" class=" waves-effect waves-block">
                                <i class="material-icons">more_vert</i>
                                <span>FAQs &amp; Docs</span>
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="legal">
                    <div class="copyright">
                        &copy; 2018 - <?php echo date('Y'); ?> <a href="<?php echo $site_url;?>"><?php echo $config['site_name']; ?></a>.
                    </div>
                    <div class="version">
                        <b>Version: </b> <?php echo $config['version']; ?>
                    </div>
                </div> 
            </aside>
        </section>

        <section class="content">
            <div class="container-fluid">
                <?php if (is_dir(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."install")) { ?>
                  <div class="alert alert-danger">
                    <i class="fa fa-fw fa-exclamation-triangle"></i> <strong>Risk:</strong> Please delete the ./install folder for security reasons.
                  </div>
                <?php } ?>
                <?php echo $page_content; ?>
            </div>
        </section>
         
        <script src="<?php echo pxp_acp_link('plugins/bootstrap/js/bootstrap.js');?>"></script>
        <script src="<?php echo pxp_acp_link('plugins/jquery-datatable/jquery.dataTables.js'); ?>"></script>
        <script src="<?php echo pxp_acp_link('plugins/jquery-datatable/skin/bootstrap/js/dataTables.bootstrap.js'); ?>"></script>
        <script src="<?php echo pxp_acp_link('plugins/jquery-datatable/extensions/export/dataTables.buttons.min.js'); ?>"></script>
        <script src="<?php echo pxp_acp_link('plugins/jquery-datatable/extensions/export/buttons.flash.min.js'); ?>"></script>
        <script src="<?php echo pxp_acp_link('plugins/jquery-datatable/extensions/export/jszip.min.js'); ?>"></script>
        <script src="<?php echo pxp_acp_link('plugins/jquery-datatable/extensions/export/pdfmake.min.js'); ?>"></script>
        <script src="<?php echo pxp_acp_link('plugins/jquery-datatable/extensions/export/vfs_fonts.js'); ?>"></script>
        <script src="<?php echo pxp_acp_link('plugins/jquery-datatable/extensions/export/buttons.html5.min.js'); ?>"></script>
        <script src="<?php echo pxp_acp_link('plugins/jquery-datatable/extensions/export/buttons.print.min.js'); ?>"></script>
        <script src="<?php echo pxp_acp_link('js/pages/tables/jquery-datatable.js'); ?>"></script>
        
        <script src="<?php echo pxp_acp_link('plugins/bootstrap-select/js/bootstrap-select.js');?>"></script>
        <script src="<?php echo pxp_acp_link('plugins/jquery-slimscroll/jquery.slimscroll.js');?>"></script>
        <script src="<?php echo pxp_acp_link('plugins/node-waves/waves.js');?>"></script>
        <script src="<?php echo pxp_acp_link('plugins/jquery-countto/jquery.countTo.js');?>"></script>
        <script src="<?php echo pxp_acp_link('plugins/raphael/raphael.min.js');?>"></script>
        <script src="<?php echo pxp_acp_link('plugins/morrisjs/morris.js');?>"></script>
        <script src="<?php echo pxp_acp_link('plugins/chartjs/Chart.bundle.js');?>"></script>
        <script src="<?php echo pxp_acp_link('plugins/jquery-sparkline/jquery.sparkline.js');?>"></script>
        <script src="<?php echo pxp_acp_link('js/admin.js');?>"></script>
        <script src="<?php echo pxp_acp_link('js/demo.js');?>"></script>
        <script src="<?php echo pxp_acp_link('js/script.js');?>"></script>
        <script src="<?php echo pxp_acp_link('js/hoolicon.js');?>"></script>
        <script src="<?php echo pxp_acp_link('plugins/bootstrap-toggle/bootstrap-toggle.min.js');?>"></script>
        <script src="<?php echo pxp_acp_link('plugins/jquery-form/jquery-form.v3.51.0.js');?>"></script>
        <script src="<?php echo $context['theme_url'] ?>/main/static/js/libs/toast/src/jquery.m.toast.js"></script>
    </body>

</html>
