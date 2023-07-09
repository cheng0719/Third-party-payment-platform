<?php
    $url = 'http://127.0.0.1/api_practice/index.php';

    $query = [
        "account" => "henry@gmail.com",
        "password" => "87654321"
    ];

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        // CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => json_encode($query),
        // CURLOPT_POSTFIELDS => array("data" =>
        //     json_encode($query)
        // )
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
        ),
        // CURLOPT_SSL_VERIFYPEER => false,
        // CURLOPT_SSL_VERIFYHOST => false,
    ));

    $result = curl_exec($curl);
    // echo $result;
    // exit();
    //$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    $data = json_decode($result);
    var_dump($data);
    //echo $data['account'];
    // $_data = json_encode($data, JSON_PRETTY_PRINT);
    // echo "<pre>".$_data."<pre/>";
    //echo 'done';
?>