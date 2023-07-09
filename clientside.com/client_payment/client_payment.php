<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Client Payment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
  </head>
  <body>

    <?php
        // $transaction_data = array(
        //     'merchanttradenum' => 'cs00028',
        //     'merchanttradedate' => '20221128102020',
        //     'itemName' => 'cost num 28',
        //     'totalAmount' => 2800,
        //     'tradeDesc' => 'desc 28',
        //     'expireDate' => '20221201000000',
        //     'remark' => '無備註'
        // );
    ?>

    <section id="first">
      <div class="container">
        <div class="row">
            <!-- <div class="col-md-8 offset-md-2 text-center"> -->
            <div class="text-center">
                <h1 class="title p-2">訂單資訊</h1>
                <table class="table">
                <thead>
                    <tr>
                    <th scope="col">項目名稱</th>
                    <th scope="col">金額</th>
                    <th scope="col">敘述</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                    <td name="itemname"> 註冊費 </td>
                    <td name="totalamount"> 3100 </td>
                    <td name="tradedesc"> 學生註冊費 </td>
                    </tr>
                </tbody>
                </table>
            </div>
        </div>
      </div>
    </section>


    <section id="second">
      <div class="container">
        <div class="row">
          <div class="col-md-8 offset-md-2 text-center">
            <h1 class="title">確認支付</h1>
            <div class="row">
              <form class="col-md-8 offset-md-2" method="post" action="http://www.clientside.com/client_payment/client_payment.php">
                <input type="hidden" name="flag" value="execute">
                <input type="submit" name="Submit" value="確認支付">
              </form>
            </div>
          </div>
        </div>
      </div>
    </section>
    <?php
        if($_POST['flag'] == 'execute')
        {

            $url = 'http://www.serverside.com/api_controll.php';

            $query = [
                'Method' => 'Create',
                'MerchantID' => '00001',
                'Password' => 'password_1',
                'MerchantTradeNum' => 'cs00031',
                'MerchantTradeDate' => '20221131000000',
                'TotalAmount' => 3100,
                'ItemName' => 'name 31',
                'ExpireDate' => '20221201000000',
                'TradeDesc' => 'desc 31',
                'Remark' => 'no remark'
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
            header("Location:" . $data->paymentUrl);
        }
    ?>
    

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
  </body>
</html>