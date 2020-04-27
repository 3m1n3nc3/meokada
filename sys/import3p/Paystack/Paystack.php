<?php
    
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

require_once('src/autoload.php');

class Paystack {

    public $config;
    public $private;
    public $public;
    public $reference;

    public function __construct() 
    {  
        $this->secret = $config['paystack_secret'];
        $this->init = new Yabacon\Paystack($this->secret);
    }

    public function pay($reference)
    {
        $trx = $this->init->transaction->verify( [ 'reference' => $reference ] );
        return $trx;
    }

}
