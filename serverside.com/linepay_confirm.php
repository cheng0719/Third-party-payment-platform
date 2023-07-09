<?php
    date_default_timezone_set("Asia/Taipei");

    error_reporting(E_ALL ^ E_WARNING);

    ini_set('error_log', __DIR__ . "/api_error.log");

    require_once 'C:\Apache24\htdocs\serverside.com\config.php';

    set_error_handler(function ($error_no, $error_msg, $error_file, $error_line) {
        switch ($error_no) {
            case E_WARNING:
                if (strcmp('Division by zero', $error_msg) == 0) {
                    throw new \DivisionByZeroError($error_msg);
                }    
                $level_tips = 'PHP Warning: ';
                break;
            case E_NOTICE:
                $level_tips = 'PHP Notice: ';
                break;
            case E_DEPRECATED:
                $level_tips = 'PHP Deprecated: ';
                break;
            case E_USER_ERROR:
                $level_tips = 'User Error: ';
                break;
            case E_USER_WARNING:
                $level_tips = 'User Warning: ';
                break;
            case E_USER_NOTICE:
                $level_tips = 'User Notice: ';
                break;
            case E_USER_DEPRECATED:
                $level_tips = 'User Deprecated: ';
                break;
            case E_STRICT:
                $level_tips = 'PHP Strict: ';
                break;
            default:
                $level_tips = 'Unknow Type Error: ';
                break;
        }
    
        // do some handle
        $error = $level_tips . $error_msg . ' in ' . $error_file . ' on ' . $error_line;
        echo $error . PHP_EOL;
        
        // or throw a ErrorException back to try ... catch block
        // throw new \ErrorException($error);
  
    }, E_ALL | E_STRICT);

    try{
        $conn = DBConnection();
        $stmt = $conn->prepare('select totalamount from linepay_transaction where orderid = :orderid;');
        $stmt->bindParam('orderid', $_GET['orderId']);
        if(!$stmt->execute())
        {
            throw new Exception('Failed database processing');
        }
        $transaction_amount = $stmt->fetch();


        $sandBox = 'https://sandbox-api-pay.line.me';
        $uri = '/v3/payments/'.$_GET['transactionId'].'/confirm';
        $channelId = '1656946528';
        $channelSecret = '36307ac678361a20fd395e209f0428ba';
        $Nonce = date('c') . uniqid('-');



        $qyery = [
            'amount' => $transaction_amount[0],
            'currency' => 'TWD'
        ];

        $authMacText = $channelSecret . $uri . json_encode($qyery) . $Nonce;
        $Authorization = base64_encode(hash_hmac('sha256', $authMacText, $channelSecret, true));


        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $sandBox.$uri,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>json_encode($qyery),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'X-LINE-ChannelId: '.$channelId,
                'X-LINE-Authorization-Nonce: '.$Nonce,
                'X-LINE-Authorization: '.$Authorization
            ),
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ));

        $response = curl_exec($curl);
        if($response === false)
        {
            throw new Exception(curl_error($curl));
        }

        curl_close($curl);


        // $data = json_encode(json_decode($response), JSON_PRETTY_PRINT);
        // echo "<pre>".$data."<pre/>";
        

        $result = json_decode($response);
        if($result->returnCode === '0000')
        {
            $stmt = $conn->prepare('update linepay_transaction 
                                    set paymentcompletion = true,
                                        transactionid = :transactionid,
                                        completetime = Current_timestamp(0)
                                    where orderid = :orderid returning merchanttraderecordid;');
            $stmt->bindParam('orderid', $_GET['orderId']);
            if(!$stmt->execute(array(
                'transactionid' => $_GET['transactionId'],
                'orderid' => $_GET['orderId']
            )))
            {
                throw new Exception('Failed database processing');
            }
            $traderecordid = $stmt->fetch();

            $stmt = $conn->prepare('update api_practice set paymentcompletion = true where traderecordid = :traderecordid;');
            $stmt->bindParam('traderecordid', $traderecordid[0]);
            if(!$stmt->execute())
            {
                throw new Exception('Failed database processing');
            }

            //echo 'Payment successful';
            header("Location:http://www.clientside.com/confirm/confirm_page.php");
        }
        else
        {
            $stmt = $conn->prepare('update linepay_transaction set paymentcompletion = false where orderid = :orderid returning merchanttraderecordid;');
            $stmt->bindParam('orderid', $_GET['orderId']);
            if(!$stmt->execute())
            {
                throw new Exception('Failed database processing');
            }
            $traderecordid = $stmt->fetch();

            $stmt = $conn->prepare('update api_practice set paymentcompletion = false where traderecordid = :traderecordid;');
            $stmt->bindParam('traderecordid', $traderecordid[0]);
            if(!$stmt->execute())
            {
                throw new Exception('Failed database processing');
            }

            //echo 'Payment failed';
            header("Location:http://www.clientside.com/confirm/confirm_page.php");
        }
    } catch (\Exception $exception) {

        // error_log('ErrorException: ' . $errorException . PHP_EOL);
        // echo 'Server internal error' . PHP_EOL;
        echo $exception . PHP_EOL;

    } catch (\ErrorException $errorException) {

        // error_log('Exception: ' . $exception . PHP_EOL);
        // echo 'Server internal error' . PHP_EOL;
        echo $errorException . PHP_EOL;

    } catch (\ParseError $parseError) {

        // error_log('Parse Error: ' . $parseError . PHP_EOL);
        // echo 'Server internal error' . PHP_EOL;
        echo $parseError . PHP_EOL;

    } catch (\ArgumentCountError $argumentCountError ) {

        // error_log('Argument Count Error: ' . $argumentCountError . PHP_EOL);
        // echo 'Server internal error' . PHP_EOL;
        echo $argumentCountError . PHP_EOL;

    } catch (\TypeError $typeError) {

        // error_log('Type Error: ' . $typeError . PHP_EOL);
        // echo 'Server internal error' . PHP_EOL;
        echo $typeError . PHP_EOL;

    } catch (\DivisionByZeroError $divisionByZeroError) {

        // error_log('Division By Zero Error: ' . $divisionByZeroError . PHP_EOL);
        // echo 'Server internal error' . PHP_EOL;
        echo $divisionByZeroError . PHP_EOL;

    } catch (\ArithmeticError $arithmeticError) {

        // error_log('Arithmetic Error: ' . $arithmeticError . PHP_EOL);
        // echo 'Server internal error' . PHP_EOL;
        echo $arithmeticError . PHP_EOL;

    } catch (\AssertionError $assertionError) {

        // error_log('Assertion Error: ' . $assertionError . PHP_EOL);
        // echo 'Server internal error' . PHP_EOL;
        echo $assertionError . PHP_EOL;

    } catch (\Error $error) {

        // error_log('Error: ' . $error . PHP_EOL);
        // echo 'Server internal error' . PHP_EOL;
        echo $error . PHP_EOL;

    }
?>