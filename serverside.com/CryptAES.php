<?php
ini_set('error_log', __DIR__ . "/CryptAES_err.log");

require_once('phpseclib/Crypt/Common/SymmetricKey.php');
require_once('phpseclib/Crypt/Common/BlockCipher.php');
require_once('phpseclib/Crypt/Rijndael.php');
require_once('phpseclib/Crypt/AES.php');
require_once('phpseclib/Crypt/Hash.php');

use phpseclib3\Crypt\Common\SymmetricKey ;
use phpseclib3\Crypt\Common\BlockCipher;
use phpseclib3\Crypt\Rijndael;
use phpseclib3\Crypt\AES;
use phpseclib3\Crypt\Hash;

class CryptAES
{
		private $mode= 'cfb';
		private $key='';
		private $iv='';
		private $blocksize=16;
		private $aes;
		function __construct($key, $iv, $passphrase)
		{
			//$this->mode = $mode;
            $this->key = $key;
            $this->iv = $iv;

			if($passphrase==''){  
				$this->key=$key;
				$this->iv=$iv;
			}else{                
				list($this->key,$this->iv)=$this->pbkdf2($passphrase);
			}
            $this->aes = new AES($this->mode);	
            $this->aes->disablePadding();  // padding by pkcs5
			$this->aes->setKey($this->key);
			$this->aes->setIV($this->iv);
		}

		function pkcs5_pad($text)
		{
				$pad = $this->blocksize - (strlen($text) % $this->blocksize);
		        return $text . str_repeat(chr($pad), $pad);
		}
		function pkcs5_unpad($text)
		{
				$pad = ord($text[($len = strlen($text)) - 1]);
		        $len = strlen($text);
		        $pad = ord($text[$len-1]);
		        return substr($text, 0, strlen($text) - $pad);
		}
		function pbkdf2($passphase,$hash='SHA256',$salt='',$iter=10000,$len=16){
			$key='';
			$hmac = new Hash();
			$hmac->setHash($hash);
			$hmac->setKey($passphase);
			$i=1;
			while (strlen($key) < $len*2 ){
				$f = $u = $hmac->hash($salt . pack('N', $i++));
				for ($j = 2; $j <= $iter; ++$j) {
					$u = $hmac->hash($u);
					$f^= $u;
				}
				$key.= $f;
		    }
		    return array(substr($key,0,$this->blocksize),substr($key,$this->blocksize,$this->blocksize));
		}

		function encrypt($data)
		{
			return $this->aes->encrypt($this->pkcs5_pad($data));
			//return $this->aes->encrypt($data);
		}
		function decrypt($data)
		{
			return $this->pkcs5_unpad($this->aes->decrypt($data));
			//return $this->aes->decrypt($data);
		}
		        
}

// try {
//     $key = 'abcdefghijklmnop';
//     $iv = '1234567890123456';
// 	$password = 'abcdefghijklmnop';
//     $encryption = new CryptAES($key, $iv, $password);
//     $data = 'p_l_a_i_n_t_e_x_t';
//     $ciphertext = $encryption->encrypt($data);
//     $plaintext = $encryption->decrypt($ciphertext);
//     echo 'cipher: ' . bin2hex($ciphertext) . '  //  plain: ' . $plaintext;
// } catch(Exception $e) {
//     error_log($e . PHP_EOL);
// }


// class CryptAES
// {
// 		private $cipher='';
// 		private $key='';
// 		private $iv='';
// 		private $blocksize=16;
// 		private $aes;
// 		function __construct($cipher=MODE_CBC,$blocksize=16,$passphrase='',$key='',$iv='')
// 		{
// 			$this->cipher=$cipher;
// 			$this->blocksize=$blocksize;
// 			if($passphrase==''){  // if there's no password(maybe? idk), just set up the key and iv
// 				$this->key=$key;
// 				$this->iv=$iv;
// 			}else{                // otherwise 
// 				list($this->key,$this->iv)=$this->pbkdf2($passphrase);
// 			}
// 			$this->aes=new Crypt_AES($this->cipher);
// 			$this->aes->disablePadding();  // padding by itself
// 			$this->aes->setKey($this->key);
// 			$this->aes->setIV($this->iv);
// 		}

// 		function pkcs5_pad($text)
// 		{
// 				$pad = $this->blocksize - (strlen($text) % $this->blocksize);
// 		        return $text . str_repeat(chr($pad), $pad);
// 		}
// 		function pkcs5_unpad($text)
// 		{
// 				$pad = ord($text[($len = strlen($text)) - 1]);
// 		        $len = strlen($text);
// 		        $pad = ord($text[$len-1]);
// 		        return substr($text, 0, strlen($text) - $pad);
// 		}
// 		function pbkdf2($passphase,$hash='SHA256',$salt='',$iter=10000,$len=16){
// 			$key='';
// 			$hmac = new Crypt_Hash();
// 			$hmac->setHash($hash);
// 			$hmac->setKey($passphase);
// 			$i=1;
// 			while (strlen($key) < $len*2 ){
// 				$f = $u = $hmac->hash($salt . pack('N', $i++));
// 				for ($j = 2; $j <= $iter; ++$j) {
// 					$u = $hmac->hash($u);
// 					$f^= $u;
// 				}
// 				$key.= $f;
// 		    }
// 		    return array(substr($key,0,$this->blocksize),substr($key,$this->blocksize,$this->blocksize));
// 		}

// 		function encrypt($data)
// 		{
// 			return $this->aes->encrypt($this->pkcs5_pad($data));
// 		}
// 		function decrypt($data)
// 		{
// 			return $this->pkcs5_unpad($this->aes->decrypt($data)    );
// 		}
		        
// }


// 	$Encryption = new CryptAES('password');
// 	$data = 'hello world';
// 	$ciphertext = $Encryption->encrypt($data);
// 	$plaintext = $Encryption->decrypt($ciphertext);

// 	echo 'ciphertext : ' . $ciphertext . PHP_EOL;
// 	echo 'plaintext : ' . $plaintext;


	
