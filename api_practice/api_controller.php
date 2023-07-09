<?php
    require_once 'config.php';

    class APIController
    {
        private $request_method;
        private $MerchantID;
        private $MerchantTradeNum;
        private $MerchantTradeDate;
        private $TotalAmount;
        private $ItemName;

        /***
         *  Constructor
         */
        function __construct($method){
            $this->request_method = $method;
        }

        /***
         *  Check request method
         */
        function Check_method(){
            return $this->request_method;
        }

        /***
         *  make a database connection
         */
        function Connect($array): PDO
        {
            try {
                $Host = $array['host'];
                $Port = $array['port'];
                $Dbname = $array['dbname'];
                $Username = $array['username'];
                $Password = $array['password'];

                $dsn = 'pgsql:host=' . $Host . ';port=' . $Port . ';dbname=' . $Dbname . ';';

                return new PDO(
                    $dsn,
                    $Username,
                    $Password,
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );
            } catch (PDOException $e) {
                die($e->getMessage());
            }
        }

        /***
         *  Search transaction data with merchant id and merchant trade number
         */
        function Search_data($array){
            $Table = $array['table'];
            $conn = $this->Connect($array);

            // $stmt = $conn->prepare("select * from $Table where merchantid = :merchantid and merchanttradenum = :merchanttradenum;");
            // $stmt->bindParam(":merchantid", $this->MerchantID);
            // $stmt->bindParam(":merchanttradenum", $this->MerchantTradeNum);
            $stmt = $conn->prepare("select * from $Table where totalamount = :totalamount;");
            $stmt->bindParam(":totalamount", $this->TotalAmount);
            $stmt->execute();
            return $stmt->fetchAll();
        }

        /***
         *  Insert transaction data into table
         */
        function Insert_table($array){
            $Table = $array['table'];
            $conn = $this->Connect($array);

            $stmt = $conn->prepare("insert into $Table (merchantid, merchanttradenum, merchanttradedate, 
                    totalamount, itemname) values (:merchantid, :merchanttradenum, :merchanttradedate, 
                    :totalamount, :itemname);");
            $stmt->bindParam(":merchantid", $this->MerchantID);
            $stmt->bindParam(":merchanttradenum", $this->MerchantTradeNum);
            $stmt->bindParam(":merchanttradedate", $this->MerchantTradeDate);
            $stmt->bindParam(":totalamount", $this->TotalAmount);
            $stmt->bindParam(":itemname", $this->ItemName);

            $stmt->execute();
            return $conn->lastInsertId();
        }

        /***
         * 
         */
        function Fill_in_data($merchantID, $merchantTradeNum, $merchantTradeDate, $totalAmount, $itemName, $array){
            $this->Fill_in_merchantID($merchantID);
            $this->Fill_in_merchantTradeNum($merchantTradeNum, $array);
            $this->Fill_in_merchantTradeDate($merchantTradeDate);
            $this->Fill_in_totalAmount($totalAmount);
            $this->Fill_in_itemName($itemName);
        }

        /***
         * 
         */
        private function Fill_in_merchantID($merchantID){
            $this->MerchantID = $merchantID;
        }

        private function Fill_in_merchantTradeNum($merchantTradeNum, $array){
            $Table = $array['table'];

            $conn = $this->Connect($array);

            $stmt = $conn->prepare("select * from $Table where merchanttradenum = :merchanttradenum;");
            $stmt->bindParam(":merchanttradenum", $this->MerchantTradeNum);
            $stmt->execute();

            if(empty($stmt->fetchAll()))
            {
                $this->MerchantTradeNum = $merchantTradeNum;
            }
            else
            {
                echo 'Merchant trade number is repeated!';
                exit();
            }
        }

        private function Fill_in_merchantTradeDate($merchantTradeDate){
            $this->MerchantTradeDate = $merchantTradeDate;
        }

        private function Fill_in_totalAmount($totalAmount){
            if($this->request_method == 'POST')
            {
                if($totalAmount > 0)
                {
                    $this->TotalAmount = $totalAmount;
                }
                else
                {
                    echo 'Total amount is not correct!';
                    exit();
                }
            }
            else
            {
                $this->TotalAmount = $totalAmount;
            }
        }

        private function Fill_in_itemName($itemName){
            $this->ItemName = $itemName;
        }
    }

    $db_data = array(
        'host' => $host,
        'port' => $port,
        'dbname' => $db,
        'username' => $username,
        'password' => $password,
        'table' => $table
    );
    
    $controller = new APIController($_SERVER['REQUEST_METHOD']);
    if($controller->Check_method() == 'POST')  // deal with POST form and cURL POST
    {
        $controller->Fill_in_data($_POST['MerchantID'], $_POST['MerchantTradeNum'], $_POST['MerchantTradeDate'],
                                $_POST['TotalAmount'], $_POST['ItemName'], $db_data);
        // if($controller->Insert_table($db_data) > 0)
        // {
        //     echo 'Transaction data saved successfully';
        // }
        // else
        // {
        //     echo 'Failed to save transaction data';
        // }
        echo 'id is ' . $controller->Insert_table($db_data);
    }
    else if($controller->Check_method() == 'GET')  // deal with GET form
    {
        $controller->Fill_in_data($_GET['MerchantID'], $_GET['MerchantTradeNum'], null,
                                $_GET['TotalAmount'], null, $db_data);
        $search_data = $controller->Search_data($db_data);
        // if(isset($search_data))
        // {
        //     if(!empty($search_data))
        //     {
        //         $response = array(
        //             'Merchant ID' => $search_data[0]['merchantid'],
        //             'Merchant Trade Num' => $search_data[0]['merchanttradenum'],
        //             'Merchant Trade Date' => $search_data[0]['merchanttradedate'],
        //             'Total Amount' => $search_data[0]['totalamount'],
        //             'Item Name' => $search_data[0]['itemname']
        //         );

        //         $response_ = json_encode($response, JSON_PRETTY_PRINT);
        //         echo "<pre>".$response_."<pre/>";
        //     }
        //     else
        //     {
        //         echo 'Failed to find the transaction data';
        //     }
        // }
        // else
        // {
        //     echo 'Failed to return transaction data';
        // }
        var_dump($search_data);
    }
?>