<?php
    ini_set('error_log', __DIR__ . "/CryptAES_err.log");
    require_once 'CryptAES.php';

    class Response
    {
        private $code;
        private $msg;
        private $transaction_data;
        private $url_param;

        function __construct(){

        }

        function set_code($code){
            $this->code = $code;
        }

        function set_msg($msg){
            $this->msg = $msg;
        }

        function set_transactionData($data){
            $this->transaction_data = $data;
        }

        function set_urlParam($string){
            $rawdata = self::encrypt($string);
            $this->url_param = bin2hex($rawdata);
            echo $this->url_param;
            //$encode = mb_detect_encoding($rawdata, array("ASCII",'UTF-8',"GB2312","GBK",'BIG5',));
            // $this->url_param = mb_convert_encoding($rawdata, 'UTF-8', $encode);
            //echo $encode . '</br>';
            // use bi to hex function
        }

        private function encrypt($string){
            $key = 'abcdefghijklmnop';
            $iv = '1234567890123456';
            $password = 'abcdefghijklmnop';
            $randomstring = 'ahdkvhey';

            $encryption = new CryptAES($key, $iv, $password);
            $cipher = $encryption->encrypt($string . '&' . $randomstring);
            return $cipher;
        }

        function produce_response(){
            $response_msg = array(
                'urlParam' => $this->url_param,
                'paymentUrl' => 'http://www.serverside.com/payment_page/payment.html' . '?' . $this->url_param,
                'info' => (empty($this->transaction_data)) ? array() : $this->transaction_data
            );
            return $response_msg;
        }
    }

    try {
        $response_msg = new Response();

        $response_msg->set_urlParam('55');
    } catch(Exception $e) {
        error_log($e . PHP_EOL);
    }

    // $a = $response_msg->produce_response();
    // $_data = json_encode($a, JSON_PRETTY_PRINT);
    // echo "<pre>".$_data."<pre/>";

    // $data = array(
    //     'data_1' => 'str_1111',
    //     'data_2' => 'str_2222'
    // );

    // $response_msg->set_transactionData($data);
    // $response_msg->set_urlParam();

    // $a = $response_msg->produce_response();
    // $_data = json_encode($a, JSON_PRETTY_PRINT);
    // echo "<pre>".$_data."<pre/>";
    
    // //-------------------------------------------------------//

    // $string = "this is need to be encrypted";
    // $ciphering = "AES-128-CTR";
    // $option = 0;
    // $encrypt_key = 'henrycheng';
    // $encrypt_iv = '1234567890123456';

    // $key = hash('sha256', $encrypt_key);
    // $iv = substr(hash('sha256', $encrypt_iv), 0, 16);

    // $encryption = openssl_encrypt($string, $ciphering, $key, $option, $iv);
    
    // echo 'Encryption data : ' . $encryption . "<br>";

    // $arr = array(
    //     'MerchantID' => '00001',
    //     'TradeRecordID' => $encryption
    // );
    // echo 'Url : ' . http_build_query($arr) . "<br>";

    // $key_2 = hash('sha256', $encrypt_key);
    // $iv_2 = substr(hash('sha256', $encrypt_iv), 0, 16);

    // $decryption = openssl_decrypt($encryption, $ciphering, $key_2, $option, $iv_2);
    // echo 'Original string : ' . $decryption;

    // session_start();

    // $_SESSION['name'] = 'Henry';
    // $_SESSION['id'] = 14;
    // unset($_SESSION['count']);

    //echo $_SESSION['name'];
?>

