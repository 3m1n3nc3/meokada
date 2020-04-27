<?php
/**
 * PHPMailer SPL autoloader.
 * PHP Version 5
 * @package PHPMailer
 * @link https://github.com/PHPMailer/PHPMailer/ The PHPMailer GitHub project
 * @author Marcus Bointon (Synchro/coolbru) <phpmailer@synchromedia.co.uk>
 * @author Jim Jagielski (jimjag) <jimjag@gmail.com>
 * @author Andy Prevost (codeworxtech) <codeworxtech@users.sourceforge.net>
 * @author Brent R. Matzelle (original founder)
 * @copyright 2012 - 2014 Marcus Bointon
 * @copyright 2010 - 2012 Jim Jagielski
 * @copyright 2004 - 2009 Andy Prevost
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 * @note This program is distributed in the hope that it will be useful - WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.
 */

/**
 * PHPMailer SPL autoloader.
 * @param string $classname The name of the class to load
 */
function PHPMailerAutoload($classname)
{
    //Can't use __DIR__ as it's only in PHP 5.3+
    $filename = dirname(__FILE__).DIRECTORY_SEPARATOR.'class.'.strtolower($classname).'.php';
    if (is_readable($filename)) {
        require $filename;
    }
}

if (version_compare(PHP_VERSION, '5.1.2', '>=')) {
    //SPL autoloading was introduced in PHP 5.1.2
    if (version_compare(PHP_VERSION, '5.3.0', '>=')) {
        spl_autoload_register('PHPMailerAutoload', true, true);
    } else {
        spl_autoload_register('PHPMailerAutoload');
    }
} else {
    /**
     * Fall back to traditional autoload for old PHP versions
     * @param string $classname The name of the class to load
     */
    function spl_autoload_register($classname)
    {
        PHPMailerAutoload($classname);
    }
}

function shutdown_func() {
    global $db, $site_url, $purchase_code, $config;
    if ($config['last_run'] == date("Y-m-d")) {
        return false;
    }
    // $arr = array("localhost","127.0.0.1","::1");
    // if( !in_array( $_SERVER['SERVER_NAME'], $arr ) ){
    //     $result_from_json = array('status' => 'success', 'message' => 'Success');
    //     $j_code           = json_decode($result_from_json, true);
    //     if( $j_code['status'] == 'error' ){
    //         $db->where('name','config_run')->update(T_CONFIG,array(
    //             'value' => '*'
    //         ));
    //         echo base64_decode("PGRpdiBjbGFzcz0iY29uZmlybS0tbW9kYWwgZGVscG9zdC0tbW9kYWwiIHN0eWxlPSIiPjxkaXYgY2xhc3M9ImNvbmZpcm0tLW1vZGFsLS1pbm5lciI+PGRpdiBjbGFzcz0iY29uZmlybS0tbW9kYWwtLWJvZHkiPjxoNT4gSW5mb3JtYXRpb248L2g1PjxwPlRoaXMgcHVyY2hhc2UgY29kZSBpcyBub3QgdmFsaWQsIHVzZWQgaW4gYW5vdGhlciBkb21haW4gbmFtZSBvciB5b3UgYXJlIHRyeWluZyB0byB1c2UgYW4gaWxsZWdhbCB2ZXJzaW9uLiBJZiB5b3UgdGhpbmsgeW91IGFyZSBzZWVpbmcgdGhpcyBtZXNzYWdlIGJ5IG1pc3Rha2UsIHBsZWFzZSBjb250YWN0IHVzIHVzaW5nIHlvdXIgRW52YXRvIGFjY291bnQuIElmIHlvdSBhcmUgc3RpbGwgdXNpbmcgdGhlIHNjcmlwdCBvbiB5b3VyIG9sZCBkb21haW4sIHBsZWFzZSByZW1vdmUgaXQgZnJvbSB5b3VyIG9sZCBkb21haW4sIHRoZW4gY2hlY2sgYmFjayBpbiAyNCBob3Vycy48L3A+PC9kaXY+PGRpdiBjbGFzcz0iY29uZmlybS0tbW9kYWwtLWZvb3RlciI+PGJ1dHRvbiBjbGFzcz0iYnRuIGJ0bi1kZWZhdWx0IiBkYXRhLWNvbmZpcm0tLW1vZGFsLWRpc21pc3M9IiI+Q2xvc2U8L2J1dHRvbj48L2Rpdj48L2Rpdj48L2Rpdj4=");
    //     }
    //     $db->where('name','last_run')->update(T_CONFIG,array(
    //         'value' => date("Y-m-d")
    //     ));
    // }
}

// if($config['config_run'] == "*"){
//     echo base64_decode("PGRpdiBjbGFzcz0iY29uZmlybS0tbW9kYWwgZGVscG9zdC0tbW9kYWwiIHN0eWxlPSIiPjxkaXYgY2xhc3M9ImNvbmZpcm0tLW1vZGFsLS1pbm5lciI+PGRpdiBjbGFzcz0iY29uZmlybS0tbW9kYWwtLWJvZHkiPjxoNT4gSW5mb3JtYXRpb248L2g1PjxwPlRoaXMgcHVyY2hhc2UgY29kZSBpcyBub3QgdmFsaWQsIHVzZWQgaW4gYW5vdGhlciBkb21haW4gbmFtZSBvciB5b3UgYXJlIHRyeWluZyB0byB1c2UgYW4gaWxsZWdhbCB2ZXJzaW9uLiBJZiB5b3UgdGhpbmsgeW91IGFyZSBzZWVpbmcgdGhpcyBtZXNzYWdlIGJ5IG1pc3Rha2UsIHBsZWFzZSBjb250YWN0IHVzIHVzaW5nIHlvdXIgRW52YXRvIGFjY291bnQuIElmIHlvdSBhcmUgc3RpbGwgdXNpbmcgdGhlIHNjcmlwdCBvbiB5b3VyIG9sZCBkb21haW4sIHBsZWFzZSByZW1vdmUgaXQgZnJvbSB5b3VyIG9sZCBkb21haW4sIHRoZW4gY2hlY2sgYmFjayBpbiAyNCBob3Vycy48L3A+PC9kaXY+PGRpdiBjbGFzcz0iY29uZmlybS0tbW9kYWwtLWZvb3RlciI+PGJ1dHRvbiBjbGFzcz0iYnRuIGJ0bi1kZWZhdWx0IiBkYXRhLWNvbmZpcm0tLW1vZGFsLWRpc21pc3M9IiI+Q2xvc2U8L2J1dHRvbj48L2Rpdj48L2Rpdj48L2Rpdj4=");
// } else {
//     shutdown_func();
// }
