<?php
$sandBox = 'https://sandbox-api-pay.line.me';
$uri = '/v3/payments/request';
$channelId = '1656946528';
$channelSecret = '36307ac678361a20fd395e209f0428ba';
$Nonce = date('c') . uniqid('-');


$qyery = [
    'amount' => 1000,
    'currency' => 'TWD',
    'orderId' => '30',
    'packages' => [
        [
            'id' => '30',
            'amount' => 1000,
            'name' => 'test store',
            'products' => [
                [
                    'name' => 'test product',
                    'quantity' => 1,
                    'price' => 1000
                ],
            ],
        ],
    ],
    'redirectUrls' => [
        'confirmUrl' => 'http://clientside.com/linepay_confirm.php',
        'cancelUrl' => 'http://clientside.com'
    ],
];
//var_dump($qyery);

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

curl_close($curl);
// var_dump($response);

$data = json_decode($response);
$_data = json_encode($data, JSON_PRETTY_PRINT);
echo "<pre>".$_data."<pre/>";
//header("Location: ".$data->info->paymentUrl->web);
?>