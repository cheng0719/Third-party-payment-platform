<?php
    $url = 'http://www.serverside.com/api_controll.php';

    $query = [
        'Method' => 'Search',
        'MerchantID' => '00001',
        'Password' => 'password_1',
        'TradeRecordID' => 31
    ];

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        // CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => http_build_query($query),
        //CURLOPT_POSTFIELDS => $query,
        // CURLOPT_HTTPHEADER => array(
        //     'Content-Type: application/json',
        // ),
        // CURLOPT_SSL_VERIFYPEER => false,
        // CURLOPT_SSL_VERIFYHOST => false,
    ));

    $response = curl_exec($curl);
    curl_close($curl);

    $data = json_decode($response);
    var_dump($data);
    $payment_flag;
    if($data->info->PaymentCompletion)
    {
        $payment_flag = '已付款';
    }
    else
    {
        $payment_flag = '未付款';
    }

    echo $payment_flag;
?>



