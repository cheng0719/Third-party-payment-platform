<html>
    <body>
        <h1>
            訂單詳情
        </h1>

        

        <form method="post">
            MerchantID:<input name="MerchantID" type="text" />
            <br />
            Password:<input name="Password" type="text" />
            <br />
            MerchantTradeNum:<input name="MerchantTradeNum" type="text" />
            <br />
            MerchantTradeDate:<input name="MerchantTradeDate" type="text" />
            <br />
            TotalAmount:<input name="TotalAmount" type="number" />
            <br />
            ItemName:<input name="ItemName" type="text" />
            <br />
            ExpireDate:<input name="ExpireDate" type="text" />
            <br />
            TradeDesc:<input name="TradeDesc" type="text" />
            <br />
            Remark:<input name="Remark" type="text" />
            <br />
            <input type="submit" name="Submit" value="確認支付"/>
        </form>


        <?php
            
            if(isset($_POST['Submit']))
            {
                $url = 'http://www.serverside.com/api_controll.php';

                $query = [
                    'Method' => 'Create',
                    'MerchantID' => $_POST['MerchantID'],
                    'Password' => $_POST['Password'],
                    'MerchantTradeNum' => $_POST['MerchantTradeNum'],
                    'MerchantTradeDate' => $_POST['MerchantTradeDate'],
                    'TotalAmount' => $_POST['TotalAmount'],
                    'ItemName' => $_POST['ItemName'],
                    'ExpireDate' => $_POST['ExpireDate'],
                    'TradeDesc' => $_POST['TradeDesc'],
                    'Remark' => $_POST['Remark']
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
                // echo $response;

                //$data = http_build_query($response);

                $data = json_decode($response);
                header("Location:" . $data->paymentUrl);
            }
        ?>
    </body>
</html>

<?php
    function curl() {
        $url = 'http://www.serverside.com/api_controll.php';

        $query = [
            'Method' => 'Create',
            'MerchantID' => '00001',
            'Password' => 'password_1',
            'MerchantTradeNum' => 'cs00013',
            'MerchantTradeDate' => '20221020210632',
            'TotalAmount' => '1300',
            'ItemName' => 'cost 13',
            'ExpireDate' => '20221201200000',
            'TradeDesc' => 'description 13',
            'Remark' => 'remark 13'
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
        // echo $response;

        //$data = http_build_query($response);

        $data = json_decode($response);
        header("Location:" . $data->paymentUrl);

        // $_data = json_encode($data, JSON_PRETTY_PRINT);
        // echo "<pre>".$_data."<pre/>";
        
    }
    
?>