<?php
    $url = 'http://127.0.0.1/api_practice/api_controller.php';

    $query = [
        "MerchantID" => "00011",
        "MerchantTradeNum" => "cs00011",
        "MerchantTradeDate" => "202205012202222",
        "TotalAmount" => "700",
        "ItemName" => "cost 11"
    ];

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        // CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => http_build_query($query),
        // CURLOPT_HTTPHEADER => array(
        //     'Content-Type: application/json',
        // ),
        // CURLOPT_SSL_VERIFYPEER => false,
        // CURLOPT_SSL_VERIFYHOST => false,
    ));

    $result = curl_exec($curl);
    curl_close($curl);

    echo $result;
?>