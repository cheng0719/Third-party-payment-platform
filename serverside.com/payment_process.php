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
        // csrf token comparison
        // check if csrf token exists
        if(empty($_POST['csrfToken']))
        {
            throw new Exception('csrf token does not exist');
        }

        // fetch transaction data with $_POST['transactionID']
        $conn = DBConnection();
        $stmt = $conn->prepare('select * from api_practice where traderecordid = :traderecordid;');
        $stmt->bindParam('traderecordid', $_POST['transactionID']);
        if(!$stmt->execute())
        {
            throw new Exception('Failed database processing');
        }
        $transaction_data = $stmt->fetch();

        // compare csrf token in POST with csrf token in database
        if(strcmp($_POST['csrfToken'], $transaction_data['csrftoken']) !== 0)
        {
            throw new Exception('csrf token does not match');
        }

        // delete csrf token in database
        $stmt = $conn->prepare('update api_practice
                                set csrftoken = null
                                where traderecordid = :traderecordid;');
        $stmt->bindParam('traderecordid', $transaction_data['traderecordid']);
        if(!$stmt->execute())
        {
            throw new Exception('Failed database processing');
        }

        //echo 'Method : ' . $_POST['PaymentMethod'] . ' TradeRecordID : ' . $transaction_data['traderecordid'];

        // check if the transaction is already in TABLE linepay_transaction
        $stmt = $conn->prepare('select * from linepay_transaction where merchanttraderecordid = :merchanttraderecordid;');
        $stmt->bindParam('merchanttraderecordid', $transaction_data['traderecordid']);
        if(!$stmt->execute())
        {
            throw new Exception('Failed database processing');
        }
        $orderid = $stmt->fetch();

        if(!empty($orderid))
        {
            if($orderid['paymentcompletion'])
            {
                throw new Exception('payment error, transaction is already complete');
            }
        }
        else
        {
            // insert traderecordid, totalamount, itemname into TABLE linepay_transaction
            $stmt = $conn->prepare('insert into linepay_transaction (merchanttraderecordid, totalamount, itemname) values 
            (:merchanttraderecordid, :totalamount, :itemname) returning orderid;');
            if(!$stmt->execute(array(
                'merchanttraderecordid' => $transaction_data['traderecordid'],
                'totalamount' => $transaction_data['totalamount'],
                'itemname' => $transaction_data['itemname']
            )))
            {
                throw new Exception('Failed database processing');
            }

            $orderid = $stmt->fetch();
        }
    
        // fetch data with orderid from TABLE linepay_transaction
        $stmt = $conn->prepare('select * from linepay_transaction where orderid = :orderid;');
        $stmt->bindParam('orderid', $orderid[0]);
        if(!$stmt->execute())
        {
            throw new Exception('Failed database processing');
        }
        $linepay_transaction_data = $stmt->fetch();


        // linepay request
        $sandBox = 'https://sandbox-api-pay.line.me';
        $uri = '/v3/payments/request';
        $channelId = '1656946528';
        $channelSecret = '36307ac678361a20fd395e209f0428ba';
        $Nonce = date('c') . uniqid('-');


        $qyery = [
            'amount' => $linepay_transaction_data['totalamount'],
            'currency' => 'TWD',
            'orderId' => $linepay_transaction_data['orderid'],
            'packages' => [
                [
                    'id' => $linepay_transaction_data['orderid'],
                    'amount' => $linepay_transaction_data['totalamount'],
                    'name' => 'test store',                                   // need to modify
                    'products' => [
                        [
                            'name' => $linepay_transaction_data['itemname'],
                            'quantity' => 1,
                            'price' => $linepay_transaction_data['totalamount']
                        ],
                    ],
                ],
            ],
            'redirectUrls' => [
                'confirmUrl' => 'http://serverside.com/linepay_confirm.php',
                'cancelUrl' => 'http://serverside.com/test2'
            ],
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
                'X-LINE-Authorization: '.$Authorization,
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
        // var_dump($response);

        $data = json_decode($response);
        $_data = json_encode($data, JSON_PRETTY_PRINT);
        echo "<pre>".$_data."<pre/>";
        
        header("Location: ".$data->info->paymentUrl->web);


    } catch (\Exception $exception) {

        // error_log('ErrorException: ' . $errorException . PHP_EOL);
        // echo 'Server internal error' . PHP_EOL;
        echo $exception . PHP_EOL;
        exit();

    } catch (\ErrorException $errorException) {

        // error_log('Exception: ' . $exception . PHP_EOL);
        // echo 'Server internal error' . PHP_EOL;
        echo $errorException . PHP_EOL;
        exit();

    } catch (\ParseError $parseError) {

        // error_log('Parse Error: ' . $parseError . PHP_EOL);
        // echo 'Server internal error' . PHP_EOL;
        echo $parseError . PHP_EOL;
        exit();

    } catch (\ArgumentCountError $argumentCountError ) {

        // error_log('Argument Count Error: ' . $argumentCountError . PHP_EOL);
        // echo 'Server internal error' . PHP_EOL;
        echo $argumentCountError . PHP_EOL;
        exit();

    } catch (\TypeError $typeError) {

        // error_log('Type Error: ' . $typeError . PHP_EOL);
        // echo 'Server internal error' . PHP_EOL;
        echo $typeError . PHP_EOL;
        exit();

    } catch (\DivisionByZeroError $divisionByZeroError) {

        // error_log('Division By Zero Error: ' . $divisionByZeroError . PHP_EOL);
        // echo 'Server internal error' . PHP_EOL;
        echo $divisionByZeroError . PHP_EOL;
        exit();

    } catch (\ArithmeticError $arithmeticError) {

        // error_log('Arithmetic Error: ' . $arithmeticError . PHP_EOL);
        // echo 'Server internal error' . PHP_EOL;
        echo $arithmeticError . PHP_EOL;
        exit();

    } catch (\AssertionError $assertionError) {

        // error_log('Assertion Error: ' . $assertionError . PHP_EOL);
        // echo 'Server internal error' . PHP_EOL;
        echo $assertionError . PHP_EOL;
        exit();

    } catch (\Error $error) {

        // error_log('Error: ' . $error . PHP_EOL);
        // echo 'Server internal error' . PHP_EOL;
        echo $error . PHP_EOL;
        exit();

    }
?>