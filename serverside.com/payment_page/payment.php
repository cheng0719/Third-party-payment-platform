<?php

    date_default_timezone_set("Asia/Taipei");

    error_reporting(E_ALL ^ E_WARNING);

    ini_set('error_log', __DIR__ . "/api_error.log");

    require_once 'C:\Apache24\htdocs\serverside.com\config.php';
    require_once 'C:\Apache24\htdocs\serverside.com\CryptAES.php';


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

    function decryption($cipher){
        $key = 'abcdefghijklmnop';
        $iv = '1234567890123456';
        $password = 'abcdefghijklmnop';

        $decryption = new CryptAES($key, $iv, $password);
        $ptext = $decryption->decrypt($cipher);
        return $ptext;
    }

    try{
        // fetch url param
        $id_ciphertext = $_GET['id'];    
        
        // check if param [id] exist
        if (empty($id_ciphertext))
        {
            throw new Exception('param [id] does not exist');
        }

        // decrypt [id] param and devide it into id and randomstring
        $plaintext = decryption(hex2bin($id_ciphertext));   
        $decrypted_string = explode(" ", $plaintext);
        
        // compare randomstring in url and database
        $conn = DBConnection();
        $stmt = $conn->prepare('select randomstring from api_practice where traderecordid = :traderecordid;');
        $stmt->bindParam(':traderecordid', $decrypted_string[0]);
        if(!$stmt->execute())
        {
            throw new Exception('Failed database processing');
        }
        $db_randomString = $stmt->fetch();
        if(strcmp($decrypted_string[1], $db_randomString[0]) !== 0)
        {
            throw new Exception('one time pad not correct');
        }
        
        // delete randomstring in database
        $stmt = $conn->prepare('update api_practice
                                set randomstring = null
                                where traderecordid = :traderecordid;');
        $stmt->bindParam(':traderecordid', $decrypted_string[0]);
        if(!$stmt->execute())
        {
            throw new Exception('Failed database processing');
        }

        // fetch data in database with traderecordid
        $stmt = $conn->prepare('select * from api_practice where traderecordid = :traderecordid;');
        $stmt->bindParam(':traderecordid', $decrypted_string[0]);
        if(!$stmt->execute())
        {
            throw new Exception('Failed database processing');
        }
        $transaction_data = $stmt->fetch();

        // check if this transaction complete
        if($transaction_data['paymentcompletion'])
        {
            throw new Exception('The transaction is already complete');
        }

        // check if the transaction is expired
        if($transaction_data['expiredate'] <= date("YmdHis"))
        {
            throw new Exception('The transaction is already expired');
        }


        // CSRF prevention
        $csrf_token = null;
        if(empty($transaction_data['csrftoken']))
        {
            $bytes = random_bytes(8);
            $csrf_token = bin2hex($bytes);
            $stmt = $conn->prepare('update api_practice
                                    set csrftoken = :csrftoken
                                    where traderecordid = :traderecordid;');
            $stmt->execute(array(
                'csrftoken' => $csrf_token,
                'traderecordid' => $transaction_data['traderecordid']
            ));
        }
        else
        {
            $csrf_token = $transaction_data['csrftoken'];
        }
 

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


<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
    <link href="payment.css" rel="stylesheet">
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
                    <td name="itemname"> <?php echo $transaction_data['itemname'] ?> </td>
                    <td name="totalamount"> <?php echo $transaction_data['totalamount'] ?> </td>
                    <td name="tradedesc"> <?php echo $transaction_data['tradedesc'] ?> </td>
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
            <h1 class="title">選擇支付方式</h1>
            <div class="row">
              <form class="col-md-8 offset-md-2" method="POST" action="http://www.serverside.com/payment_process.php">
                <input type="hidden" name="csrfToken" value="<?php echo $csrf_token ?>">
                <input type="hidden" name="transactionID" value="<?php echo $transaction_data['traderecordid']?>">
                <div class="form-check border ps-5 py-4">
                  <input class="form-check-input my-3" type="radio" name="PaymentMethod" id="exampleRadios1" value="LinePay" checked>
                  <label class="form-check-label fs-2" for="exampleRadios1">
                    LinePay
                  </label>
                </div>
                <div class="form-check border ps-5 py-4">
                  <input class="form-check-input my-3" type="radio" name="PaymentMethod" id="exampleRadios2" value="VISA">
                  <label class="form-check-label fs-2" for="exampleRadios2">
                    VISA
                  </label>
                </div>
                <div class="submit p-3">
                  <button type="submit" class="btn btn-primary mb-3">確定</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </section>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-A3rJD856KowSb7dwlZdYEkO39Gagi7vIsF0jrRAoQmDKKtQBHUuLZ9AsSv4jD4Xa" crossorigin="anonymous"></script>
  </body>
</html>