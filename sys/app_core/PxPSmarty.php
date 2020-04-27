<?php
class PxPSmarty extends Smarty{
    public function __construct(){
        global $config,$context;
        parent::__construct();

        $context_data = array(
            'context' => $context,
            'langs' => $context['langs'],
            'config' => $config,
            'site_url' => $config['site_url'],
            'theme_url' => sprintf('%s/apps/%s',$config['site_url'],$config['theme']),
            'me' => false,
            'countries' => array(),
            'icons' => sprintf('%s/media/icons',$config['site_url']),
            'images' => sprintf('%s/media/img',$config['site_url']),
            'lang' => json_encode($context['lang']),
            'csrf_token' => pxp_gencsrf_token(),
            'ad1' => $config['ad1'],
            'ad2' => $config['ad2'],
            'ad3' => $config['ad3'],
            'c' => ''
        );

        if (!empty($context['countries_name'])) {
            $context_data['countries'] = $context['countries_name'];
        }

        if (!empty($context['user'])) {
            $context_data['me'] = $context['user'];
        }

        $this->assign($context_data);
        $this->setCompileDir('./templates_new_');
        $this->setCompileCheck(true);
        $this->setForceCompile(false);
        $this->caching = false;
        
    }

    public function setDir($path = ""){
        global $config;
        $theme = $config['theme'];

        $this->template_dir = ROOT . "/apps/$theme/$path/";
        return $this;
    }
}
