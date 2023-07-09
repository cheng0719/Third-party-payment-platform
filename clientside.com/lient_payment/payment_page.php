<?php
    $transaction_data = array(
        'merchanttradenum' => 'cs00024',
        'merchanttradedate' => '20221128102020',
        'itemName' => 'cost num 24',
        'totalAmount' => 2400,
        'tradeDesc' => 'desc 24',
        'expireDate' => '20221201000000',
        'remark' => '無備註'
    );
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Client Payment Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
    <link href="payment_page.css" rel="stylesheet">
  </head>
  <body>

    <div class="container">
      <nav class="navbar navbar-expand-lg bg-light">
        <div class="container-fluid">
          <a class="navbar-brand" href="#">Navbar</a>
          <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
          </button>
          <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
              <li class="nav-item">
                <a class="nav-link active" aria-current="page" href="#">Home</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="#">Link</a>
              </li>
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                  Dropdown
                </a>
                <ul class="dropdown-menu">
                  <li><a class="dropdown-item" href="#">Action</a></li>
                  <li><a class="dropdown-item" href="#">Another action</a></li>
                  <li><hr class="dropdown-divider"></li>
                  <li><a class="dropdown-item" href="#">Something else here</a></li>
                </ul>
              </li>
            </ul>
            <form class="d-flex" role="search">
              <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search">
              <button class="btn btn-outline-success" type="submit">Search</button>
            </form>
          </div>
        </div>
      </nav>
    </div>



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
                    <td name="itemname"> <?php echo $transaction_data['itemName']; ?> </td>
                    <td name="totalamount"> <?php echo $transaction_data['totalAmount']; ?> </td>
                    <td name="tradedesc"> <?php echo $transaction_data['tradeDesc'] ?> </td>
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
              <form class="col-md-8 offset-md-2" method="post">
                <div class="submit p-3">
                  <!-- <button type="submit" name="Submit" class="btn btn-primary mb-3">確定</button> -->
                  <input type="submit" name="Submit" value="確認支付"/>
                </div>
              </form>


              <?php
                  if(isset($_POST['Submit']))
                  {
                      $url = 'http://www.serverside.com/api_controll.php';

                      $query = [
                          'Method' => 'Create',
                          'MerchantID' => '00001',
                          'Password' => 'password_1',
                          'MerchantTradeNum' => $transaction_data['merchanttradenum'],
                          'MerchantTradeDate' => $transaction_data['merchanttradedate'],
                          'TotalAmount' => $transaction_data['totalamount'],
                          'ItemName' => $transaction_data['itemname'],
                          'ExpireDate' => $transaction_data['expiredate'],
                          'TradeDesc' => $transaction_data['tradedesc'],
                          'Remark' => $transaction_data['remark']
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
            </div>
          </div>
        </div>
      </div>
    </section>

    


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-A3rJD856KowSb7dwlZdYEkO39Gagi7vIsF0jrRAoQmDKKtQBHUuLZ9AsSv4jD4Xa" crossorigin="anonymous"></script>
  </body>
</html>